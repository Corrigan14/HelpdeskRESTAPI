<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Repository\TaskRepository;
use API\TaskBundle\Security\VoteOptions;
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getTasksResponse(int $page, array $options): array
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
     * @param Task $task
     * @return array
     */
    public function getTaskResponse(Task $task)
    {
        return [
            'data' => $task,
            '_links' => $this->getTaskLinks($task->getId()),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getTaskLinks(int $id)
    {
        return [
            'put' => $this->router->generate('tasks_update', ['id' => $id, 'projectId' => 'all', 'requestedUserId' => 'all']),
            'patch' => $this->router->generate('tasks_partial_update', ['id' => $id, 'projectId' => 'all', 'requestedUserId' => 'all']),
            'delete' => $this->router->generate('tasks_delete', ['id' => $id]),
        ];
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

        $filters = $options['filtersForUrl'];
        $params = '';

        foreach ($filters as $filter) {
            $params .= $filter;
        }

        return [
            '_links' => [
                'self' => $url . '?page=' . $page . $params,
                'first' => $url . '?page=' . 1 . $params,
                'prev' => $previousPage ? $url . '?page=' . $previousPage . $params : false,
                'next' => $nextPage ? $url . '?page=' . $nextPage . $params : false,
                'last' => $url . '?page=' . $totalNumberOfPages . $params,
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    private function getRequiredTasks(int $page, array $options)
    {
        /** @var User $loggedUser */
        $loggedUser = $options['loggedUser'];
        $isAdmin = $options['isAdmin'];

        // Return's all Tasks - logged user is ADMIN
        if ($isAdmin) {
            $tasks = $this->em->getRepository('APITaskBundle:Task')->getAllAdminTasks($page, $options);
            $count = $this->em->getRepository('APITaskBundle:Task')->countAllAdminTasks($options);
            return [
                'tasks' => $tasks,
                'count' => $count
            ];
        }

        // Return's tasks based on loggedUser ACL
        // User Can view: - all tasks which created and which are requested by him - also tasks which are not in projects
        //               - tasks from projects: VIEW_ALL_TASKS_IN_PROJECT, VIEW_COMPANY_TASKS_IN_PROJECT,

        // Divide user's projects based on his ACL

        // 1. User can view all tasks in projects which he created
        $usersProjects = $loggedUser->getProjects();
        $dividedProjects = [];
        if (count($usersProjects) > 0) {
            /** @var Project $up */
            foreach ($usersProjects as $up) {
                $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'][] = $up->getId();
            }
        }

        // 2. User can view projects tasks based on userHasProject ACL
        $userHasProjects = $loggedUser->getUserHasProjects();
        if (count($userHasProjects) > 0) {
            /** @var UserHasProject $uhp */
            foreach ($userHasProjects as $uhp) {
                $acl = $uhp->getAcl();
                if (null === $acl) {
                    continue;
                }
                if (in_array(VoteOptions::VIEW_ALL_TASKS_IN_PROJECT, $acl, true)) {
                    $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'][] = $uhp->getProject()->getId();
                    continue;
                }
                if (in_array(VoteOptions::VIEW_COMPANY_TASKS_IN_PROJECT, $acl, true)) {
                    $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'][] = $uhp->getProject()->getId();
                    continue;
                }
            }
        }

        $usersCompany = $loggedUser->getCompany();
        $companyId = false;
        if ($usersCompany instanceof Company) {
            $companyId = $usersCompany->getId();
        }

        $usersTasks = $this->em->getRepository('APITaskBundle:Task')->getAllUsersTasks($page, $loggedUser->getId(), $companyId, $dividedProjects, $optionsNeeded);
        $usersTasksCount = $this->em->getRepository('APITaskBundle:Task')->countAllUsersTasks($loggedUser->getId(), $companyId, $dividedProjects, $optionsNeeded);

        return [
            'tasks' => $usersTasks,
            'count' => $usersTasksCount
        ];
    }
}