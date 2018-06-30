<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Repository\TaskRepository;
use API\TaskBundle\Security\ProjectAclOptions;
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
    public function getTasksResponse(int $page, array $options): array
    {
        $data = $this->getRequiredTasks($page, $options);
        $tasks = $data['tasks'];
        $count = $data['count'];

        $response = [
            'data' => $tasks,
        ];

        $url = $this->router->generate('tasks_list');
        $limit = $options['limit'];
        $filters = $options['filtersForUrl'];

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param Task $task
     * @param bool $canEdit
     * @param  User $loggedUser
     * @param  bool $isAdmin
     * @return array
     */
    public function getFullTaskEntity(Task $task, bool $canEdit, User $loggedUser, bool $isAdmin): array
    {
        $responseData = [];
        $responseLinks = [];
        $projectAcl = [];

        $project = $task->getProject();
        if ($project instanceof Project) {
            // Return Project Acl
            $userHasProject = $this->em->getRepository('APITaskBundle:UserHasProject')->findOneBy([
                'user' => $loggedUser,
                'project' => $project
            ]);

            if ($userHasProject instanceof UserHasProject) {
                $projectAcl = $userHasProject->getAcl();
            }
            $hasProject = true;
        } else {
            $hasProject = false;
        }

        $followers = $task->getFollowers();
        $followTask = false;
        if (\count($followers) > 0) {
            $followersId = [];
            foreach ($followers as $follower) {
                $followersId[] = $follower->getId();
            }
            if (\in_array($loggedUser->getId(), $followersId, true)) {
                $followTask = true;
            } else {
                $followTask = false;
            }
        }

        $ids = [
            'taskId' => $task->getId(),
            'projectId' => $task->getProject()->getId(),
            'companyId' => $task->getCompany()->getId(),
            'requesterId' => $task->getRequestedBy()->getId(),
            'statusId' => $task->getStatus()->getId()
        ];

        /** @var UserRole $loggedUserRole */
        $loggedUserRole = $loggedUser->getUserRole();

        $response = $this->getTaskResponse($ids);

        $responseData['data'] = $response['data'];
        $responseData['data']['canEdit'] = $canEdit;
        $responseData['data']['follow'] = $followTask;
        $responseData['data']['hasProject'] = $hasProject;
        $responseData['data']['loggedUserIsAdmin'] = $isAdmin;
        $responseData['data']['loggedUserProjectAcl'] = $projectAcl;
        $responseData['data']['loggedUserRoleAcl'] = $loggedUserRole->getAcl();
        $responseLinks['_links'] = $response['_links'];

        return array_merge($responseData, $responseLinks);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getTaskResponse(array $ids):array
    {
        $taskId = $ids['taskId'];

        return [
            'data' => $this->em->getRepository('APITaskBundle:Task')->getTask($taskId),
            '_links' => $this->getTaskLinks($ids)
        ];
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    private function getTaskLinks(array $ids):array
    {
        $taskId = $ids['taskId'];
        $projectId = $ids['projectId'];
        $statusId = $ids['statusId'];
        $requesterId = $ids['requesterId'];
        $companyId = $ids['companyId'];

        $baseUrl = [
            'update 1' => $this->router->generate('tasks_update_project_status_requester_company', ['taskId' => $taskId, 'projectId' => $projectId, 'statusId' => $statusId, 'requesterId' => $requesterId, 'companyId' => $companyId]),
            'update 2' => $this->router->generate('tasks_update_project', ['taskId' => $taskId, 'projectId' => $projectId]),
            'update 3' => $this->router->generate('tasks_update_project_status', ['taskId' => $taskId, 'projectId' => $projectId, 'statusId' => $statusId]),
            'update 4' => $this->router->generate('tasks_update_project_status_requester', ['taskId' => $taskId, 'projectId' => $projectId, 'statusId' => $statusId, 'requesterId' => $requesterId]),
            'update 5' => $this->router->generate('tasks_update_status', ['taskId' => $taskId, 'statusId' => $statusId]),
            'update 6' => $this->router->generate('tasks_update_status_requester', ['taskId' => $taskId, 'statusId' => $statusId, 'requesterId' => $requesterId]),
            'update 7' => $this->router->generate('tasks_update_status_requester_company', ['taskId' => $taskId, 'statusId' => $statusId, 'requesterId' => $requesterId, 'companyId' => $companyId]),
            'update 8' => $this->router->generate('tasks_update_requester', ['taskId' => $taskId, 'requesterId' => $requesterId]),
            'update 9' => $this->router->generate('tasks_update_requester_company', ['taskId' => $taskId, 'requesterId' => $requesterId, 'companyId' => $companyId]),
            'update 10' => $this->router->generate('tasks_update_company', ['taskId' => $taskId, 'companyId' => $companyId]),
            'delete' => $this->router->generate('tasks_delete', ['taskId' => $taskId])
        ];

        return $baseUrl;
    }

    /**
     * Return Tasks based on User's ACL
     *
     * @param int $page
     * @param array $options
     * @return array
     */
    private function getRequiredTasks(int $page, array $options): array
    {
        /** @var User $loggedUser */
        $loggedUser = $options['loggedUser'];
        $isAdmin = $options['isAdmin'];
        $limit = $options['limit'];

        // Return's all Tasks - logged user is ADMIN
        if ($isAdmin) {
            $response = $this->em->getRepository('APITaskBundle:Task')->getAllAdminTasks($page, $options);

            if (999 !== $limit) {
                $count = $response['count'];
            } else {
                $count = \count($response['array']);
            }

            return [
                'tasks' => $response['array'],
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
            $count = $response['count'];
        } else {
            $count = \count($response['array']);
        }

        return [
            'tasks' => $response['array'],
            'count' => $count
        ];
    }
}