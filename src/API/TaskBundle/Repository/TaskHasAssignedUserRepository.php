<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\Task;
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
     * @param int $limit
     * @return array
     */
    public function getTasksAssignedUsers(int $taskId, int $page, int $limit): array
    {
        $query = $this->createQueryBuilder('tau')
            ->select('tau, user, status')
            ->leftJoin('tau.user', 'user')
            ->leftJoin('tau.status', 'status')
            ->orderBy('tau.id', 'DESC')
            ->distinct()
            ->where('tau.task = :taskId')
            ->andWhere('tau.actual = :actual')
            ->setParameters([
                'taskId' => $taskId,
                'actual' => true
            ]);

        if (999 !== $limit) {
            // Pagination
            if (1 < $page) {
                $query->setFirstResult($limit * $page - $limit);
            } else {
                $query->setFirstResult(0);
            }

            $query->setMaxResults($limit);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = $paginator->count();

            return [
                'count' => $count,
                'array' => $this->formatData($paginator)
            ];
        } else {
            // Return all entities
            return [
                'array' => $this->formatData($query->getQuery()->getArrayResult(), true)
            ];
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function findOtherUsersAssignedToTask(array $data): array
    {
        $task = $data['task'];
        $user = $data['user'];

        $query = $this->createQueryBuilder('tau')
            ->select('tau')
            ->leftJoin('tau.user', 'user')
            ->orderBy('tau.id', 'DESC')
            ->distinct()
            ->where('tau.task = :task')
            ->andWhere('tau.user != :user')
            ->setParameters([
                'user' => $user,
                'task' => $task
            ]);

        return $query->getQuery()->getResult();
    }

    /**
     * @param array $data
     * @return array
     */
    public function findAssignedUsersEntities(array $data): array
    {
        $task = $data['task'];
        $user = $data['user'];

        $query = $this->createQueryBuilder('tau')
            ->select('tau')
            ->leftJoin('tau.user', 'user')
            ->orderBy('tau.id', 'DESC')
            ->distinct()
            ->where('tau.task = :task')
            ->andWhere('tau.user = :user')
            ->setParameters([
                'user' => $user,
                'task' => $task
            ]);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $paginatorData
     * @param bool $array
     * @return array
     */
    private function formatData($paginatorData, $array = false): array
    {
        $response = [];
        foreach ($paginatorData as $data) {
            if ($array) {
                $response[] = $this->processArrayData($data);
            } else {
                $response[] = $this->processData($data);
            }
        }

        return $response;
    }

    /**
     * @param TaskHasAssignedUser $data
     * @return array
     */
    private function processData(TaskHasAssignedUser $data): array
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
            ],
            'gps' => $data->getGps()
        ];

        return $response;
    }

    /**
     * @param  array $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $response = [
            'id' => $data['id'],
            'createdAt' => isset($data['createdAt']) ? date_timestamp_get($data['createdAt']) : null,
            'updatedAt' => isset($data['updatedAt']) ? date_timestamp_get($data['updatedAt']) : null,
            'status_date' => isset($data['status_date']) ? date_timestamp_get($data['status_date']) : null,
            'time_spent' => $data['time_spent'],
            'user' => [
                'id' => $data['user']['id'],
                'username' => $data['user']['username'],
                'email' => $data['user']['email']
            ],
            'status' => [
                'id' => $data['status']['id'],
                'title' => $data['status']['title'],
                'color' => $data['status']['color'],
            ],
            'gps' => $data['gps']
        ];

        return $response;
    }
}
