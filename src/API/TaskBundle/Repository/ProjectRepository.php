<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends EntityRepository
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
        $loggedUser = $options['loggedUser'];
        $isAdmin = $options['isAdmin'];
        $isActive = $options['isActive'];
        $limit = $options['limit'];

        if ('true' === $isActive || true === $isActive) {
            $isActiveParam = 1;
        } else {
            $isActiveParam = 0;
        }

        if ($isAdmin) {
            if ('true' === $isActive || 'false' === $isActive || true === $isActive || false === $isActive) {
                $query = $this->createQueryBuilder('p')
                    ->select('p, userHasProjects, uhpUser')
                    ->leftJoin('p.userHasProjects', 'userHasProjects')
                    ->leftJoin('userHasProjects.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->where('p.is_active = :isActive')
                    ->setParameter('isActive', $isActiveParam)
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('p, userHasProjects, uhpUser')
                    ->leftJoin('p.userHasProjects', 'userHasProjects')
                    ->leftJoin('userHasProjects.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->getQuery();
            }
        } else {
            if ('true' === $isActive || 'false' === $isActive || true === $isActive || false === $isActive) {
                $query = $this->createQueryBuilder('p')
                    ->select('p, userHasProjects, uhpUser')
                    ->leftJoin('p.userHasProjects', 'userHasProjects')
                    ->leftJoin('userHasProjects.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->where('p.createdBy = :loggedUser OR userHasProjects.user = :loggedUser')
                    ->andWhere('p.is_active = :isActive')
                    ->setParameters(['loggedUser' => $loggedUser, 'isActive' => $isActiveParam])
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('p, userHasProjects, uhpUser')
                    ->leftJoin('p.userHasProjects', 'userHasProjects')
                    ->leftJoin('userHasProjects.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->where('p.createdBy = :loggedUser OR userHasProjects.user = :loggedUser')
                    ->setParameter('loggedUser', $loggedUser)
                    ->getQuery();
            }
        }

        if (999 !== $limit) {
            // Pagination
            if (1 < $page) {
                $query->setFirstResult($limit * $page - $limit);
            } else {
                $query->setFirstResult(0);
            }

            $query->setMaxResults($limit);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = $paginator->count();

            return [
                'count' => $count,
                'array' => $this->formatData($paginator)
            ];
        } else {
            // Return all entities
            return [
                'array' => $this->formatData($query->getArrayResult(), true)
            ];
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function getEntityWithTasks($id)
    {
        $query = $this->createQueryBuilder('project')
            ->select('project, userHasProjects, tasks, uhpUser')
            ->leftJoin('project.userHasProjects', 'userHasProjects')
            ->leftJoin('userHasProjects.user', 'uhpUser')
            ->leftJoin('project.tasks', 'tasks')
            ->where('project.id = :id')
            ->setParameter('id', $id);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $id
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getEntity($id)
    {
        $query = $this->createQueryBuilder('project')
            ->select('project')
            ->leftJoin('project.userHasProjects', 'userHasProjects')
            ->leftJoin('userHasProjects.user', 'uhpUser')
            ->where('project.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * @return array
     */
    public function getAllProjectEntitiesWithIdAndTitle(): array
    {
        $query = $this->createQueryBuilder('project')
            ->select('project.id, project.title')
            ->where('project.is_active = :isActive')
            ->setParameter('isActive', true);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $paginatorData
     * @param bool $array
     * @return array
     */
    private function formatData($paginatorData, $array = false): array
    {
        $response = [];
        foreach ($paginatorData as $data) {
            if ($array) {
                $response[] = $this->processArrayData($data);
            } else {
                $response[] = $this->processData($data);
            }
        }

        return $response;
    }


    /**
     * @param Project $data
     * @return array
     */
    private function processData(Project $data): array
    {
        $userHasProjects = $data->getUserHasProjects();
        $userHasProjectsArray = [];
        if ($userHasProjects) {
            /** @var UserHasProject $item */
            foreach ($userHasProjects as $item) {
                $userHasProjectsArray[] = [
                    'id' => $item->getId(),
                    'user' => [
                        'id' => $item->getUser()->getId(),
                        'username' => $item->getUser()->getUsername(),
                        'email' => $item->getUser()->getEmail()
                    ],
                    'acl' => $item->getAcl()
                ];
            }
        }

        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'description' => $data->getDescription(),
            'createdAt' => $data->getCreatedAt(),
            'updatedAt' => $data->getUpdatedAt(),
            'is_active' => $data->getIsActive(),
            'userHasProjects' => $userHasProjectsArray
        ];

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $userHasProjectsArray = [];
        if (isset($data['userHasProjects'])) {
            $userHasProjects = $data['userHasProjects'];
            /** @var UserHasProject $item */
            foreach ($userHasProjects as $item) {
                $userHasProjectsArray[] = [
                    'id' => $item['id'],
                    'user' => [
                        'id' => $item['user']['id'],
                        'username' => $item['user']['username'],
                        'email' => $item['user']['email']
                    ],
                    'acl' => json_decode($item['acl'])
                ];
            }
        }

        $response = [
            'id' => $data['id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'createdAt' => isset($data['createdAt']) ? date_timestamp_get($data['createdAt']) : null,
            'updatedAt' => isset($data['updatedAt']) ? date_timestamp_get($data['updatedAt']) : null,
            'is_active' => $data['is_active'],
            'userHasProjects' => $userHasProjectsArray
        ];

        return $response;
    }
}
