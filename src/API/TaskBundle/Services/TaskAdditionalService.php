<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Repository\TaskRepository;
use API\TaskBundle\Repository\TaskHasAssignedUserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class TaskAdditionalService
 *
 * @package API\TaskBundle\Services
 */
class TaskAdditionalService
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;

    /**
     * TaskService constructor.
     *
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * Return Attachment (File) Response which includes Data, Links and is based on Pagination
     *
     * @param array $options
     * @param int $page
     * @param array $routeOptions
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @internal param int $taskId
     */
    public function getTaskAttachmentsResponse(array $options, int $page, array $routeOptions): array
    {
        $taskId = $options['task'];

        $fileSlugs = $this->em->getRepository('APITaskBundle:TaskHasAttachment')->getAllAttachmentSlugs($taskId, $page);
        $count = $this->em->getRepository('APITaskBundle:TaskHasAttachment')->countAttachmentEntities($taskId);

        $attachmentsArray = [];
        if (count($fileSlugs) > 0) {
            foreach ($fileSlugs as $slug) {
                $file = $this->em->getRepository('APICoreBundle:File')->findOneBy([
                    'slug' => $slug
                ]);
                $attachmentsArray[] = $file;
            }
        }

        $response = [
            'data' => $attachmentsArray,
        ];

        $pagination = $this->getPagination($page, $count, $routeOptions);

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @param int $page
     * @param array $routeOptions
     * @return array
     */
    public function getTaskTagsResponse(array $options, int $page, array $routeOptions): array
    {
        /** @var Task $task */
        $task = $options['task'];
        $taskTags = $task->getTags();
        $taskTagsArray = $this->em->getRepository('APITaskBundle:Task')->getTasksTags($task->getId(), $page);
        $count = count($taskTags);

        $response = [
            'data' => []
        ];
        if (count($taskTagsArray) > 0) {
            $response = [
                'data' => $taskTagsArray[0]['tags'],
            ];
        }

        $pagination = $this->getPagination($page, $count, $routeOptions);

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @param int $page
     * @param array $routeOptions
     * @return array
     */
    public function getTaskFollowerResponse(array $options, int $page, array $routeOptions): array
    {
        /** @var Task $task */
        $task = $options['task'];
        $taskFollowers = $task->getFollowers();
        $taskFollowersArray = $this->em->getRepository('APITaskBundle:Task')->getTasksFollowers($task->getId(), $page);
        $count = count($taskFollowers);

        $response = [
            'data' => []
        ];

        if (count($taskFollowersArray) > 0) {
            $data = [];
            foreach ($taskFollowersArray[0]['followers'] as $item) {
                unset($item['password']);
                $data[] = $item;
            }
            $response = [
                'data' => $data,
            ];
        }

        $pagination = $this->getPagination($page, $count, $routeOptions);

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @param int $page
     * @param array $routeOptions
     * @return array
     */
    public function getUsersAssignedToTaskResponse(array $options, int $page, array $routeOptions): array
    {
        /** @var Task $task */
        $task = $options['task'];
        $taskAssignedUsers = $task->getTaskHasAssignedUsers();
        $taskAssignedUsersArray = $this->em->getRepository('APITaskBundle:TaskHasAssignedUser')->getTasksAssignedUsers($task->getId(), $page);
        $count = count($taskAssignedUsers);

        $response = [
            'data' => []
        ];
        if (count($taskAssignedUsersArray) > 0) {
            $data = [];
            foreach ($taskAssignedUsersArray as $item) {
                unset($item['user']['password']);
                $data[] = $item['user'];
            }
            $response = [
                'data' => $data,
            ];
        }

        $pagination = $this->getPagination($page, $count, $routeOptions);

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @param int $page
     * @param array $routeOptions
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getCommentsOfTaskResponse(array $options, int $page, array $routeOptions): array
    {
        $commentsArray = $this->em->getRepository('APITaskBundle:Comment')->getTaskComments($options, $page);
        $countComments = $this->em->getRepository('APITaskBundle:Comment')->countTaskComments($options);

        $response = [
            'data' => $commentsArray
        ];

        $pagination = $this->getPagination($page, $countComments, $routeOptions);

        return array_merge($response, $pagination);
    }

    /**
     * Return Entity Response which includes all data about Entity and Link to delete
     *
     * @param Comment $entity
     *
     * @return array
     */
    public function getCommentOfTaskResponse($entity): array
    {
        return [
            'data' => $entity,
            '_links' => [
                'delete' => $this->router->generate('tasks_delete_tasks_comment', ['commentId' => $entity->getId()])
            ],
        ];
    }

    /**
     * @param int $page
     * @param int $count
     * @param array $routeOptions
     * @return array
     */
    private function getPagination(int $page, int $count, array $routeOptions)
    {
        $limit = TaskRepository::LIMIT;

        $routeName = $routeOptions['routeName'];
        $routeParams = $routeOptions['routeParams'];
        $url = $this->router->generate($routeName, $routeParams);

        $totalNumberOfPages = ceil($count / $limit);
        $previousPage = $page > 1 ? $page - 1 : false;
        $nextPage = $page < $totalNumberOfPages ? $page + 1 : false;

        return [
            '_links' => [
                'self' => $url . '?page=' . $page,
                'first' => $url . '?page=' . 1,
                'prev' => $previousPage ? $url . '?page=' . $previousPage : false,
                'next' => $nextPage ? $url . '?page=' . $nextPage : false,
                'last' => $url . '?page=' . $totalNumberOfPages,
            ],
            'total' => $count,
            'page' => $page,
            'numberOfPages' => $totalNumberOfPages,
        ];
    }
}