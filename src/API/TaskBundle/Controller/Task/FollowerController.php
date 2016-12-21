<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FollowerController
 *
 * @package API\TaskBundle\Controller\Task
 */
class FollowerController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *         "data":
     *         {
     *            "0":
     *            {
     *               "id": 11,
     *               "username": "admin",
     *               "password": "$2y$13$Mmruh1j0Y07twkywF4TvIO9WMOYISWrXMDPa24nls7mm3QMvRE10q",
     *               "email": "admin@admin.sk",
     *               "roles": "[\"ROLE_ADMIN\"]",
     *               "is_active": true,
     *               "acl": "[]"
     *            }
     *         },
     *         "_links":
     *         {
     *              "self": "/api/v1/task-bundle/tasks/7/follower?page=1",
     *              "first": "/api/v1/task-bundle/tasks/7/follower?page=1",
     *              "prev": false,
     *              "next": false,
     *              "last": "/api/v1/task-bundle/tasks/7/follower?page=1"
     *         },
     *         "total": 1,
     *         "page": 1,
     *         "numberOfPages": 1
     *      }
     *
     * @ApiDoc(
     *  description="Returns array of tasks followers",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     }
     *  },
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @return Response
     * @throws \LogicException
     */
    public function listOfTasksFollowersAction(Request $request, int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_LIST_OF_TASK_FOLLOWERS, $task)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;

        $options['task'] = $task;
        $routeOptions = [
            'routeName' => 'tasks_list_of_tasks_followers',
            'routeParams' => ['taskId' => $taskId]
        ];

        $followersArray = $this->get('task_additional_service')->getTaskFollowerResponse($options, $page, $routeOptions);
        return $this->createApiResponse($followersArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *         "0":
     *         {
     *            "id": 85,
     *            "username": "admin",
     *            "email": "admin@admin.sk",
     *            "roles": "[\"ROLE_ADMIN\"]",
     *            "is_active": true,
     *            "acl": "[]",
     *            "company": ⊕{...}
     *         },
     *         "1":
     *         {
     *            "id": 87,
     *            "username": "testuser2",
     *            "email": "testuser2@user.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "acl": "[]",
     *            "company": ⊕{...}
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Add a new follower to the Task. Returns a list of tasks followers",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="userId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user followed task"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $userId
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function addFollowerToTaskAction(int $taskId, int $userId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task,
            'follower' => $user
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TASK_FOLLOWER, $options)) {
            return $this->accessDeniedResponse();
        }

        if ($this->canAddTaskFollower($user, $task)) {
            $task->addFollower($user);
            $user->addFollowedTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
        }

        $listOfTaskFollowers = $task->getFollowers();
        return $this->createApiResponse($listOfTaskFollowers, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *       {
     *         "0":
     *         {
     *            "id": 85,
     *            "username": "admin",
     *            "email": "admin@admin.sk",
     *            "roles": "[\"ROLE_ADMIN\"]",
     *            "is_active": true,
     *            "acl": "[]",
     *            "company": ⊕{...}
     *         },
     *         "1":
     *         {
     *            "id": 87,
     *            "username": "testuser2",
     *            "email": "testuser2@user.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "acl": "[]",
     *            "company": ⊕{...}
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Remove the follower from the Task. Returns array of tasks followers",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="userId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user followed task"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The follower was successfully removed",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $userId
     * @return Response
     * @throws \LogicException
     */
    public function removeFollowerFromTaskAction(int $taskId, int $userId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task,
            'follower' => $user
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_TASK_FOLLOWER, $options)) {
            return $this->accessDeniedResponse();
        }

        if (!$this->canAddTaskFollower($user, $task)) {
            $task->removeFollower($user);
            $user->removeFollowedTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            $listOfTaskFollowers = $task->getFollowers();
            return $this->createApiResponse($listOfTaskFollowers, StatusCodesHelper::SUCCESSFUL_CODE);
        }

        return $this->notFoundResponse();
    }

    /**
     * @param User $user
     * @param Task $task
     * @return bool
     * @throws \LogicException
     */
    private function canAddTaskFollower(User $user, Task $task): bool
    {
        $taskHasFollower = $task->getFollowers();

        if (in_array($user, $taskHasFollower->toArray(), true)) {
            return false;
        }
        return true;
    }

}