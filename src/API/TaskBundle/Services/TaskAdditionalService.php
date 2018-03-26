<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\File;
use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAttachment;
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
     * @param array $options
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getTaskAttachmentsResponse(array $options): array
    {
        /** @var Task $task */
        $task = $options['task'];
        $taskAttachmentArray = [];

        $taskAttachments = $task->getTaskHasAttachments();
        /** @var TaskHasAttachment $tag */
        foreach ($taskAttachments as $attachment) {
            $taskAttachmentArray [] = [
                'id' => $attachment->getId(),
                'slug' => $attachment->getSlug()
            ];
        }

        $response = [
            'data' => $taskAttachmentArray
        ];

        $pagination = [
            '_links' => [],
            'total' => \count($taskAttachmentArray)
        ];

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getTaskOneAttachmentResponse(array $options): array
    {
        /** @var Task $task */
        $taskId = $options['task'];
        /** @var File $file */
        $file = $options['file'];

        $attachmentArray = [
            'id' => $file->getId(),
            'slug' => $file->getSlug(),
            'fileDir' => $file->getUploadDir(),
            'fileName' => $file->getTempName()
        ];

        $response = [
            'data' => $attachmentArray
        ];

        $pagination = [
            '_links' => [
                'add attachment to the Task' => $this->router->generate('tasks_add_attachment_to_task', ['taskId' => $taskId, 'slug' => $file->getSlug()]),
                'remove attachment from the Task' => $this->router->generate('tasks_remove_attachment_from_task', ['taskId' => $taskId, 'slug' => $file->getSlug()]),
            ]
        ];

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
    public function getTaskOneTagResponse(array $options): array
    {
        /** @var Task $task */
        $taskId = $options['task'];
        /** @var Tag $user */
        $tag = $options['tag'];

        $tagArray = [
            'id' => $tag->getId(),
            'title' => $tag->getTitle(),
            'color' => $tag->getColor(),
            'public' => $tag->getPublic()
        ];

        $response = [
            'data' => $tagArray
        ];

        $pagination = [
            '_links' => [
                'add tag to the Task' => $this->router->generate('tasks_add_tag_to_task', ['taskId' => $taskId, 'tagId' => $tag->getId()]),
                'remove tag from the Task' => $this->router->generate('tasks_remove_tag_from_task', ['taskId' => $taskId, 'tagId' => $tag->getId()]),
            ]
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
    public function getTaskCommentsResponse(array $options, int $page, array $routeOptions): array
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

        $pagination = $this->getPagination($page, $count, $routeOptions, $limit, $options['filterForUrl']);

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
    public function getTaskCommentResponse(int $id): array
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
     * @param bool $filterForUrl
     * @return array
     */
    private function getPagination(int $page, int $count, array $routeOptions, $limit, $filterForUrl = false): array
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
                'self' => $url . '?page=' . $page . $filterForUrl,
                'first' => $url . '?page=' . 1 . $filterForUrl,
                'prev' => $previousPage ? $url . '?page=' . $previousPage : false . $filterForUrl,
                'next' => $nextPage ? $url . '?page=' . $nextPage : false . $filterForUrl,
                'last' => $url . '?page=' . $totalNumberOfPages . $filterForUrl,
            ],
            'total' => $count,
            'page' => $page,
            'numberOfPages' => $totalNumberOfPages,
        ];
    }
}