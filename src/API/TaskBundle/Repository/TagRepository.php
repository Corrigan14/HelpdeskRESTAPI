<?php

namespace API\TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TagRepository
 */
class TagRepository extends EntityRepository
{
    /**
     * Return all User's Tags + public Tags
     *
     * @param int $userId
     * @return array
     */
    public function getAllTags(int $userId)
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.createdBy = :userId')
            ->orWhere('t.public = :public')
            ->setParameters(['userId' => $userId, 'public' => true]);

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
            ->setParameter('userId',$userId);

        return $query->getQuery()->getArrayResult();
    }
}
