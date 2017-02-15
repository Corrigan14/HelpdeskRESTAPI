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
     *         [
     *           {
     *              "id": 44031,
     *              "title": "Task 1999",
     *              "description": "Description of Users Task 1999",
     *              "deadline": null,
     *              "startedAt": null,
     *              "closedAt": null,
     *              "important": true,
     *              "work": null,
     *              "work_time": null,
     *              "createdAt":
     *              {
     *                  "date": "2017-02-10 15:47:48.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-02-14 11:52:30.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *              },
     *              "followers":
     *              [
     *                 {
     *                     "id": 1855,
     *                     "username": "customer5",
     *                     "password": "$2y$13$P5./aAvPh98PZqOSxHb4ceDeBqxgGVGip.JJvOOXNDLf8T6ei29Wy",
     *                     "email": "customer@customer5.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                 },
     *                 {
     *                     "id": 1856,
     *                     "username": "customer6",
     *                     "password": "$2y$13$2glfkRbwTj4BLtgwEn/ireOzhoZwMJIliPq4Frge0E5SRn/.RMcja",
     *                     "email": "customer@customer6.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                 }
     *              ]
     *           }
     *        ],
     *        "_links": [],
     *        "total": 2
     *      }
     *
     * @ApiDoc(
     *  description="Returns array of tasks followers.",
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
     * @param int $taskId
     * @return Response
     * @throws \LogicException
     */
    public function listOfTasksFollowersAction(int $taskId)
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

        $page = 1;

        $options['task'] = $task;
        $routeOptions = [
            'routeName' => 'tasks_list_of_tasks_followers',
            'routeParams' => ['taskId' => $taskId]
        ];

        $followersArray = $this->get('task_additional_service')->getTaskFollowerResponse($options, $page, $routeOptions);
        return $this->json($followersArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
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
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ],
     *           "invoiceableItems":
     *           [
     *              {
     *                 "id": 4,
     *                 "title": "Keyboard",
     *                 "amount": "2.00",
     *                 "unit_price": "50.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                 "id": 5,
     *                 "title": "Mouse",
     *                 "amount": "5.00",
     *                 "unit_price": "10.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *               },
     *            ],
     *            "canEdit": true,
     *            "follow": false
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
     *  description="Add a new follower to the Task. Returns a task.",
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

            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();
        }

        // Check if user can update selected task
        if ($this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $canEdit = true;
        } else {
            $canEdit = false;
        }

        $taskArray = $this->get('task_service')->getFullTaskEntity($task, $canEdit, $this->getUser()->getId());
        return $this->json($taskArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
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
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ],
     *           "invoiceableItems":
     *           [
     *              {
     *                 "id": 4,
     *                 "title": "Keyboard",
     *                 "amount": "2.00",
     *                 "unit_price": "50.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                 "id": 5,
     *                 "title": "Mouse",
     *                 "amount": "5.00",
     *                 "unit_price": "10.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *               },
     *            ],
     *            "canEdit": true,
     *            "follow": false
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
     *  description="Remove the follower from the Task. Returns Task Entity.",
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

            // Check if user can update selected task
            if ($this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
                $canEdit = true;
            } else {
                $canEdit = false;
            }

            $taskArray = $this->get('task_service')->getFullTaskEntity($task, $canEdit, $this->getUser()->getId());
            return $this->json($taskArray, StatusCodesHelper::SUCCESSFUL_CODE);
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