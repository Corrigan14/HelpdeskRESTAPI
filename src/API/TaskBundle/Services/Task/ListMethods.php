<?php

namespace API\TaskBundle\Services\Task;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Services\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class ListMethods
 *
 * @package API\TaskBundle\Services
 */
class ListMethods
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
     * @param Filter $filter
     * @return array
     */
    public function getList(array $options, Filter $filter): array
    {
        if ($filter->getReport()) {
            return $this->getTasksResponseForReport($options);
        }

        return $this->getTasksResponse($options);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getListOfCompaniesForCompanyReport(array $options): array
    {
        return $this->em->getRepository('APITaskBundle:Task')->getCompaniesWithTasksFulfillingOptions($options);
    }

    /**
     * Return Tasks Response which includes Data, Links and is based on Pagination, Project, Creator and/or Requested user
     *
     * @param array $options
     *
     * @return array
     */
    public function getTasksResponse(array $options): array
    {
        $data = $this->getRequiredTasks($options);
        $tasks = $data['tasks'];

        $count = $data['count'];
        $url = $this->router->generate('tasks_list');
        $limit = $options['limit'];
        $filters = $options['filtersForUrl'];

        $response['data'] = $tasks;
        $pagination = PaginationHelper::getPagination($url, $limit, $options['page'], $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getTasksResponseForReport(array $options): array
    {
        $tasks = $this->em->getRepository('APITaskBundle:Task')->getAllAdminTasks($options);

        $url = $this->router->generate('tasks_list');
        $limit = $options['limit'];
        $filters = $options['filtersForUrl'];

        if (999 !== $limit) {
            $pagination = PaginationHelper::getPagination($url, $limit, $options['page'], $tasks['count'], $filters);
            $response['data'] = $tasks['array'];

            return array_merge($response, $pagination);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $options['page'], \count($tasks['array']), $filters);
        $response['data'] = $tasks['array'];

        return array_merge($response, $pagination);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getTasksResponseForCompanyReport(array $options, int $companyId): array
    {
        $tasks = $this->em->getRepository('APITaskBundle:Task')->getTasksResponseForCompanyReport($options, $companyId);

        $url = $this->router->generate('tasks_list');
        $limit = $options['limit'];
        $filters = $options['filtersForUrl'];

        if (999 !== $limit) {
            $pagination = PaginationHelper::getPagination($url, $limit, $options['page'], $tasks['count'], $filters);
            $response['data'] = $tasks['array'];

            return array_merge($response, $pagination);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $options['page'], \count($tasks['array']), $filters);
        $response['data'] = $tasks['array'];

        return array_merge($response, $pagination);
    }

    /**
     * Return Tasks based on User's ACL
     *
     * @param array $options
     * @return array
     */
    private function getRequiredTasks(array $options): array
    {
        /** @var User $loggedUser */
        $loggedUser = $options['loggedUser'];
        $isAdmin = $options['isAdmin'];
        $limit = $options['limit'];
        $page = $options['page'];

        // Return's all Tasks - logged user is ADMIN
        if ($isAdmin) {
            $response = $this->em->getRepository('APITaskBundle:Task')->getAllAdminTasks($options);

            if (999 !== $limit) {
                return [
                    'tasks' => $response['array'],
                    'count' => $response['count']
                ];
            }

            return [
                'tasks' => $response['array'],
                'count' => \count($response['array'])
            ];
        }

        // Return's tasks based on loggedUser ACL
        // User Can view: - all tasks which created and which are requested by him - also tasks which are not in projects
        //               - tasks from projects: VIEW_ALL_TASKS_IN_PROJECT, VIEW_COMPANY_TASKS_IN_PROJECT,

        // Divide user's projects based on his ACL

        // 1. User can view all tasks in projects which he created
        $usersProjects = $loggedUser->getProjects();
        $dividedProjects = [];
        if (\count($usersProjects) > 0) {
            /** @var Project $up */
            foreach ($usersProjects as $up) {
                $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'][] = $up->getId();
            }
        }

        // 2. User can view projects tasks based on userHasProject ACL
        $userHasProjects = $loggedUser->getUserHasProjects();
        if (\count($userHasProjects) > 0) {
            /** @var UserHasProject $uhp */
            foreach ($userHasProjects as $uhp) {
                $acl = $uhp->getAcl();
                if (null === $acl) {
                    continue;
                }
                if (\in_array(ProjectAclOptions::VIEW_ALL_TASKS, $acl, true)) {
                    $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'][] = $uhp->getProject()->getId();
                    continue;
                }
                if (\in_array(ProjectAclOptions::VIEW_TASKS_FROM_USERS_COMPANY, $acl, true)) {
                    $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'][] = $uhp->getProject()->getId();
                    continue;
                }
                if (\in_array(ProjectAclOptions::VIEW_OWN_TASKS, $acl, true)) {
                    $dividedProjects['VIEW_OWN_TASKS'][] = $uhp->getProject()->getId();
                    continue;
                }
            }
        }

        $usersCompany = $loggedUser->getCompany();
        $companyId = false;
        if ($usersCompany instanceof Company) {
            $companyId = $usersCompany->getId();
        }

        $response = $this->em->getRepository('APITaskBundle:Task')->getAllUsersTasks($page, $loggedUser->getId(), $companyId, $dividedProjects, $options);

        if (999 !== $limit) {
            return [
                'tasks' => $response['array'],
                'count' => $response['count']
            ];
        }

        return [
            'tasks' => $response['array'],
            'count' => \count($response['array'])
        ];
    }

}