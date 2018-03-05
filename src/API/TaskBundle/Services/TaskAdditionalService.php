<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskSubtask;
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
        $limit = $options['limit'];

        $responseData = $this->em->getRepository('APITaskBundle:TaskHasAttachment')->getAllAttachmentSlugs($taskId, $page, $limit);

        $response = [
            'data' => $responseData['array'],
        ];

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $pagination = $this->getPagination($page, $count, $routeOptions, $limit);

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getTaskTagsResponse(array $options): array
    {
        /** @var Task $task */
        $task = $options['task'];
        $taskTagsArray = [];
        $taskTags = $task->getTags();
        /** @var Tag $tag */
        foreach ($taskTags as $tag) {
            $taskTagsArray [] = [
                'id' => $tag->getId(),
                'title' => $tag->getTitle(),
                'color' => $tag->getColor()
            ];
        }

        $response = [
            'data' => $taskTagsArray
        ];

        $pagination = [
            '_links' => [],
            'total' => \count($taskTagsArray)
        ];

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getTaskFollowerResponse(array $options): array
    {
        /** @var Task $task */
        $task = $options['task'];
        $followersArray = [];

        $responseData = $task->getFollowers();
        /** @var User $follower */
        foreach ($responseData as $follower) {
            $name = '';
            $surname = '';
            if ($follower->getDetailData()) {
                $name = $follower->getDetailData()->getName();
                $surname = $follower->getDetailData()->getSurname();
            }
            $followersArray[] = [
                'id' => $follower->getId(),
                'username' => $follower->getUsername(),
                'email' => $follower->getEmail(),
                'name' => $name,
                'surname' => $surname
            ];
        }

        $response = [
            'data' => $followersArray
        ];

        $pagination = [
            '_links' => [],
            'total' => \count($followersArray)
        ];

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getTaskOneFollowerResponse(array $options): array
    {
        /** @var Task $task */
        $taskId = $options['task'];
        /** @var User $user */
        $user = $options['user'];

        $name = '';
        $surname = '';
        if ($user->getDetailData()) {
            $name = $user->getDetailData()->getName();
            $surname = $user->getDetailData()->getSurname();
        }
        $followersArray = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'name' => $name,
            'surname' => $surname
        ];

        $response = [
            'data' => $followersArray
        ];

        $pagination = [
            '_links' => [
                'add follower to the Task' => $this->router->generate('tasks_add_follower_to_task', ['taskId' => $taskId, 'userId' => $user->getId()]),
                'remove follower from the Task' => $this->router->generate('tasks_remove_follower_from_task', ['taskId' => $taskId, 'userId' => $user->getId()]),
            ]
        ];

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
        $limit = $options['limit'];
        $responseData = $this->em->getRepository('APITaskBundle:TaskHasAssignedUser')->getTasksAssignedUsers($task->getId(), $page, $limit);

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $response = [
            'data' => $responseData['array']
        ];

        $pagination = $this->getPagination($page, $count, $routeOptions, $limit);

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
        $limit = $options['limit'];
        $responseData = $this->em->getRepository('APITaskBundle:Comment')->getTaskComments($options, $page, $limit);

        $response = [
            'data' => $responseData['array']
        ];


        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $pagination = $this->getPagination($page, $count, $routeOptions, $limit);

        return array_merge($response, $pagination);
    }

    /**
     * Return Entity Response which includes all data about Entity and Link to delete
     *
     * @param int $id
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getCommentOfTaskResponse(int $id): array
    {
        $comment = $this->em->getRepository('APITaskBundle:Comment')->getCommentEntity($id);

        return [
            'data' => $comment,
            '_links' => [
                'delete' => $this->router->generate('tasks_delete_tasks_comment', ['commentId' => $id])
            ],
        ];
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
    public function getCommentAttachmentsResponse(array $options, int $page, array $routeOptions): array
    {
        $commentId = $options['comment'];
        $limit = $options['limit'];

        $responseData = $this->em->getRepository('APITaskBundle:CommentHasAttachment')->getAllAttachmentSlugs($commentId, $page, $limit);

        $response = [
            'data' => $responseData['array'],
        ];


        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $pagination = $this->getPagination($page, $count, $routeOptions, $limit);

        return array_merge($response, $pagination);
    }

    /**
     * @param Task $task
     * @return array
     */
    public function getTaskSubtasksResponse(Task $task): array
    {
        $responseData = $this->em->getRepository('APITaskBundle:TaskSubtask')->getEntities($task->getId());

        $response = [
            'data' => $responseData
        ];

        $pagination = [
            '_links' => [],
            'total' => \count($responseData)
        ];

        return array_merge($response, $pagination);
    }

    /**
     * @param int $taskId
     * @param int $subtaskId
     * @return array
     */
    public function getTaskSubtaskResponse(int $taskId, int $subtaskId): array
    {
        $subtask = $this->em->getRepository('APITaskBundle:TaskSubtask')->getEntity($taskId, $subtaskId);

        return [
            'data' => $subtask,
            '_links' => [
                'create subtask' => $this->router->generate('tasks_create_subtask', ['taskId' => $taskId]),
                'update subtask' => $this->router->generate('tasks_update_subtask', ['taskId' => $taskId, 'subtaskId' => $subtaskId]),
                'delete subtask' => $this->router->generate('tasks_delete_subtask', ['taskId' => $taskId, 'subtaskId' => $subtaskId])
            ],
        ];
    }

    /**
     * @param int $page
     * @param int $count
     * @param array $routeOptions
     * @param int $limit
     * @return array
     */
    private function getPagination(int $page, int $count, array $routeOptions, $limit): array
    {
        $routeName = $routeOptions['routeName'];
        $routeParams = $routeOptions['routeParams'];
        $url = $this->router->generate($routeName, $routeParams);

        if (999 !== $limit) {
            $totalNumberOfPages = ceil($count / $limit);
            $previousPage = $page > 1 ? $page - 1 : false;
            $nextPage = $page < $totalNumberOfPages ? $page + 1 : false;
        } else {
            $totalNumberOfPages = 1;
            $previousPage = false;
            $nextPage = false;
        }

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