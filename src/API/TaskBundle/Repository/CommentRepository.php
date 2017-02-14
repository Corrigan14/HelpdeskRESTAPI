<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\Task;
use Doctrine\ORM\EntityRepository;

/**
 * CommentRepository
 */
class CommentRepository extends EntityRepository
{
    /**
     * @param array $options
     * @param int $page
     * @return array
     */
    public function getTaskComments(array $options, int $page): array
    {
        /** @var Task $task */
        $task = $options['task'];
        $internal = $options['internal'];

        if ('false' === strtolower($internal)) {
            $query = $this->createQueryBuilder('c')
                ->select('c,createdby,subcomments,commenthasattachments,subcommentAttachments')
                ->where('c.task = :taskId')
                ->leftJoin('c.createdBy', 'createdby')
                ->leftJoin('c.commentHasAttachments', 'commenthasattachments')
                ->leftJoin('c.comment', 'subcomments')
                ->leftJoin('subcomments.commentHasAttachments', 'subcommentAttachments')
                ->andWhere('c.internal = :internal')
                ->setParameters(['taskId' => $task, 'internal' => 0])
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('c')
                ->select('c,createdby,subcomments,commenthasattachments,subcommentAttachments')
                ->where('c.task = :taskId')
                ->leftJoin('c.createdBy', 'createdby')
                ->leftJoin('c.commentHasAttachments', 'commenthasattachments')
                ->leftJoin('c.comment', 'subcomments')
                ->leftJoin('subcomments.commentHasAttachments', 'subcommentAttachments')
                ->setParameter('taskId', $task)
                ->getQuery();
        }

        $query->setMaxResults(TaskRepository::LIMIT);

        // Pagination calculating offset
        if (1 < $page) {
            $query->setFirstResult(TaskRepository::LIMIT * $page - TaskRepository::LIMIT);
        }

        return $query->getArrayResult();
    }

    /**
     * @param array $options
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countTaskComments(array $options): int
    {
        /** @var Task $task */
        $task = $options['task'];
        $internal = $options['internal'];

        if ('false' === strtolower($internal)) {
            $query = $this->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.task = :taskId')
                ->andWhere('c.internal = :internal')
                ->setParameters(['taskId' => $task, 'internal' => 0])
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.task = :taskId')
                ->setParameter('taskId', $task)
                ->getQuery();
        }

        return $query->getSingleScalarResult();
    }

    /**
     * @param $id
     * @return array
     */
    public function getCommentEntity($id):array
    {
        $query = $this->createQueryBuilder('comment')
            ->select('comment, creator, subcomments, commentHasAttachments, subCommenthasattachments')
            ->leftJoin('comment.createdBy', 'creator')
            ->leftJoin('comment.comment', 'subcomments')
            ->leftJoin('comment.commentHasAttachments', 'commentHasAttachments')
            ->leftJoin('subcomments.commentHasAttachments', 'subCommenthasattachments')
            ->where('comment.id = :id')
            ->setParameter('id', $id);

        return $query->getQuery()->getArrayResult();
    }
}
