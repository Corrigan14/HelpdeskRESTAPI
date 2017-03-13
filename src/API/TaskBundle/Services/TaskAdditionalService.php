<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Tag;
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

        $responseData = $this->em->getRepository('APITaskBundle:TaskHasAttachment')->getAllAttachmentSlugs($taskId, $page);

        $response = [
            'data' => $responseData['array'],
        ];

        $pagination = $this->getPagination($page, $responseData['count'], $routeOptions);

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
            'total' => count($taskTagsArray)
        ];

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
        $followersArray = [];

        $responseData = $task->getFollowers();
        foreach ($responseData as $follower) {
            $followersArray[] = [
                'id' => $follower->getId(),
                'username' => $follower->getUsername(),
                'email' => $follower->getEmail()
            ];
        }

        $response = [
            'data' => $followersArray
        ];

        $pagination = [
            '_links' => [],
            'total' => count($followersArray)
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
        $responseData = $this->em->getRepository('APITaskBundle:TaskHasAssignedUser')->getTasksAssignedUsers($task->getId(), $page);

        $response = [
            'data' => $responseData['array']
        ];

        $pagination = $this->getPagination($page, $responseData['count'], $routeOptions);

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
        $responseData = $this->em->getRepository('APITaskBundle:Comment')->getTaskComments($options, $page);

        $response = [
            'data' => $responseData['array']
        ];

        $pagination = $this->getPagination($page, $responseData['count'], $routeOptions);

        return array_merge($response, $pagination);
    }

    /**
     * Return Entity Response which includes all data about Entity and Link to delete
     *
     * @param int $id
     *
     * @return array
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

        $responseData = $this->em->getRepository('APITaskBundle:CommentHasAttachment')->getAllAttachmentSlugs($commentId, $page);

        $response = [
            'data' => $responseData['array'],
        ];

        $pagination = $this->getPagination($page, $responseData['count'], $routeOptions);

        return array_merge($response, $pagination);
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