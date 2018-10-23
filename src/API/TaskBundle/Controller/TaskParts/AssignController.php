<?php

namespace API\TaskBundle\Controller\TaskParts;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Security\ProjectAclOptions;
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
     *     {
     *       "data":
     *       [
     *           {
     *               "id": 1014,
     *               "username": "admin",
     *               "email": admin@admin.sk
     *               "name": null,
     *               "surname": null
     *           },
     *          {
     *               "id": 1015,
     *               "username": "admin4",
     *               "email": admin@admin4.sk
     *               "name": null,
     *               "surname": null
     *           }
     *       ]
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of all Assigners available for a requested project.",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *      404 ="Not found Entity"
     *  },
     * )
     *
     * @param int $projectId
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function listOfAllAvailableAssignersAction(int $projectId): Response
    {
        $locationURL =  $locationURL = $this->generateUrl('project_list_of_available_assign_users', ['projectId' => $projectId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

        if (!$project instanceof Project) {
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            return $response;
        }

        // Assigner has to have RESOLVE_TASK ACL in user_has_project
        $allAssigners = $this->get('api_user.service')->getListOfAvailableProjectAssigners($project, ProjectAclOptions::RESOLVE_TASK);

        $dataArray = [
            'data' => $allAssigners
        ];

        $response = $response->setContent(json_encode($dataArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
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
     *       "description"="The id of the user"
     *     },
     *     {
     *       "name"="statusId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of the status"
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
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
            } else {
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
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

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
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
     *  description="Delete taskHasAssignedUser Entity. Returns Task Entity.",
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
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

            // Check if user can update selected task
            if ($this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
                $canEdit = true;
            } else {
                $canEdit = false;
            }

            // Check if logged user Is ADMIN
            $isAdmin = $this->get('task_voter')->isAdmin();

            $taskArray = $this->get('task_service')->getFullTaskEntity($task, $canEdit, $this->getUser(), $isAdmin);
            return $this->json($taskArray, $statusCode);
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