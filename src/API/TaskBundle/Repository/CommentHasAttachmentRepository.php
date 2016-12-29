<?php

namespace API\TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CommentHasAttachmentRepository
 */
class CommentHasAttachmentRepository extends EntityRepository
{
    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param int $commentId
     * @return array|null
     */
    public function getAllAttachmentSlugs(int $commentId, int $page)
    {
        $query = $this->createQueryBuilder('cha')
            ->select('cha.slug')
            ->where('cha.comment = :commentId')
            ->setParameter('commentId', $commentId);

        $query->setMaxResults(TaskRepository::LIMIT);

        // Pagination calculating offset
        if (1 < $page) {
            $query->setFirstResult(TaskRepository::LIMIT * $page - TaskRepository::LIMIT);
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * Return count of all Entities
     *
     * @param int $commentId
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @internal param array $options
     *
     */
    public function countAttachmentEntities(int $commentId)
    {
        $query = $this->createQueryBuilder('cha')
            ->select('COUNT(cha.id)')
            ->where('cha.comment = :commentId')
            ->setParameter('commentId', $commentId);

        return $query->getQuery()->getSingleScalarResult();
    }
}
