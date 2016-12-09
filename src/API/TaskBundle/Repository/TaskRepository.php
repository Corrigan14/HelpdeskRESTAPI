<?php

namespace API\TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TaskRepository
 */
class TaskRepository extends EntityRepository
{
    const LIMIT = 10;

    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param array $options
     * @return array|null
     */
    public function getAllAdminTasks(int $page, array $options)
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.id is not NULL');

        $paramArray = [];
        $paramNum = 0;
        foreach ($options as $key => $value) {
            if (false !== $value) {
                $query->andWhere($key . '= :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
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
     * @param array $options
     * @return int|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countAllAdminTasks(array $options)
    {
        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.id is not NULL');

        $paramArray = [];
        $paramNum = 0;
        foreach ($options as $key => $value) {
            if (false !== $value) {
                $query->andWhere($key . '= :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
            }
        }

        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $page
     * @param int $userId
     * @param int $companyId
     * @param $dividedProjects
     * @param array $options
     * @return array|null
     */
    public function getAllUsersTasks(int $page, int $userId, int $companyId, $dividedProjects, array $options)
    {
        if (array_key_exists('VIEW_ALL_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $allTasksInProject */
            $allTasksInProject = $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'];
        } else {
            $allTasksInProject = [];
        }

        if (array_key_exists('VIEW_COMPANY_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $companyTasksInProject */
            $companyTasksInProject = $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'];
        } else {
            $companyTasksInProject = [];
        }

        $query = $this->createQueryBuilder('t')
            ->where('t.createdBy = :userId')
            ->orWhere('t.requestedBy = :userId');
        $paramArray['userId'] = $userId;

        $paramNum = 0;
        if (count($allTasksInProject) > 0) {
            foreach ($allTasksInProject as $project) {
                $query->orWhere('t.project = :project' . $paramNum);
                $paramArray['project' . $paramNum] = $project;

                $paramNum++;
            }
        }

        if (count($companyTasksInProject) > 0) {
            foreach ($companyTasksInProject as $project) {
                $query->orWhere('t.project = :project' . $paramNum)
                    ->leftJoin('t.createdBy', 'u')
                    ->andWhere('u.company = :companyId');
                $paramArray['project' . $paramNum] = $project;

                $paramNum++;
            }
            $paramArray['companyId'] = $companyId;
        }

        foreach ($options as $key => $value) {
            if (false !== $value) {
                $query->andWhere($key . '= :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
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
     * @param int $userId
     * @param int $companyId
     * @param $dividedProjects
     * @param array $options
     * @return int
     */
    public function countAllUsersTasks(int $userId, int $companyId, $dividedProjects, array $options)
    {
        if (array_key_exists('VIEW_ALL_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $allTasksInProject */
            $allTasksInProject = $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'];
        } else {
            $allTasksInProject = [];
        }

        if (array_key_exists('VIEW_COMPANY_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $companyTasksInProject */
            $companyTasksInProject = $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'];
        } else {
            $companyTasksInProject = [];
        }

        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.createdBy = :userId')
            ->orWhere('t.requestedBy = :userId');
        $paramArray['userId'] = $userId;

        $paramNum = 0;
        if (count($allTasksInProject) > 0) {
            foreach ($allTasksInProject as $project) {
                $query->orWhere('t.project = :project' . $paramNum);
                $paramArray['project' . $paramNum] = $project;

                $paramNum++;
            }
        }

        if (count($companyTasksInProject) > 0) {
            foreach ($companyTasksInProject as $project) {
                $query->orWhere('t.project = :project' . $paramNum)
                    ->leftJoin('t.createdBy', 'u')
                    ->andWhere('u.company = :companyId');
                $paramArray['project' . $paramNum] = $project;

                $paramNum++;
            }
            $paramArray['companyId'] = $companyId;
        }

        foreach ($options as $key => $value) {
            if (false !== $value) {
                $query->andWhere($key . '= :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
            }
        }

        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        return $query->getQuery()->getSingleScalarResult();
    }
}
