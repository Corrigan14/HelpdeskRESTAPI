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
     * @param array $optionsNeeded
     * @return array|null
     */
    public function getAllUsersTasks(int $page, int $userId, int $companyId, $dividedProjects, array $optionsNeeded)
    {
        return [];
    }


    /**
     * @param int $userId
     * @param int $companyId
     * @param $dividedProjects
     * @param array $optionsNeeded
     * @return int
     */
    public function countAllUsersTasks(int $userId, int $companyId, $dividedProjects, array $optionsNeeded)
    {
        return 12;
    }
}
