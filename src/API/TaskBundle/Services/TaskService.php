<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Repository\TaskRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class TaskService
 *
 * @package API\TaskBundle\Services
 */
class TaskService
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
     * Return Tasks Response which includes Data, Links and is based on Pagination, Project, Creator and/or Requested user
     *
     * @param int $page
     * @param array $options
     *
     * @return array
     */
    public function getTasksResponse(int $page, array $options):array
    {
        $data = $this->getRequiredTasks($page, $options);
        $tasks = $data['tasks'];
        $count = $data['count'];

        $response = [
            'data' => $tasks,
        ];

        $pagination = $this->getPagination($page, $count, $options);

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

    /**
     * Return Tasks based on User's ACL
     *
     * @param int $page
     * @param array $options
     * @return array
     */
    private function getRequiredTasks(int $page, array $options)
    {
        $loggedUser = $options['loggedUser'];
        $isAdmin = $options['isAdmin'];

        $optionsNeeded = [
            't.createdBy' => $options['creator'],
            't.requestedBy' => $options['requested'],
            't.project' => $options['project']
        ];

        // Return's all Tasks - logged user is ADMIN
        if ($isAdmin) {
            $tasks = $this->em->getRepository('APITaskBundle:Task')->getAllAdminTasks($page, $optionsNeeded);
            $count = $this->em->getRepository('APITaskBundle:Task')->countAllAdminTasks($optionsNeeded);
            return [
                'tasks' => $tasks,
                'count' => $count
            ];
        }

        // Return's tasks based on loggedUser ACL


        return [];
    }
}