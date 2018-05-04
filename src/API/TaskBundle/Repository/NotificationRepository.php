<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\Notification;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * NotificationRepository
 */
class NotificationRepository extends EntityRepository
{

    /**
     * @param int $page
     * @param array $options
     * @return array
     */
    public function getLoggedUserNotifications(int $page, array $options): array
    {
        $loggedUserId = $options['loggedUserId'];
        $read = $options['read'];
        $order = $options['order'];
        $limit = $options['limit'];

        if (true === $read || false === $read) {
            $query = $this->createQueryBuilder('notification')
                ->select('notification, createdBy, detailData, project, task')
                ->leftJoin('notification.createdBy', 'createdBy')
                ->leftJoin('createdBy.detailData', 'detailData')
                ->leftJoin('notification.user', 'user')
                ->leftJoin('notification.project', 'project')
                ->leftJoin('notification.task', 'task')
                ->where('user.id = :loggedUserId')
                ->andWhere('notification.checked = :read')
                ->setParameters([
                    'loggedUserId' => $loggedUserId,
                    'read' => $read
                ])
                ->orderBy('notification.createdAt', $order)
                ->distinct();
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
                ->orderBy('notification.createdAt', $order)
                ->distinct();
        }
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
        }
        // Return all entities
        return [
            'array' => $this->formatData($query->getQuery()->getArrayResult(), true)
        ];

    }

    /**
     * @param int $loggedUserId
     * @param bool $read
     * @return int|null
     * @throws \Doctrine\ORM\NonUniqueResultException
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
     * @param  $data
     * @param bool $array
     * @return array
     */
    private function formatData($data, $array = false): array
    {
        $response = [];

        foreach ($data as $processingData) {
            if ($array) {
                $response[] = $this->processArrayData($processingData);
            } else {
                $response[] = $this->processData($processingData);
            }

        }

        return $response;
    }

    /**
     * @param Notification $data
     * @return array
     */
    private function processData(Notification $data): array
    {
        $userCreatorDetailData = $data->getCreatedBy()->getDetailData();
        $userCreatorName = null;
        $userCreatorSurname = null;
        if ($userCreatorDetailData) {
            $userCreatorName = $userCreatorDetailData->getName();
            $userCreatorSurname = $userCreatorDetailData->getSurname();
        }

        $project = $data->getProject();
        $projectArray = null;
        if ($project instanceof Project) {
            $projectArray = [
                'id' => $project->getId(),
                'title' => $project->getTitle()
            ];
        }

        $task = $data->getTask();
        $taskArray = null;
        if ($task instanceof Task) {
            $taskArray = [
                'id' => $task->getId(),
                'title' => $task->getTitle()
            ];
        }

        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'body' => $data->getBody(),
            'read' => $data->getChecked(),
            'createdAt' => $data->getCreatedAt(),
            'createdBy' => [
                'id' => $data->getCreatedBy()->getId(),
                'username' => $data->getCreatedBy()->getUsername(),
                'email' => $data->getCreatedBy()->getEmail(),
                'name' => $userCreatorName,
                'surname' => $userCreatorSurname,
            ],
            'project' => $projectArray,
            'task' => $taskArray
        ];

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

        $body = json_decode($data['body'], true);
        if ($body) {
            $bodyDecoded = $body;
        } else {
            $bodyDecoded = $data['body'];
        }

        $response = [
            'id' => $data['id'],
            'title' => $data['title'],
            'body' => $bodyDecoded,
            'read' => $data['checked'],
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
