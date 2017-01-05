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
                ->where('c.task = :taskId')
                ->andWhere('c.internal = :internal')
                ->setParameters(['taskId' => $task, 'internal' => 0])
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('c')
                ->where('c.task = :taskId')
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
}
