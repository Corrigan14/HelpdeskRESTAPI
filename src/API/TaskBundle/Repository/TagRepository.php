<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Repository\RepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * TagRepository
 */
class TagRepository extends EntityRepository implements RepositoryInterface
{
    const LIMIT = 10;

    /**
     * Return all User's Tags + public Tags
     *
     * @param int $userId
     * @param int $page
     * @return array
     */
    public function getAllTags(int $userId, int $page)
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.createdBy = :userId')
            ->orWhere('t.public = :public')
            ->setParameters(['userId' => $userId, 'public' => true])
            ->getQuery();

        $query->setMaxResults(self::LIMIT);

        /**
         * Pagination calculating offset
         */
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        }

        return $query->getArrayResult();
    }

    /**
     * Return all User's Tags
     *
     * @param int $userId
     * @return array
     */
    public function getUsersTags(int $userId)
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.createdBy = :userId')
            ->setParameter('userId', $userId);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Return count of all User's tags + public tags
     *
     * @param int $userId
     * @return int
     */
    public function countTags(int $userId): int
    {
        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.createdBy = :userId')
            ->orWhere('t.public = :public')
            ->setParameters(['userId' => $userId, 'public' => true])
            ->getQuery()
            ->getSingleScalarResult();

        return $query;
    }

    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param array $options
     * @return mixed
     */
    public function getAllEntities(int $page, array $options = [])
    {
        $userId = $options['userId'];

        $query = $this->createQueryBuilder('t')
            ->where('t.createdBy = :userId')
            ->orWhere('t.public = :public')
            ->setParameters(['userId' => $userId, 'public' => true])
            ->getQuery();

        $query->setMaxResults(self::LIMIT);

        // Pagination calculating offset

        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        }

        return $query->getArrayResult();
    }

    /**
     * Return count of all Entities
     *
     * @param array $options
     *
     * @return int
     */
    public function countEntities(array $options = [])
    {
        // TODO: Implement countEntities() method.
    }
}
