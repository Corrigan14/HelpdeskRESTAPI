<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\Filter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * FilterRepository
 */
class FilterRepository extends EntityRepository
{
    const LIMIT = 10;

    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param array $options
     * @return mixed
     */
    public function getAllEntities(int $page, array $options = [])
    {
        $isActive = $options['isActive'];
        $public = $options['public'];
        $report = $options['report'];
        $project = $options['project'];
        $loggedUserId = $options['loggedUserId'];
        $order = $options['order'];

        $query = $this->createQueryBuilder('f')
            ->select('f')
            ->leftJoin('f.createdBy', 'createdBy')
            ->leftJoin('f.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
            ->orderBy('f.order', $order)
            ->distinct()
            ->where('f.id is not NULL');

        $paramArray = [];
        if ('true' === $isActive) {
            $query->andWhere('f.is_active = :isActiveParam');
            $paramArray['isActiveParam'] = true;
        } elseif ('false' === $isActive) {
            $query->andWhere('f.is_active = :isActiveParam');
            $paramArray['isActiveParam'] = false;
        }

        if ('true' === $public) {
            $query->andWhere('f.public = :publicParam');
            $paramArray['publicParam'] = true;
        } elseif ('false' === $public) {
            $query->andWhere('createdBy.id = :loggedUserId');
            $paramArray['loggedUserId'] = $loggedUserId;
        } else {
            $query->andWhere('f.public = :publicParam')
                ->orWhere('createdBy.id = :loggedUserId');
            $paramArray['publicParam'] = true;
            $paramArray['loggedUserId'] = $loggedUserId;
        }

        if ('true' === $report) {
            $query->andWhere('f.report = :reportParam');
            $paramArray['reportParam'] = true;
        } elseif ('false' === $report) {
            $query->andWhere('f.report = :reportParam');
            $paramArray['reportParam'] = false;
        }

        if (!empty($project)) {
            if ('not' === $project) {
                $query->andWhere('f.project IS NULL');
            } elseif ('current-user' === $project) {
                $query->andWhere('projectCreator.id = :projectCreatorId');
                $paramArray['projectCreatorId'] = $loggedUserId;
            } else {
                $query->andWhere('project.id IN (:projectIds)');

                $projectIds = explode(',', $project);
                $paramArray['projectIds'] = $projectIds;
            }
        }


        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        // Pagination
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        } else {
            $query->setFirstResult(0);
        }

        $query->setMaxResults(self::LIMIT);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = $paginator->count();

        return [
            'count' => $count,
            'array' => $this->formatData($paginator)
        ];
    }

    /**
     * Return's all entities without pagination
     *
     * @param array $options
     * @return mixed
     */
    public function getAllUsersFiltersWithoutPagination(array $options = [])
    {
        $isActive = $options['isActive'];
        $public = $options['public'];
        $report = $options['report'];
        $loggedUserId = $options['loggedUserId'];

        $query = $this->createQueryBuilder('f')
            ->select('f, createdBy, project')
            ->leftJoin('f.createdBy', 'createdBy')
            ->leftJoin('f.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
            ->orderBy('f.id', 'DESC')
            ->distinct()
            ->where('f.id is not NULL');

        $paramArray = [];
        if (true === $isActive) {
            $query->andWhere('f.is_active = :isActiveParam');
            $paramArray['isActiveParam'] = true;
        } elseif (false === $isActive) {
            $query->andWhere('f.is_active = :isActiveParam');
            $paramArray['isActiveParam'] = false;
        }

        if (true === $public) {
            $query->andWhere('f.public = :publicParam');
            $paramArray['publicParam'] = true;
        } elseif (false === $public) {
            $query->andWhere('createdBy.id = :loggedUserId');
            $paramArray['loggedUserId'] = $loggedUserId;
        } else {
            $query->andWhere('f.public = :publicParam')
                ->orWhere('createdBy.id = :loggedUserId');
            $paramArray['publicParam'] = true;
            $paramArray['loggedUserId'] = $loggedUserId;
        }

        if (true === $report) {
            $query->andWhere('f.report = :reportParam');
            $paramArray['reportParam'] = true;
        } elseif (false === $report) {
            $query->andWhere('f.report = :reportParam');
            $paramArray['reportParam'] = false;
        }

        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        $query = $query->getQuery()->getArrayResult();

        $arrayProcessed = [];

        foreach ($query as $data) {
            $projectArray = null;
            if (isset($data['project'])) {
                $projectArray = [
                    'id' => $data['project']['id'],
                    'title' => $data['project']['title'],
                ];
            }
            $arrayProcessed [] = [
                'id' => $data['id'],
                'title' => $data['title'],
                'public' => $data['public'],
                'filter' => $data['filter'],
                'report' => $data['report'],
                'is_active' => $data['is_active'],
                'default' => $data['default'],
                'icon_class' => $data['icon_class'],
                'order' => $data['order'],
                'createdBy' => [
                    'id' => $data['createdBy']['id'],
                    'username' => $data['createdBy']['username'],
                    'email' => $data['createdBy']['email']
                ],
                'project' => $projectArray,
                'columns' => $data['columns']
            ];
        }

        return $arrayProcessed;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getFilterEntity(int $id): array
    {
        $query = $this->createQueryBuilder('filter')
            ->select('filter')
            ->leftJoin('filter.project', 'project')
            ->where('filter.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * @param $paginatorData
     * @return array
     */
    private function formatData($paginatorData): array
    {
        $response = [];
        /** @var Filter $data */
        foreach ($paginatorData as $data) {
            $response[] = $this->processData($data);
        }

        return $response;
    }

    /**
     * @param $data
     * @return array
     */
    private function processData(Filter $data): array
    {
        $project = $data->getProject();
        $projectArray = null;
        if ($project) {
            $projectArray = [
                'id' => $project->getId(),
                'title' => $project->getTitle()
            ];
        }

        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'public' => $data->getPublic(),
            'filter' => $data->getFilter(),
            'report' => $data->getReport(),
            'is_active' => $data->getIsActive(),
            'default' => $data->getDefault(),
            'icon_class' => $data->getIconClass(),
            'order' => $data->getOrder(),
            'createdBy' => [
                'id' => $data->getCreatedBy()->getId(),
                'username' => $data->getCreatedBy()->getUsername(),
                'email' => $data->getCreatedBy()->getEmail()
            ],
            'project' => $projectArray,
            'columns' => $data->getColumns(),
            'columns_task_attributes' => $data->getColumnsTaskAttributes()
        ];

        return $response;
    }
}
