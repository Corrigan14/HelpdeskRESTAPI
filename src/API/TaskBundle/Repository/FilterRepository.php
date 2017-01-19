<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Repository\RepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * FilterRepository
 */
class FilterRepository extends EntityRepository implements RepositoryInterface
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

        $query = $this->createQueryBuilder('f')
            ->select('f, createdBy,project')
            ->leftJoin('f.createdBy', 'createdBy')
            ->leftJoin('f.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
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

        $query->setMaxResults(self::LIMIT);

        // Pagination calculating offset
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Return count of all Entities
     *
     * @param array $options
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countEntities(array $options = [])
    {
        $isActive = $options['isActive'];
        $public = $options['public'];
        $report = $options['report'];
        $project = $options['project'];
        $loggedUserId = $options['loggedUserId'];

        $query = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->leftJoin('f.createdBy', 'createdBy')
            ->leftJoin('f.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
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

        return $query->getQuery()->getSingleScalarResult();
    }
}
