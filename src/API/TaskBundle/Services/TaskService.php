<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Security\ProjectAclOptions;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
    public function getTaskResponse(array $ids): array
    {
        $taskId = $ids['taskId'];

        try {
            return [
                'data' => $this->em->getRepository('APITaskBundle:Task')->getTask($taskId),
                '_links' => $this->getTaskLinks($ids)
            ];
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        }
    }

    /**
     * Return user's allowed tasks ID based on his ACL
     *
     * @param User $user
     * @return array
     */
    public function getUsersViewTasksId(User $user): array
    {
        $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'] = [0];
        $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'] = [0];
        $dividedProjects['VIEW_OWN_TASKS'] = [0];

        $userHasProjects = $user->getUserHasProjects();
        /** @var UserHasProject $userHasProject */
        foreach ($userHasProjects as $userHasProject) {
            /** @var Project $project */
            $acl = $userHasProject->getAcl();
            if (null === $acl) {
                continue;
            }
            if (\in_array(ProjectAclOptions::VIEW_ALL_TASKS, $acl, true)) {
                $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'][] = $userHasProject->getProject()->getId();
                continue;
            }
            if (\in_array(ProjectAclOptions::VIEW_TASKS_FROM_USERS_COMPANY, $acl, true)) {
                $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'][] = $userHasProject->getProject()->getId();
                continue;
            }
            if (\in_array(ProjectAclOptions::VIEW_OWN_TASKS, $acl, true)) {
                $dividedProjects['VIEW_OWN_TASKS'][] = $userHasProject->getProject()->getId();
                continue;
            }
        }

        $response = $this->em->getRepository('APITaskBundle:Task')->getUsersTasksId($dividedProjects, $user->getId(), $user->getCompany()->getId());
        return $response;
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    private function getTaskLinks(array $ids): array
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
}