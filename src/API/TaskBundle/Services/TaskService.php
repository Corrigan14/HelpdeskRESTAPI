<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Repository\TaskRepository;
use API\TaskBundle\Security\ProjectAclOptions;
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

        $url = $this->router->generate('tasks_list');
        $limit = TaskRepository::LIMIT;
        $filters = $options['filtersForUrl'];

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getTaskResponse(array $ids)
    {
        $id = $ids['id'];

        return [
            'data' => $this->em->getRepository('APITaskBundle:Task')->getTask($id),
            '_links' => $this->getTaskLinks($ids)
        ];
    }

    /**
     * @param Task $task
     * @param bool $canEdit
     * @param int $loggedUserId
     * @return array
     */
    public function getFullTaskEntity(Task $task, bool $canEdit, int $loggedUserId):array
    {
        $responseData = [];
        $responseLinks = [];

        if ($task->getProject() instanceof Project) {
            $projectId = $task->getProject()->getId();
        } else {
            $projectId = false;
        }

        $followers = $task->getFollowers();
        $followTask = false;
        if (count($followers) > 0) {
            $followersId = [];
            foreach ($followers as $follower) {
                $followersId[] = $follower->getId();
            }
            if (in_array($loggedUserId, $followersId)) {
                $followTask = true;
            } else {
                $followTask = false;
            }
        }

        $ids = [
            'id' => $task->getId(),
            'projectId' => $projectId,
            'requesterId' => $task->getRequestedBy()->getId(),
        ];

        $response = $this->getTaskResponse($ids);
        $responseData['data'] = $response['data'][0];
        $responseData['data']['canEdit'] = $canEdit;
        $responseData['data']['follow'] = $followTask;
        $responseLinks['_links'] = $response['_links'];

        return array_merge($responseData, $responseLinks);
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    private function getTaskLinks(array $ids)
    {
        $id = $ids['id'];
        $projectId = $ids['projectId'];
        $requesterId = $ids['requesterId'];

        $baseUrl = [
            'put: task' => $this->router->generate('tasks_update', ['id' => $id]),
            'patch: task' => $this->router->generate('tasks_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('tasks_delete', ['id' => $id]),
        ];

        $projectUrl = [];
        if ($projectId) {
            $projectUrl = [
                'put: tasks project' => $this->router->generate('tasks_update_project', ['id' => $id, 'projectId' => $projectId]),
                'patch: tasks project' => $this->router->generate('tasks_partial_update_project', ['id' => $id, 'projectId' => $projectId]),
            ];
        }

        $requesterUrl = [];
        if ($requesterId) {
            $requesterUrl = [
                'put: tasks requester' => $this->router->generate('tasks_update_requester', ['id' => $id, 'requestedUserId' => $requesterId]),
                'patch: tasks requester' => $this->router->generate('tasks_partial_update_requester', ['id' => $id, 'requestedUserId' => $requesterId]),
            ];
        }

        $reqProjUrl = [];
        if ($requesterId && $projectId) {
            $reqProjUrl = [
                'put: tasks project and requester' => $this->router->generate('tasks_update_project_and_requester', ['id' => $id, 'projectId' => $projectId, 'requestedUserId' => $requesterId]),
                'patch: tasks project and requester' => $this->router->generate('tasks_partial_update_project_and_requester', ['id' => $id, 'projectId' => $projectId, 'requestedUserId' => $requesterId]),
            ];
        }

        return array_merge($baseUrl, $requesterUrl, $projectUrl, $reqProjUrl);
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
                if (in_array(ProjectAclOptions::VIEW_ALL_TASKS, $acl, true)) {
                    $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'][] = $uhp->getProject()->getId();
                    continue;
                }
                if (in_array(ProjectAclOptions::VIEW_TASKS_FROM_USERS_COMPANY, $acl, true)) {
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

        $usersTasks = $this->em->getRepository('APITaskBundle:Task')->getAllUsersTasks($page, $loggedUser->getId(), $companyId, $dividedProjects, $options);
        $usersTasksCount = $this->em->getRepository('APITaskBundle:Task')->countAllUsersTasks($loggedUser->getId(), $companyId, $dividedProjects, $options);

        return [
            'tasks' => $usersTasks,
            'count' => $usersTasksCount
        ];
    }
}