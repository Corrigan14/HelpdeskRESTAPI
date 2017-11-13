<?php

namespace API\TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * NotificationRepository
 */
class NotificationRepository extends EntityRepository
{

    /**
     * @param int $loggedUserId
     * @param $read
     * @return array
     */
    public function getLoggedUserNotifications(int $loggedUserId, $read): array
    {
        if ($read) {
            $query = $this->createQueryBuilder('notification')
                ->select('notification, createdBy, detailData, project, task')
                ->leftJoin('notification.createdBy', 'createdBy')
                ->leftJoin('createdBy.detailData', 'detailData')
                ->leftJoin('notification.user', 'user')
                ->leftJoin('notification.project', 'project')
                ->leftJoin('notification.task', 'task')
                ->where('user.id = :loggedUserId')
                ->andWhere('notification.checked = :checked')
                ->setParameters([
                    'loggedUserId' => $loggedUserId,
                    'checked' => $read
                ])
                ->orderBy('notification.createdAt', 'ASC')
                ->distinct()
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('notification')
                ->select('notification, createdBy, detailData, project, task')
                ->leftJoin('notification.createdBy', 'createdBy')
                ->leftJoin('createdBy.detailData', 'detailData')
                ->leftJoin('notification.user', 'user')
                ->leftJoin('notification.project', 'project')
                ->leftJoin('notification.task', 'task')
                ->where('user.id = :loggedUserId')
                ->setParameter('loggedUserId', $loggedUserId)
                ->orderBy('notification.createdAt', 'ASC')
                ->distinct()
                ->getQuery();
        }

        return $this->formatData($query->getArrayResult());
    }

    /**
     * @param int $loggedUserId
     * @param bool $read
     * @return int|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countLoggedUserNotifications(int $loggedUserId, bool $read): int
    {
        $query = $this->createQueryBuilder('notification')
            ->select('COUNT(notification.id)')
            ->leftJoin('notification.user', 'user')
            ->where('user.id = :loggedUserId')
            ->andWhere('notification.checked = :checked')
            ->setParameters([
                'loggedUserId' => $loggedUserId,
                'checked' => $read
            ])
            ->distinct()
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param $data
     * @return array
     */
    private function formatData(array $data): array
    {
        $response = [];
        foreach ($data as $datum) {
            $response[] = $this->processArrayData($datum);
        }

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $userCreatorDetailData = $data['createdBy']['detailData'];
        $userCreatorName = null;
        $userCreatorSurname = null;
        if ($userCreatorDetailData) {
            $userCreatorName = $userCreatorDetailData['name'];
            $userCreatorSurname = $userCreatorDetailData['surname'];
        }

        $project = $data['project'];
        $projectArray = null;
        if ($project) {
            $projectArray = [
                'id' => $project['id'],
                'title' => $project['title']
            ];
        }

        $task = $data['task'];
        $taskArray = null;
        if ($task) {
            $taskArray = [
                'id' => $task['id'],
                'title' => $task['title']
            ];
        }

        $response = [
            'id' => $data['id'],
            'title' => $data['title'],
            'body' => $data['body'],
            'checked' => $data['checked'],
            'createdAt' => isset($data['createdAt']) ? date_timestamp_get($data['createdAt']) : null,
            'createdBy' => [
                'id' => $data['createdBy']['id'],
                'username' => $data['createdBy']['username'],
                'email' => $data['createdBy']['email'],
                'name' => $userCreatorName,
                'surname' => $userCreatorSurname,
            ],
            'project' => $projectArray,
            'task' => $taskArray
        ];

        return $response;
    }
}
