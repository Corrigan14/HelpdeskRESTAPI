<?php

namespace API\TaskBundle\Services;

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
     * @param int $taskId
     * @param int $page
     * @return array
     */
    public function getTaskAttachmentsResponse(int $taskId, int $page): array
    {
        $tasks = $this->em->getRepository('APITaskBundle:TaskHasAttachment')->getAllEntities($taskId,$page);
        $count = $this->em->getRepository('APITaskBundle:TaskHasAttachment')->countEntities($taskId);


        $response = [
            'data' => $tasks,
        ];

        $pagination = $this->getPagination($page, $count, $taskId);

        return array_merge($response, $pagination);
    }

    /**
     * @param int $page
     * @param int $count
     * @param array $options
     * @return array
     */
    private function getPagination(int $page, int $count, array $options)
    {
        $limit = TaskRepository::LIMIT;
        $url = $this->router->generate('tasks_list');

        $totalNumberOfPages = ceil($count / $limit);
        $previousPage = $page > 1 ? $page - 1 : false;
        $nextPage = $page < $totalNumberOfPages ? $page + 1 : false;

        $creator = $options['creator'];
        $requestedUser = $options['requested'];
        $project = $options['project'];

        $creatorParam = (false !== $creator ? '&creator=' . $creator : false);
        $requestedUserParam = (false !== $requestedUser ? '&requested=' . $requestedUser : false);
        $projectParam = (false !== $project ? '&project=' . $project : false);

        return [
            '_links' => [
                'self' => $url . '?page=' . $page . $creatorParam . $requestedUserParam . $projectParam,
                'first' => $url . '?page=' . 1 . $creatorParam . $requestedUserParam . $projectParam,
                'prev' => $previousPage ? $url . '?page=' . $previousPage . $creatorParam . $requestedUserParam . $projectParam : false,
                'next' => $nextPage ? $url . '?page=' . $nextPage . $creatorParam . $requestedUserParam . $projectParam : false,
                'last' => $url . '?page=' . $totalNumberOfPages . $creatorParam . $requestedUserParam . $projectParam,
            ],
            'total' => $count,
            'page' => $page,
            'numberOfPages' => $totalNumberOfPages,
        ];
    }
}