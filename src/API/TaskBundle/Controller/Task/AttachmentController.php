<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\File;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAttachment;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AttachmentController
 *
 * @package API\TaskBundle\Controller\Task
 */
class AttachmentController extends ApiBaseController
{

    /**
     * ### Response ###
     *      {
     *         "data":
     *         [
     *            {
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *         ],
     *        "_links":
     *        {
     *          "self": "/api/v1/task-bundle/tasks/7/attachment?page=1",
     *          "first": "/api/v1/task-bundle/tasks/7/attachment?page=1",
     *          "prev": false,
     *          "next": false,
     *          "last": "/api/v1/task-bundle/tasks/7/attachment?page=1"
     *        },
     *        "total": 1,
     *        "page": 1,
     *        "numberOfPages": 1
     *      }
     *
     * @ApiDoc(
     *  description="Returns a list of slugs of task attachments",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Limit for Pagination: 999 - returns all entities, null - returns 10 entities"
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
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \LogicException
     */
    public function listOfTasksAttachmentsAction(Request $request, int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_LIST_OF_TASK_ATTACHMENTS, $task)) {
            return $this->accessDeniedResponse();
        }

        $pageNum = $request->get('page');
        $pageNum = (int)$pageNum;
        $page = ($pageNum === 0) ? 1 : $pageNum;

        $limitNum = $request->get('limit');
        $limit = (int)$limitNum ? (int)$limitNum : 10;

        $options = [
            'task' => $taskId,
            'limit' => $limit
        ];
        $routeOptions = [
            'routeName' => 'tasks_list_of_tasks_attachments',
            'routeParams' => ['taskId' => $taskId]
        ];

        $attachmentArray = $this->get('task_additional_service')->getTaskAttachmentsResponse($options, $page, $routeOptions);
        return $this->json($attachmentArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *            "createdAt":
     *            {
     *               "date": "2017-02-27 15:55:15.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "updatedAt":
     *            {
     *               "date": "2017-02-27 15:55:15.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "createdBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk"
     *            },
     *            "requestedBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk"
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
     *                 "email": "admin@admin.sk"
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
     *                  "createdAt":
     *                  {
     *                     "date": "2017-02-27 15:55:17.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-02-27 15:55:17.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
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
     *                      "email": "user@user.sk"
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
     *               "0":
     *               {
     *                  "parent": true,
     *                  "id": 30,
     *                  "title": "Koment - public",
     *                  "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *                  "createdAt":
     *                  {
     *                     "date": "2017-02-27 15:55:17.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-02-27 15:55:17.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                   },
     *                   "internal": false,
     *                   "email": false,
     *                   "email_to": false,
     *                   "email_cc": false,
     *                   "email_bcc": false,
     *                   "createdBy":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                   }
     *               },
     *               "30":
     *               [
     *                  {
     *                      "child": true,
     *                      "parentId": 30,
     *                      "id": 30,
     *                      "title": "Koment - public",
     *                      "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *                      "createdAt":
     *                      {
     *                         "date": "2017-02-27 15:55:17.000000",
     *                         "timezone_type": 3,
     *                         "timezone": "Europe/Berlin"
     *                       },
     *                      "updatedAt":
     *                      {
     *                         "date": "2017-02-27 15:55:17.000000",
     *                         "timezone_type": 3,
     *                         "timezone": "Europe/Berlin"
     *                      },
     *                      "internal": false,
     *                      "email": false,
     *                      "email_to": false,
     *                      "email_cc": false,
     *                      "email_bcc": false,
     *                      "createdBy":
     *                      {
     *                         "id": 2575,
     *                         "username": "admin",
     *                         "email": "admin@admin.sk"
     *                      }
     *                   }
     *                ]
     *             },
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
     *  description="Add a new attachment to the Task. Returns Task Entity.",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="slug",
     *       "dataType"="string",
     *       "description"="The slug of uploaded attachment"
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
     *      201 ="The attachment was successfully added to task",
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param string $slug
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @internal param int $userId
     */
    public function addAttachmentToTaskAction(int $taskId, string $slug)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $file = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug
        ]);

        if (!$file instanceof File) {
            return $this->createApiResponse([
                'message' => 'Attachment with requested Slug does not exist! Attachment has to be uploaded before added to task!',
            ], StatusCodesHelper::BAD_REQUEST_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_ATTACHMENT_TO_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        if ($this->canAddAttachmentToTask($task, $slug)) {
            $taskHasAttachment = new TaskHasAttachment();
            $taskHasAttachment->setTask($task);
            $taskHasAttachment->setSlug($slug);
            $task->addTaskHasAttachment($taskHasAttachment);
            $this->getDoctrine()->getManager()->persist($taskHasAttachment);
            $this->getDoctrine()->getManager()->persist($task);
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
     *            "createdAt":
     *            {
     *               "date": "2017-02-27 15:55:15.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "updatedAt":
     *            {
     *               "date": "2017-02-27 15:55:15.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "createdBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk"
     *            },
     *            "requestedBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk"
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
     *                 "email": "admin@admin.sk"
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
     *                  "createdAt":
     *                  {
     *                     "date": "2017-02-27 15:55:17.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-02-27 15:55:17.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
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
     *                      "email": "user@user.sk"
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
     *               "0":
     *               {
     *                  "parent": true,
     *                  "id": 30,
     *                  "title": "Koment - public",
     *                  "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *                  "createdAt":
     *                  {
     *                     "date": "2017-02-27 15:55:17.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-02-27 15:55:17.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                   },
     *                   "internal": false,
     *                   "email": false,
     *                   "email_to": false,
     *                   "email_cc": false,
     *                   "email_bcc": false,
     *                   "createdBy":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                   }
     *               },
     *               "30":
     *               [
     *                  {
     *                      "child": true,
     *                      "parentId": 30,
     *                      "id": 30,
     *                      "title": "Koment - public",
     *                      "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *                      "createdAt":
     *                      {
     *                         "date": "2017-02-27 15:55:17.000000",
     *                         "timezone_type": 3,
     *                         "timezone": "Europe/Berlin"
     *                       },
     *                      "updatedAt":
     *                      {
     *                         "date": "2017-02-27 15:55:17.000000",
     *                         "timezone_type": 3,
     *                         "timezone": "Europe/Berlin"
     *                      },
     *                      "internal": false,
     *                      "email": false,
     *                      "email_to": false,
     *                      "email_cc": false,
     *                      "email_bcc": false,
     *                      "createdBy":
     *                      {
     *                         "id": 2575,
     *                         "username": "admin",
     *                         "email": "admin@admin.sk"
     *                      }
     *                   }
     *                ]
     *             },
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
     *  description="Remove the attachment from the Task",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="slug",
     *       "dataType"="string",
     *       "description"="The slug of uploaded attachment"
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
     *      204 ="The attachment was successfully removed",
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param string $slug
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @internal param int $userId
     */
    public function removeAttachmentFromTaskAction(int $taskId, string $slug)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $file = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug
        ]);

        if (!$file instanceof File) {
            return $this->createApiResponse([
                'message' => 'Attachment with requested Slug does not exist!',
            ], StatusCodesHelper::BAD_REQUEST_CODE);
        }

        if ($this->canAddAttachmentToTask($task, $slug)) {
            return $this->createApiResponse([
                'message' => 'The requested attachment is not the attachment of the requested Task!',
            ], StatusCodesHelper::BAD_REQUEST_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_ATTACHMENT_FROM_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $taskHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAttachment')->findOneBy([
            'task' => $task,
            'slug' => $slug
        ]);

        $this->getDoctrine()->getManager()->remove($taskHasAttachment);
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
        return $this->json($taskArray, StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param Task $task
     * @param string $slug
     * @return bool
     * @throws \LogicException
     */
    private function canAddAttachmentToTask(Task $task, string $slug): bool
    {
        $taskHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAttachment')->findOneBy([
            'task' => $task,
            'slug' => $slug
        ]);

        return (!$taskHasAttachment instanceof TaskHasAttachment);
    }
}