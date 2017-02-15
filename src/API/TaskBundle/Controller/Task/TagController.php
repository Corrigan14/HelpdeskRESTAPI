<?php

namespace API\TaskBundle\Controller\Task;

use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TagController
 *
 * @package API\TaskBundle\Controller\Task
 */
class TagController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *       "data":
     *       [
     *         {
     *            "id": 41035,
     *            "title": "Task 1",
     *            "description": "Description of Task 1",
     *            "deadline": null,
     *            "startedAt": null,
     *            "closedAt": null,
     *            "important": false,
     *            "work": null,
     *            "work_time": null,
     *            "createdAt":
     *            {
     *               "date": "2017-02-10 15:47:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "updatedAt":
     *            {
     *               "date": "2017-02-10 15:47:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "tags":
     *            [
     *               {
     *                  "id": 44,
     *                  "title": "Work",
     *                  "color": "4871BF",
     *                  "public": true
     *               },
     *               {
     *                  "id": 45,
     *                  "title": "Home",
     *                  "color": "DFD112",
     *                  "public": false
     *                }
     *             ]
     *           }
     *       ],
     *       "_links": [],
     *       "total": 2
     *     }
     *
     * @ApiDoc(
     *  description="Returns array of task tags.",
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listOfTasksTagsAction(int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_LIST_OF_TASK_TAGS, $task)) {
            return $this->accessDeniedResponse();
        }

        $page = 1;

        $options['task'] = $task;
        $routeOptions = [
            'routeName' => 'tasks_list_of_tasks_tags',
            'routeParams' => ['taskId' => $taskId]
        ];

        $tagsArray = $this->get('task_additional_service')->getTaskTagsResponse($options, $page, $routeOptions);
        return $this->json($tagsArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *  description="Add a new Tag to the Task. Returns Task Entity.",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="tagId",
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
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $tagId
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function addTagToTaskAction(int $taskId, int $tagId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($tagId);

        if (!$tag instanceof Tag) {
            return $this->createApiResponse([
                'message' => 'Tag with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task,
            'tag' => $tag
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TAG_TO_TASK, $options)) {
            return $this->accessDeniedResponse();
        }

        if ($this->canAddTagToTask($task, $tag)) {
            $task->addTag($tag);
            $tag->addTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($tag);
            $this->getDoctrine()->getManager()->flush();
        }

        // Check if user can update selected task
        if ($this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $canEdit = true;
        } else {
            $canEdit = false;
        }

        $taskArray = $this->get('task_service')->getFullTaskEntity($task, $canEdit, $this->getUser()->getId());
        return $this->json($taskArray, StatusCodesHelper::CREATED_CODE);
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
     *  description="Remove the Tag from the Task. Returns Task Entity.",
     *  requirements={
     *     {
     *       "name"="tagId",
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
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The tag was successfully removed from task",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $tagId
     * @return Response
     * @throws \LogicException
     */
    public function removeTagFromTaskAction(int $taskId, int $tagId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($tagId);

        if (!$tag instanceof Tag) {
            return $this->createApiResponse([
                'message' => 'Tag with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task,
            'tag' => $tag
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_TAG_FROM_TASK, $options)) {
            return $this->accessDeniedResponse();
        }

        if (!$this->canAddTagToTask($task, $tag)) {
            $task->removeTag($tag);
            $tag->removeTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($tag);
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

        return $this->createApiResponse([
            'message' => 'Task does not contains requested tag!',
        ], StatusCodesHelper::NOT_FOUND_CODE);
    }

    /**
     * @param Task $task
     * @param Tag $tag
     * @return bool
     */
    private function canAddTagToTask(Task $task, Tag $tag): bool
    {
        $taskHasTags = $task->getTags();

        if (in_array($tag, $taskHasTags->toArray(), true)) {
            return false;
        }
        return true;
    }
}