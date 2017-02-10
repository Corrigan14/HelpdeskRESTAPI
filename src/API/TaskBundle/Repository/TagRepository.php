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
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param array $options
     * @return mixed
     */
    public function getAllEntities(int $page, array $options = [])
    {
        $userId = $options['loggedUserId'];

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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countEntities(array $options = [])
    {
        $userId = $options['loggedUserId'];

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
     * @param int $id
     * @return array
     */
    public function getEntity(int $id):array
    {
        $query = $this->createQueryBuilder('tag')
            ->where('tag.id = :tagId')
            ->setParameter('tagId', $id);

        return $query->getQuery()->getArrayResult();
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
     * @param int $userId
     * @return array
     */
    public function getAllTagEntitiesWithIdAndTitle(int $userId):array
    {
        $query = $this->createQueryBuilder('tag')
            ->select('tag.id, tag.title')
            ->where('tag.public = :public')
            ->orWhere('tag.createdBy = :userId')
            ->setParameters(['public' => true, 'userId' => $userId]);

        return $query->getQuery()->getArrayResult();
    }
}
