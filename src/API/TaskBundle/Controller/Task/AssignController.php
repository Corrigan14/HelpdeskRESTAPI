<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Security\StatusOptions;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AssignController
 *
 * @package API\TaskBundle\Controller\Task
 */
class AssignController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *          "0":
     *          {
     *            "id": 11,
     *            "username": "admin",
     *            "email": "admin@admin.sk",
     *            "roles": "[\"ROLE_ADMIN\"]",
     *            "is_active": true,
     *            "acl": "[]"
     *          },
     *          "1":
     *          {
     *            "id": 12,
     *            "username": "user",
     *            "email": "user@user.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "acl": "[]"
     *          },
     *        },
     *        "_links":
     *        {
     *          "self": "/api/v1/task-bundle/tasks/7/assign-user?page=1",
     *          "first": "/api/v1/task-bundle/tasks/7/assign-user?page=1",
     *          "prev": false,
     *          "next": false,
     *          "last": "/api/v1/task-bundle/tasks/7/assign-user?page=1"
     *        },
     *        "total": 3,
     *        "page": 1,
     *        "numberOfPages": 1
     *      }
     *
     * @ApiDoc(
     *  description="Returns array of users assigned to task",
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
     *      201 ="The request has succeeded",
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
    public function listOfTasksAssignedUsersAction(Request $request, int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_LIST_OF_USERS_ASSIGNED_TO_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;

        $options['task'] = $task;
        $routeOptions = [
            'routeName' => 'tasks_list_of_tasks_assigned_users',
            'routeParams' => ['taskId' => $taskId]
        ];

        $assignedUsersArray = $this->get('task_additional_service')->getUsersAssignedToTaskResponse($options, $page, $routeOptions);
        return $this->createApiResponse($assignedUsersArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ]
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  description="Assign task to the user - create taskHasAssignedUser Entity.
     *  Status of this task is set to StatusOption: NEW or requested Status.
     *  Returns processed task",
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
     *       "description"="The id of tag"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\TaskHasAssignedUser"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      201 ="The task was successfully assigned to the user",
     *      400 ="Bad Request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @param int $userId
     * @param int|bool $statusId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAssignUserToTaskAction(Request $request, int $taskId, int $userId, $statusId = false)
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
            'user' => $user
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::ASSIGN_USER_TO_TASK, $options)) {
            return $this->accessDeniedResponse();
        }

        if ($statusId) {
            $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($statusId);

            if (!$status instanceof Status) {
                return $this->createApiResponse([
                    'message' => 'Status with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
        } else {
            $newStatus = $this->getDoctrine()->getRepository('APITaskBundle:Status')->findOneBy([
                'title' => StatusOptions::NEW,
            ]);
            if (!$newStatus instanceof Status) {
                return $this->createApiResponse([
                    'message' => 'New Status Entity does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }else{
                $status = $newStatus;
            }
        }

        if ($this->canAssignUserToTask($task, $user)) {
            $taskHasAssignedUser = new TaskHasAssignedUser();
            $taskHasAssignedUser->setTask($task);
            $taskHasAssignedUser->setStatus($status);
            $taskHasAssignedUser->setUser($user);

            $requestData = $request->request->all();
            return $this->updateTaskHasAssignUserEntity($taskHasAssignedUser, $requestData, true);
        }

        return $this->createApiResponse([
            'message' => 'User is already assigned to this task!',
        ], StatusCodesHelper::BAD_REQUEST_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ]
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  description="Assign task to the user - create taskHasAssignedUser Entity.
     *  Status of this task is set to StatusOption: NEW or requested Status.
     *  Returns processed task",
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
     *       "description"="The id of tag"
     *     },
     *     {
     *       "name"="statusId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of tag"
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
     *      200 ="The task was successfully updated",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @param int $userId
     * @param int $statusId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAssignUserToTaskAction(Request $request, int $taskId, int $userId, int $statusId)
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

        $taskHasAssignedEntity = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy([
            'user' => $user,
            'task' => $task
        ]);

        if (!$taskHasAssignedEntity instanceof TaskHasAssignedUser) {
            return $this->createApiResponse([
                'message' => 'Requested user is not assigned to the requested task!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_ASSIGN_USER_TO_TASK, $taskHasAssignedEntity)) {
            return $this->accessDeniedResponse();
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($statusId);

        if (!$status instanceof Status) {
            return $this->createApiResponse([
                'message' => 'Status with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $taskHasAssignedEntity->setStatus($status);
        $status->addTaskHasAssignedUser($taskHasAssignedEntity);

        $requestData = $request->request->all();

        $errors = $this->get('entity_processor')->processEntity($taskHasAssignedEntity, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($taskHasAssignedEntity);
            $this->getDoctrine()->getManager()->persist($status);
            $this->getDoctrine()->getManager()->flush();

            if ($task->getProject()) {
                $projectId = $task->getProject()->getId();
            } else {
                $projectId = false;
            }
            $ids = [
                'id' => $taskId,
                'projectId' => $projectId,
                'requesterId' => $task->getRequestedBy()->getId(),
            ];
            $response = $this->get('task_service')->getTaskResponse($ids);
            $responseData['data'] = $response['data'][0];
            $responseLinks['_links'] = $response['_links'];

            return $this->json(array_merge($responseData, $responseLinks), StatusCodesHelper::SUCCESSFUL_CODE);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @ApiDoc(
     *  description="Delete taskHasAssignedUser Entity. Returns Task.",
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
     *       "description"="The id of user"
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
     *      204 ="Assigned user was successfully removed from task",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $taskId
     * @param int $userId
     * @return Response
     * @throws \LogicException
     */
    public function removeAssignUserFromTaskAction(int $taskId, int $userId)
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

        $taskHasAssignedEntity = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy([
            'user' => $user,
            'task' => $task
        ]);

        if (!$taskHasAssignedEntity instanceof TaskHasAssignedUser) {
            return $this->createApiResponse([
                'message' => 'Requested user is not assigned to the requested task!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_ASSIGN_USER_FROM_TASK, $taskHasAssignedEntity)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($taskHasAssignedEntity);
        $this->getDoctrine()->getManager()->flush();

        if ($task->getProject()) {
            $projectId = $task->getProject()->getId();
        } else {
            $projectId = false;
        }
        $ids = [
            'id' => $taskId,
            'projectId' => $projectId,
            'requesterId' => $task->getRequestedBy()->getId(),
        ];
        $response = $this->get('task_service')->getTaskResponse($ids);
        $responseData['data'] = $response['data'][0];
        $responseLinks['_links'] = $response['_links'];

        return $this->json(array_merge($responseData, $responseLinks), StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param Task $task
     * @param User $user
     * @return bool
     */
    private function canAssignUserToTask(Task $task, User $user): bool
    {
        $assignedUsersArray = $this->getArrayOfUsersAssignedToTask($task);

        if (in_array($user, $assignedUsersArray, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param TaskHasAssignedUser $taskHasAssignedUser
     * @param array $requestData
     * @param bool $create
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    private function updateTaskHasAssignUserEntity(TaskHasAssignedUser $taskHasAssignedUser, array $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($taskHasAssignedUser, $requestData);

        if (false === $errors) {
            $task = $taskHasAssignedUser->getTask();
            $task->addTaskHasAssignedUser($taskHasAssignedUser);
            $user = $taskHasAssignedUser->getUser();
            $user->addTaskHasAssignedUser($taskHasAssignedUser);
            $this->getDoctrine()->getManager()->persist($taskHasAssignedUser);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            if ($task->getProject()) {
                $projectId = $task->getProject()->getId();
            } else {
                $projectId = false;
            }
            $ids = [
                'id' => $task->getId(),
                'projectId' => $projectId,
                'requesterId' => $task->getRequestedBy()->getId(),
            ];
            $response = $this->get('task_service')->getTaskResponse($ids);
            $responseData['data'] = $response['data'][0];
            $responseLinks['_links'] = $response['_links'];

            return $this->json(array_merge($responseData, $responseLinks), $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param Task $task
     * @return array
     */
    private function getArrayOfUsersAssignedToTask(Task $task): array
    {
        $assignedUsers = $task->getTaskHasAssignedUsers();
        $assignedUsersArray = [];

        /** @var TaskHasAssignedUser $au */
        foreach ($assignedUsers as $au) {
            $assignedUsersArray[] = $au->getUser();
        }

        return $assignedUsersArray;
    }
}