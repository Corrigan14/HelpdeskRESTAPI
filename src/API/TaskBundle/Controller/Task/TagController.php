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
     *          {
     *             "id": 72,
     *             "title": "Work",
     *             "color": "4871BF"
     *          },
     *          {
     *             "id": 73,
     *             "title": "Home",
     *             "color": "DFD112"
     *          }
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

        $options['task'] = $task;

        $tagsArray = $this->get('task_additional_service')->getTaskTagsResponse($options);
        return $this->json($tagsArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": 62020,
     *            "title": "Task 3 - admin is creator, admin is requested",
     *            "description": "Description of Task 3",
     *            "deadline": null,
     *            "startedAt": null,
     *            "closedAt": null,
     *            "important": false,
     *            "work": null,
     *            "work_time": null,
     *            "createdAt":1506434914,
     *            "updatedAt":1506434914
     *            "createdBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk",
     *               "name": null,
     *               "surname": null
     *            },
     *            "requestedBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk",
     *               "name": null,
     *               "surname": null
     *            },
     *            "project":
     *            {
     *               "id": 284,
     *               "title": "Project of user 1"
     *             },
     *            "company":
     *            {
     *               "id": 1802,
     *               "title": "Web-Solutions"
     *            },
     *            "taskData":
     *            [
     *              {
     *                 "id": 113,
     *                 "value": "some input",
     *                 "taskAttribute":
     *                 {
     *                    "id": 169,
     *                    "title": "input task additional attribute"
     *                  }
     *               }
     *            ],
     *            "followers":
     *            [
     *              {
     *                 "id": 2575,
     *                 "username": "admin",
     *                 "email": "admin@admin.sk",
     *                 "name": null,
     *                 "surname": null
     *               }
     *            ],
     *            "tags":
     *            [
     *               {
     *                  "id": 71,
     *                  "title": "Free Time",
     *                  "color": "BF4848"
     *               },
     *               {
     *                  "id": 73,
     *                  "title": "Home",
     *                  "color": "DFD112"
     *                }
     *            ],
     *            "taskHasAssignedUsers":
     *            [
     *               {
     *                  "id": 69,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt": 1506434914,
     *                  "updatedAt": 1506434914,
     *                  "status":
     *                  {
     *                     "id": 240,
     *                     "title": "Completed",
     *                     "color": "#FF4500"
     *                  },
     *                  "user":
     *                  {
     *                      "id": 2579,
     *                      "username": "user",
     *                      "email": "user@user.sk",
     *                      "name": null,
     *                      "surname": null
     *                   }
     *                }
     *            ],
     *            "taskHasAttachments":
     *            [
     *               {
     *                   "id": 240,
     *                   "slug": "Slug-of-image-12-14-2015",
     *               }
     *            ],
     *            "comments":
     *            {
     *              "189":
     *              {
     *                  "id": 189,
     *                  "title": "test",
     *                  "body": "gggg 222 222",
     *                  "createdAt": 1506434914,
     *                  "updatedAt": 1506434914,
     *                  "internal": true,
     *                  "email": true,
     *                  "email_to":
     *                  [
     *                      "mb@web-solutions.sk"
     *                  ],
     *                  "email_cc": null,
     *                  "email_bcc": null,
     *                  "createdBy":
     *                  {
     *                      "id": 4031,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk",
     *                      "name": "Admin",
     *                      "surname": "Adminovic",
     *                      "avatarSlug": "slug-15-15-2014"
     *                  },
     *                  "commentHasAttachments":
     *                  [
     *                      {
     *                          "id": 3,
     *                          "slug": "zsskcd-jpg-2016-12-17-15-36"
     *                      }
     *                  ],
     *                  "children": false
     *              },
     *            },
     *             "invoiceableItems":
     *             [
     *                {
     *                   "id": 30,
     *                   "title": "Keyboard",
     *                   "amount": "2.00",
     *                   "unit_price": "50.00",
     *                   "unit":
     *                   {
     *                      "id": 54,
     *                      "title": "Kus",
     *                      "shortcut": "Ks"
     *                   }
     *                },
     *                {
     *                   "id": 31,
     *                   "title": "Mouse",
     *                   "amount": "5.00",
     *                   "unit_price": "10.00",
     *                   "unit":
     *                   {
     *                      "id": 54,
     *                      "title": "Kus",
     *                      "shortcut": "Ks"
     *                    }
     *                },
     *             ],
     *             "canEdit": true,
     *             "follow": true,
     *             "hasProject": true,
     *             "loggedUserIsAdmin": false,
     *             "loggedUserProjectAcl":
     *             [
     *                "edit_project",
     *                "create_task",
     *                "resolve_task",
     *                "delete_task",
     *                "view_internal_note",
     *                "view_all_tasks",
     *                "view_own_tasks",
     *                "view_tasks_from_users_company"
     *             ],
     *             "loggedUserAcl":
     *             [
     *                "login_to_system",
     *                "share_filters",
     *                "project_shared_filters",
     *                "report_filters",
     *                "share_tags",
     *                "create_projects",
     *                "sent_emails_from_comments",
     *                "create_tasks",
     *                "create_tasks_in_all_projects",
     *                "update_all_tasks",
     *                "user_settings",
     *                "user_role_settings",
     *                "company_attribute_settings",
     *                "company_settings",
     *                "status_settings",
     *                "task_attribute_settings",
     *                "unit_settings",
     *                "system_settings",
     *                "smtp_settings",
     *                "imap_settings"
     *              ]
     *           }
     *       },
     *       "_links":
     *       {
     *          "quick update: task": "/api/v1/task-bundle/tasks/quick-update/23996",
     *          "patch: task": "/api/v1/task-bundle/tasks/23996",
     *          "delete": "/api/v1/task-bundle/tasks/23996"
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
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

        // Check if logged user Is ADMIN
        $isAdmin = $this->get('task_voter')->isAdmin();

        $taskArray = $this->get('task_service')->getFullTaskEntity($task, $canEdit, $this->getUser(), $isAdmin);
        return $this->json($taskArray, StatusCodesHelper::CREATED_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": 62020,
     *            "title": "Task 3 - admin is creator, admin is requested",
     *            "description": "Description of Task 3",
     *            "deadline": null,
     *            "startedAt": null,
     *            "closedAt": null,
     *            "important": false,
     *            "work": null,
     *            "work_time": null,
     *            "createdAt":1506434914,
     *            "updatedAt":1506434914
     *            "createdBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk",
     *               "name": null,
     *               "surname": null
     *            },
     *            "requestedBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk",
     *               "name": null,
     *               "surname": null
     *            },
     *            "project":
     *            {
     *               "id": 284,
     *               "title": "Project of user 1"
     *             },
     *            "company":
     *            {
     *               "id": 1802,
     *               "title": "Web-Solutions"
     *            },
     *            "taskData":
     *            [
     *              {
     *                 "id": 113,
     *                 "value": "some input",
     *                 "taskAttribute":
     *                 {
     *                    "id": 169,
     *                    "title": "input task additional attribute"
     *                  }
     *               }
     *            ],
     *            "followers":
     *            [
     *              {
     *                 "id": 2575,
     *                 "username": "admin",
     *                 "email": "admin@admin.sk",
     *                 "name": null,
     *                 "surname": null
     *               }
     *            ],
     *            "tags":
     *            [
     *               {
     *                  "id": 71,
     *                  "title": "Free Time",
     *                  "color": "BF4848"
     *               },
     *               {
     *                  "id": 73,
     *                  "title": "Home",
     *                  "color": "DFD112"
     *                }
     *            ],
     *            "taskHasAssignedUsers":
     *            [
     *               {
     *                  "id": 69,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt": 1506434914,
     *                  "updatedAt": 1506434914,
     *                  "status":
     *                  {
     *                     "id": 240,
     *                     "title": "Completed",
     *                     "color": "#FF4500"
     *                  },
     *                  "user":
     *                  {
     *                      "id": 2579,
     *                      "username": "user",
     *                      "email": "user@user.sk",
     *                      "name": null,
     *                      "surname": null
     *                   }
     *                }
     *            ],
     *            "taskHasAttachments":
     *            [
     *               {
     *                   "id": 240,
     *                   "slug": "Slug-of-image-12-14-2015",
     *               }
     *            ],
     *            "comments":
     *            {
     *              "189":
     *              {
     *                  "id": 189,
     *                  "title": "test",
     *                  "body": "gggg 222 222",
     *                  "createdAt": 1506434914,
     *                  "updatedAt": 1506434914,
     *                  "internal": true,
     *                  "email": true,
     *                  "email_to":
     *                  [
     *                      "mb@web-solutions.sk"
     *                  ],
     *                  "email_cc": null,
     *                  "email_bcc": null,
     *                  "createdBy":
     *                  {
     *                      "id": 4031,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk",
     *                      "name": "Admin",
     *                      "surname": "Adminovic",
     *                      "avatarSlug": "slug-15-15-2014"
     *                  },
     *                  "commentHasAttachments":
     *                  [
     *                      {
     *                          "id": 3,
     *                          "slug": "zsskcd-jpg-2016-12-17-15-36"
     *                      }
     *                  ],
     *                  "children": false
     *              },
     *            },
     *             "invoiceableItems":
     *             [
     *                {
     *                   "id": 30,
     *                   "title": "Keyboard",
     *                   "amount": "2.00",
     *                   "unit_price": "50.00",
     *                   "unit":
     *                   {
     *                      "id": 54,
     *                      "title": "Kus",
     *                      "shortcut": "Ks"
     *                   }
     *                },
     *                {
     *                   "id": 31,
     *                   "title": "Mouse",
     *                   "amount": "5.00",
     *                   "unit_price": "10.00",
     *                   "unit":
     *                   {
     *                      "id": 54,
     *                      "title": "Kus",
     *                      "shortcut": "Ks"
     *                    }
     *                },
     *             ],
     *             "canEdit": true,
     *             "follow": true,
     *             "hasProject": true,
     *             "loggedUserIsAdmin": false,
     *             "loggedUserProjectAcl":
     *             [
     *                "edit_project",
     *                "create_task",
     *                "resolve_task",
     *                "delete_task",
     *                "view_internal_note",
     *                "view_all_tasks",
     *                "view_own_tasks",
     *                "view_tasks_from_users_company"
     *             ],
     *             "loggedUserAcl":
     *             [
     *                "login_to_system",
     *                "share_filters",
     *                "project_shared_filters",
     *                "report_filters",
     *                "share_tags",
     *                "create_projects",
     *                "sent_emails_from_comments",
     *                "create_tasks",
     *                "create_tasks_in_all_projects",
     *                "update_all_tasks",
     *                "user_settings",
     *                "user_role_settings",
     *                "company_attribute_settings",
     *                "company_settings",
     *                "status_settings",
     *                "task_attribute_settings",
     *                "unit_settings",
     *                "system_settings",
     *                "smtp_settings",
     *                "imap_settings"
     *              ]
     *           }
     *       },
     *       "_links":
     *       {
     *          "quick update: task": "/api/v1/task-bundle/tasks/quick-update/23996",
     *          "patch: task": "/api/v1/task-bundle/tasks/23996",
     *          "delete": "/api/v1/task-bundle/tasks/23996"
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
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

            // Check if logged user Is ADMIN
            $isAdmin = $this->get('task_voter')->isAdmin();

            $taskArray = $this->get('task_service')->getFullTaskEntity($task, $canEdit, $this->getUser(), $isAdmin);
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