<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\TaskHasAssignedUser;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * TaskHasAssignedUserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaskHasAssignedUserRepository extends EntityRepository
{
    /**
     * @param int $taskId
     * @param int $page
     * @return array
     */
    public function getTasksAssignedUsers(int $taskId, int $page): array
    {
        $query = $this->createQueryBuilder('tau')
            ->select('tau')
            ->leftJoin('tau.user', 'user')
            ->orderBy('tau.id','DESC')
            ->distinct()
            ->where('tau.task = :taskId')
            ->setParameter('taskId', $taskId);

        // Pagination
        if (1 < $page) {
            $query->setFirstResult(TaskRepository::LIMIT * $page - TaskRepository::LIMIT);
        } else {
            $query->setFirstResult(0);
        }

        $query->setMaxResults(TaskRepository::LIMIT);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = $paginator->count();

        return [
            'count' => $count,
            'array' => $this->formatData($paginator)
        ];
    }

    /**
     * @param $paginatorData
     * @return array
     */
    private function formatData($paginatorData):array
    {
        $response = [];
        /** @var TaskHasAssignedUser $data */
        foreach ($paginatorData as $data) {
            $response[] = $this->processData($data);
        }

        return $response;
    }

    /**
     * @param TaskHasAssignedUser $data
     * @return array
     */
    private function processData(TaskHasAssignedUser $data):array
    {
        $response = [
            'id' => $data->getId(),
            'createdAt' => $data->getCreatedAt(),
            'updatedAt' => $data->getUpdatedAt(),
            'status_date' => $data->getStatusDate(),
            'time_spent' => $data->getTimeSpent(),
            'user' => [
                'id' => $data->getUser()->getId(),
                'username' => $data->getUser()->getUsername(),
                'email' => $data->getUser()->getEmail()
            ],
            'status' => [
                'id' => $data->getStatus()->getId(),
                'title' => $data->getStatus()->getTitle(),
                'color' => $data->getStatus()->getColor(),
            ]
        ];

        return $response;
    }
}
