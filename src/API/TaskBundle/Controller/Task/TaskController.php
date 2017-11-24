<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Entity\TaskData;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Security\StatusFunctionOptions;
use API\TaskBundle\Security\StatusOptions;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Security\VoteOptions;
use API\TaskBundle\Services\FilterAttributeOptions;
use API\TaskBundle\Services\VariableHelper;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskController
 *
 * @package API\TaskBundle\Controller\Task
 */
class TaskController extends ApiBaseController
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *         {
     *            "id": 62020,
     *            "title": "Task 3 - admin is creator, admin is requested",
     *            "description": "Description of Task 3",
     *            "deadline": null,
     *            "startedAt": null,
     *            "closedAt": null,
     *            "important": false,
     *            "work": null,
     *            "work_time": null,
     *            "createdAt": 1506434914,
     *            "updatedAt": 1506434914,
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
     *               {
     *                  "id": 209,
     *                  "username": "admin",
     *                  "email": "admin@admin.sk",
     *                  "name": "Admin",
     *                  "surname": "Adminovic"
     *                }
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
     *             "taskHasAssignedUsers":
     *             [
     *                 {
     *                      "id": 23,
     *                      "status_date": null,
     *                      "time_spent": null,
     *                      "createdAt": 1508768644,
     *                      "updatedAt": 1508768644,
     *                      "status":
     *                      {
     *                          "id": 7,
     *                          "title": "Completed",
     *                          "color": "#FF4500"
     *                      },
     *                      "user":
     *                      {
     *                          "id": 212,
     *                          "username": "customer",
     *                          "email": "customer@customer.sk",
     *                          "name": null,
     *                          "surname": null
     *                      }
     *                  }
     *            ],
     *            "taskHasAttachments": [],
     *            "comments": [],
     *            "invoiceableItems":
     *            [
     *              {
     *                  "id": 4,
     *                  "title": "Keyboard",
     *                  "amount": 2.00,
     *                  "unit_price": 50.00,
     *                  "unit":
     *                  {
     *                      "id": 10,
     *                      "title": "Kus",
     *                      "shortcut": "Ks"
     *                  }
     *               },
     *            ],
     *            "canEdit": true
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/task?page=1&project=145&creator=21",
     *           "first": "/api/v1/task-bundle/task?page=1&project=145&creator=21",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/task?page=2&project=145&creator=21",
     *           "last": "/api/v1/task-bundle/task?page=3&project=145&creator=21"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of full Task Entities which includes extended Task Data",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Limit for Pagination: 999 - returns all entities, null - returns 10 entities"
     *     },
     *     {
     *       "name"="order",
     *       "description"="Array of key=>value values, where KEY is column to sort by, VALUE is ASC or DESC order chart"
     *     },
     *     {
     *       "name"="search",
     *       "description"="Search string - system is searching in ID and TITLE and Requester, Company, Assignee, Created, Deadline, Status"
     *     },
     *     {
     *       "name"="status",
     *       "description"="A list of coma separated ID's of statuses f.i. 1,2,3,4"
     *     },
     *     {
     *       "name"="project",
     *       "description"="A list of coma separated ID's of Project f.i. 1,2,3.
     *        Another options:
     *          NOT - just tasks without projects are returned,
     *          CURRENT-USER - just tasks from actually logged user's projects are returned."
     *     },
     *     {
     *       "name"="creator",
     *       "description"="A list of coma separated ID's of Creator f.i. 1,2,3
     *        Another option:
     *          CURRENT-USER - just tasks created by actually logged user are returned."
     *     },
     *     {
     *       "name"="requester",
     *       "description"="A list of coma separated ID's of Requesters f.i. 1,2,3
     *        Another option:
     *          CURRENT-USER - just tasks requested by actually logged user are returned."
     *     },
     *     {
     *       "name"="company",
     *       "description"="A list of coma separated ID's of Companies f.i. 1,2,3
     *        Another options:
     *          CURRENT-USER - just tasks created by users with the same company like logged user are returned."
     *     },
     *     {
     *       "name"="assigned",
     *       "description"="A list of coma separated ID's of Users f.i. 1,2,3
     *        Another option:
     *          NOT - just tasks which aren't assigned to nobody are returned,
     *          CURRENT-USER - just tasks assigned to actually logged user are returned."
     *     },
     *     {
     *       "name"="tag",
     *       "description"="A list of coma separated ID's of Tags f.i. 1,2,3"
     *     },
     *     {
     *       "name"="follower",
     *       "description"="A list of coma separated ID's of Task Followers f.i. 1,2,3
     *        Another option:
     *          CURRENT-USER - just tasks followed by actually logged user are returned."
     *     },
     *     {
     *       "name"="createdTime",
     *       "description"="A coma separated dates in TIMESTAMP format
     *       FROM=1510531232,TO=1510531232
     *       Another option:
     *       TO=NOW - just tasks started to NOW datetime are returned."
     *     },
     *     {
     *       "name"="startedTime",
     *       "description"="A coma separated dates in TIMESTAMP format
     *       FROM=1510531232,TO=1510531232
     *       Another option:
     *       TO=NOW - just tasks started to NOW datetime are returned."
     *     },
     *     {
     *       "name"="deadlineTime",
     *       "description"="A coma separated dates in TIMESTAMP format
     *       FROM=1510531232,TO=1510531232
     *       Another option:
     *       TO=NOW - just tasks started to NOW datetime are returned."
     *     },
     *     {
     *       "name"="closedTime",
     *       "description"="A coma separated dates in TIMESTAMP format
     *       FROM=1510531232,TO=1510531232
     *       Another option:
     *       TO=NOW - just tasks started to NOW datetime are returned."
     *     },
     *     {
     *       "name"="archived",
     *       "description"="If TRUE, just tasks from archived projects are returned"
     *     },
     *     {
     *       "name"="important",
     *       "description"="If TRUE, just IMPORTANT tasks are returned"
     *     },
     *     {
     *       "name"="addedParameters",
     *       "description"="& separated data in form: taskAttributeId=value1,value2&taskAttributeId=value"
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
     *      401 ="Unauthorized request"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request)
    {
        $filterData = $this->getFilterData($request);

        $pageNum = $request->get('page');
        $pageNum = (int)$pageNum;
        $page = ($pageNum === 0) ? 1 : $pageNum;

        $limitNum = $request->get('limit');
        $limit = (int)$limitNum ?: 10;

        if (999 === $limit) {
            $page = 1;
        }

        $orderString = $request->get('order');
        $order = $this->processOrderData($orderString);

        if (null === $orderString) {
            $orderString = 'DESC';
        }

        $options = [
            'loggedUser' => $this->getUser(),
            'isAdmin' => $this->get('task_voter')->isAdmin(),
            'inFilter' => $filterData['inFilter'],
            'equalFilter' => $filterData['equalFilter'],
            'isNullFilter' => $filterData['isNullFilter'],
            'dateFilter' => $filterData['dateFilter'],
            'searchFilter' => $filterData['searchFilter'],
            'notAndCurrentFilter' => $filterData['notAndCurrentFilter'],
            'inFilterAddedParams' => $filterData['inFilterAddedParams'],
            'equalFilterAddedParams' => $filterData['equalFilterAddedParams'],
            'dateFilterAddedParams' => $filterData['dateFilterAddedParams'],
            'filtersForUrl' => array_merge($filterData['filterForUrl'], ['order' => '&order=' . $orderString]),
            'order' => $order,
            'limit' => $limit
        ];

        $tasksArray = $this->get('task_service')->getTasksResponse($page, $options);

        // Every Task need an additional canEdit Value
        $tasksModified = $this->addCanEditParamToEveryTask($tasksArray);

        return $this->json($tasksModified, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *         {
     *            "id": 62020,
     *            "title": "Task 3 - admin is creator, admin is requested",
     *            "description": "Description of Task 3",
     *            "deadline": null,
     *            "startedAt": null,
     *            "closedAt": null,
     *            "important": false,
     *            "work": null,
     *            "work_time": null,
     *            "createdAt": 1506434914,
     *            "updatedAt": 1506434914,
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
     *               {
     *                  "id": 209,
     *                  "username": "admin",
     *                  "email": "admin@admin.sk",
     *                  "name": "Admin",
     *                  "surname": "Adminovic"
     *                }
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
     *             "taskHasAssignedUsers":
     *             [
     *                 {
     *                      "id": 23,
     *                      "status_date": null,
     *                      "time_spent": null,
     *                      "createdAt": 1508768644,
     *                      "updatedAt": 1508768644,
     *                      "status":
     *                      {
     *                          "id": 7,
     *                          "title": "Completed",
     *                          "color": "#FF4500"
     *                      },
     *                      "user":
     *                      {
     *                          "id": 212,
     *                          "username": "customer",
     *                          "email": "customer@customer.sk",
     *                          "name": null,
     *                          "surname": null
     *                      }
     *                  }
     *            ],
     *            "taskHasAttachments": [],
     *            "comments": [],
     *            "invoiceableItems":
     *            [
     *              {
     *                  "id": 4,
     *                  "title": "Keyboard",
     *                  "amount": 2.00,
     *                  "unit_price": 50.00,
     *                  "unit":
     *                  {
     *                      "id": 10,
     *                      "title": "Kus",
     *                      "shortcut": "Ks"
     *                  }
     *               },
     *            ],
     *            "canEdit": true
     *           }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/task?page=1&project=145&creator=21",
     *           "first": "/api/v1/task-bundle/task?page=1&project=145&creator=21",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/task?page=2&project=145&creator=21",
     *           "last": "/api/v1/task-bundle/task?page=3&project=145&creator=21"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of full Task Entities selected by rules of requested Filter",
     *  requirements={
     *     {
     *       "name"="filterId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of filter"
     *     }
     *  },
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="order",
     *       "description"="Array of key=>value values, where KEY is column to sort by, VALUE is ASC or DESC order chart"
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Limit for Pagination: 999 - returns all entities, null - returns 10 entities"
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
     *      404 ="Not found entity"
     *  }
     * )
     *
     * @param Request $request
     * @param int $filterId
     *
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listSavedFilterAction(Request $request, int $filterId)
    {
        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($filterId);

        if (!$filter instanceof Filter) {
            return $this->createApiResponse([
                'message' => 'Filter with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if logged user has permission to see requested filter
        if (!$this->get('filter_voter')->isGranted(VoteOptions::SHOW_FILTER, $filter)) {
            return $this->accessDeniedResponse();
        }

        $pageNum = $request->get('page');
        $pageNum = (int)$pageNum;
        $page = ($pageNum === 0) ? 1 : $pageNum;

        $limitNum = $request->get('limit');
        $limit = (int)$limitNum ?: 10;

        if (999 === $limit) {
            $page = 1;
        }

        $orderString = $request->get('order');
        $order = $this->processOrderData($orderString);

        if (null === $orderString) {
            $orderString = 'DESC';
        }

        $filterDataArray = $filter->getFilter();
        $filterData = $this->getFilterDataFromSavedFilterArray($filterDataArray);
        $options = [
            'loggedUser' => $this->getUser(),
            'isAdmin' => $this->get('task_voter')->isAdmin(),
            'inFilter' => $filterData['inFilter'],
            'equalFilter' => $filterData['equalFilter'],
            'isNullFilter' => $filterData['isNullFilter'],
            'dateFilter' => $filterData['dateFilter'],
            'searchFilter' => $filterData['searchFilter'],
            'notAndCurrentFilter' => $filterData['notAndCurrentFilter'],
            'inFilterAddedParams' => $filterData['inFilterAddedParams'],
            'equalFilterAddedParams' => $filterData['equalFilterAddedParams'],
            'dateFilterAddedParams' => $filterData['dateFilterAddedParams'],
            'filtersForUrl' => array_merge($filterData['filterForUrl'], ['order' => '&order=' . $orderString]),
            'order' => $order,
            'limit' => $limit
        ];

        $tasksArray = $this->get('task_service')->getTasksResponse($page, $options);

        // Every Task need an additional canEdit Value
        $tasksModified = $this->addCanEditParamToEveryTask($tasksArray);

        return $this->json($tasksModified, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *        },
     *       "_links":
     *       {
     *          "quick update: task": "/api/v1/task-bundle/tasks/quick-update/23996",
     *          "patch: task": "/api/v1/task-bundle/tasks/23996",
     *          "delete": "/api/v1/task-bundle/tasks/23996"
     *       }
     *    }
     *
     * @ApiDoc(
     *  description="Returns full Task Entity including extended Task Data",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output="API\TaskBundle\Entity\Task",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     *
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            return $this->accessDeniedResponse();
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
        return $this->json($taskArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *        },
     *       "_links":
     *       {
     *          "quick update: task": "/api/v1/task-bundle/tasks/quick-update/23996",
     *          "patch: task": "/api/v1/task-bundle/tasks/23996",
     *          "delete": "/api/v1/task-bundle/tasks/23996"
     *       }
     *    }
     *
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Task Entity with extra Task Data.
     *  This can be added by the json array of attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.",
     *  parameters={
     *      {"name"="title", "dataType"="string", "required"=true,  "description"="tile of the Task"},
     *      {"name"="description", "dataType"="string", "required"=false,  "description"="description of the Task"},
     *      {"name"="requester", "dataType"="int", "required"=true,  "description"="id of the User"},
     *      {"name"="project", "dataType"="int", "required"=false, "description"="id of the Project"},
     *      {"name"="company", "dataType"="int", "required"=false,  "description"="id of the Company"},
     *      {"name"="startedAt", "dataType"="datetime", "required"=false,  "description"="the date of planned start"},
     *      {"name"="deadline", "dataType"="datetime", "required"=false,  "description"="the date of deadline"},
     *      {"name"="important", "dataType"="boolean", "required"=false,  "description"="set TRUE if the Task should be checked as IMPORTANT"},
     *      {"name"="work", "dataType"="string", "required"=false,  "description"="work"},
     *      {"name"="workTime", "dataType"="string", "required"=false,  "description"="work time"},
     *      {"name"="assigned", "dataType"="array", "required"=false,  "description"="array of the Users assigned to the task: [userId => 12, statusId => 5]. Format: $json array - http://php.net/manual/en/function.json-decode.php"},
     *      {"name"="tag", "dataType"="array", "required"=false,  "description"="array of the Tag titles: [tag1, tag2]"},
     *      {"name"="task_data", "dataType"="array", "required"=false,  "description"="array of the additional task attributes: [task_attribute_id => value, task_attribute_id2 => values]. Format: $json array - http://php.net/manual/en/function.json-decode.php"},
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAction(Request $request)
    {
        // Check if logged user has ACL to create task
        $aclOptions = [
            'acl' => UserRoleAclOptions::CREATE_TASKS,
            'user' => $this->getUser(),
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $task = new Task();
        $task->setCreatedAt(new \DateTime());
        $task->setCreatedBy($this->getUser());

        $requestData = $request->request->all();

        $requestDetailData = false;
        if (isset($requestData['task_data'])) {
            $requestDetailData = json_decode($requestData['task_data'], true);
            unset($requestData['task_data']);
        }

        // REQUIRED PARAMETERS
        if (isset($requestData['title'])) {
            if (\strlen($requestData['title']) > 0) {
                $task->setTitle($requestData['title']);
            }
        } else {
            return $this->createApiResponse([
                'message' => 'The Title of the task is required!',
            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
        }

        if (isset($requestData['requester'])) {
            $requestedUserId = (int)$requestData['requester'];
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);
            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $task->setRequestedBy($requestedUser);
        } else {
            $task->setRequestedBy($this->getUser());
        }

        if (isset($requestData['important'])) {
            $important = $requestData['important'];
            $task->setImportant($important);
        } else {
            $task->setImportant(false);
        }

        if (isset($requestData['project'])) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find((int)$requestData['project']);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            // Check if user can create task in selected project
            if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK_IN_PROJECT, $project)) {
                return $this->createApiResponse([
                    'message' => 'Permission denied! Can not create task in selected project!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            }
            $task->setProject($project);
        } else {
            // If Task doesn't have project - the system returns error
            return $this->createApiResponse([
                'message' => 'Project is Required!',
            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
        }

        // OPTIONAL PARAMETERS
        if (isset($requestData['description'])) {
            if (\is_string($requestData['description'])) {
                $desc = $requestData['description'];
                $task->setDescription($desc);
            } else {
                return $this->createApiResponse([
                    'message' => 'The Description of the task has to be a TEXT!',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }

        if (isset($requestData['work'])) {
            if (\is_string($requestData['work'])) {
                $work = $requestData['work'];
                $task->setWork($work);
            } else {
                return $this->createApiResponse([
                    'message' => 'The Work parameter has to be a TEXT!',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }

        if (isset($requestData['workTime'])) {
            if (\is_string($requestData['workTime'])) {
                $workTime = $requestData['workTime'];
                $task->setWorkTime($workTime);
            } else {
                return $this->createApiResponse([
                    'message' => 'The Work Time parameter has to be a STRING!',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }

        if (isset($requestData['company'])) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find((int)$requestData['company']);
            if (!$company instanceof Company) {
                return $this->createApiResponse([
                    'message' => 'Company with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $task->setCompany($company);
        }

        if (isset($requestData['startedAt'])) {
            $intDateData = (int)$requestData['startedAt'];
            if (null === $requestData['startedAt'] || 'null' === $requestData['startedAt']) {
                $task->setStartedAt(null);
            } else {
                try {
                    $startedAtDateTimeObject = new \DateTime("@$intDateData");
                    $task->setStartedAt($startedAtDateTimeObject);
                    $changedParams[] = 'started at';
                } catch (\Exception $e) {
                    return $this->createApiResponse([
                        'message' => 'startedAt parameter is not in a valid format! Expected format: Timestamp',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
        }

        if (isset($requestData['deadline'])) {
            $intDateData = (int)$requestData['deadline'];
            if (null === $requestData['deadline'] || 'null' === $requestData['deadline']) {
                $task->setDeadline(null);
            } else {
                try {
                    $deadlineDateTimeObject = new \Datetime("@$intDateData");
                    $task->setDeadline($deadlineDateTimeObject);
                    $changedParams[] = 'deadline';
                } catch (\Exception $e) {
                    return $this->createApiResponse([
                        'message' => 'deadline parameter is not in a valid format! Expected format: Timestamp',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
        }
        $this->getDoctrine()->getManager()->persist($task);

//        $requestData['assigned'] = '[{"userId": 209, "statusId": 8}]';
        // OPTIONAL PARAMETERS - ANOTHER NEW ENTITY IS REQUIRED
        if (isset($requestData['assigned'])) {
            $assignedUsersArray = json_decode($requestData['assigned'], true);
            $userIsAssignedToTask = new TaskHasAssignedUser();

            // Add new requested users to the task
            foreach ($assignedUsersArray as $key => $value) {
                $assignedUserId = $value['userId'];

                $assignedUserStatusId = null;
                if (isset($value['statusId'])) {
                    $assignedUserStatusId = $value['statusId'];
                }

                // USER
                $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($assignedUserId);
                if (!$user instanceof User) {
                    return $this->createApiResponse([
                        'message' => 'User with requested Id does not exist!',
                    ], StatusCodesHelper::NOT_FOUND_CODE);
                }

                // Check if user can be assigned to task
                $options = [
                    'task' => $task,
                    'user' => $user
                ];

                if (!$this->get('task_voter')->isGranted(VoteOptions::ASSIGN_USER_TO_TASK, $options)) {
                    return $this->createApiResponse([
                        'message' => 'User with id: ' . $assignedUserId . 'has not permission to be assigned to requested task!',
                    ], StatusCodesHelper::NOT_FOUND_CODE);
                }

                // STATUS
                if (null !== $assignedUserStatusId) {
                    $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($assignedUserStatusId);

                    // ONLY TASK WITH PROJECT, REQUESTER AND COMPANY CAN BE CLOSED
                    if ($status->getDefault() === TRUE && $status->getFunction() === StatusFunctionOptions::CLOSED_TASK) {

                        $tasksProject = $task->getProject();
                        if (!$tasksProject instanceof Project) {
                            return $this->createApiResponse([
                                'message' => 'Task without PROJECT can not be closed!',
                            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        }

                        $tasksRequester = $task->getRequestedBy();
                        if (!$tasksRequester instanceof User) {
                            return $this->createApiResponse([
                                'message' => 'Task without REQUESTER can not be closed!',
                            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        }

                        $tasksCompany = $task->getCompany();
                        if (!$tasksCompany instanceof Company) {
                            return $this->createApiResponse([
                                'message' => 'Task without COMPANY can not be closed!',
                            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        }

                        $task->setClosedAt(new \DateTime());
                    }
                } else {
                    $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->findOneBy([
                        'function' => StatusFunctionOptions::NEW_TASK,
                        'default' => true
                    ]);
                }
                if (!$status instanceof Status) {
                    return $this->createApiResponse([
                        'message' => 'New Status or your requested Status does not exist!',
                    ], StatusCodesHelper::NOT_FOUND_CODE);
                }

                if (null === $task->getStartedAt() && $status->getDefault() === true && $status->getFunction() !== StatusFunctionOptions::NEW_TASK) {
                    $task->setStartedAt(new \DateTime());
                }

                $userIsAssignedToTask->setTask($task);
                $userIsAssignedToTask->setStatus($status);
                $userIsAssignedToTask->setUser($user);
                $userIsAssignedToTask->setActual(true);
                $this->getDoctrine()->getManager()->persist($userIsAssignedToTask);
                $this->getDoctrine()->getManager()->flush();

                //Add assigned User to task
                $task->addTaskHasAssignedUser($userIsAssignedToTask);
                $this->getDoctrine()->getManager()->persist($task);
            }
        }

        // Add tags to task
        //$requestData = '{"tag":["tag1"."tag2"]}';
        if (isset($requestData['tag'])) {
            $tagsArray = $requestData['tag'];
            if (\is_string($tagsArray)) {
                $tagsArray = json_decode($tagsArray, true);
            }

            foreach ($tagsArray as $data) {
                if (isset($data['title'])) {
                    $tagTitle = $data['title'];
                    $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
                        'title' => $data['title']
                    ]);
                } else {
                    $tagTitle = $data;
                    $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
                        'title' => $data
                    ]);
                }

                if ($tag instanceof Tag) {
                    //Check if user can add tag to requested Task
                    $options = [
                        'task' => $task,
                        'tag' => $tag
                    ];

                    if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TAG_TO_TASK, $options)) {
                        return $this->createApiResponse([
                            'message' => 'Tag with title: ' . $tagTitle . 'can not be added to requested task!',
                        ], StatusCodesHelper::NOT_FOUND_CODE);
                    }

                    //Check if tag is already added to task
                    $taskHasTags = $task->getTags();
                    if (in_array($tag, $taskHasTags->toArray(), true)) {
                        continue;
                    }
                } else {
                    //Create a new tag
                    $tag = new Tag();
                    $tag->setTitle($tagTitle);
                    $tag->setPublic(false);
                    $tag->setColor('FFFF66');
                    $tag->setCreatedBy($this->getUser());

                    $this->getDoctrine()->getManager()->persist($tag);
                    $this->getDoctrine()->getManager()->flush();
                }
                //Add tag to task
                $task->addTag($tag);
                $this->getDoctrine()->getManager()->persist($task);
            }
        }

        // Fill TaskData Entity if some of its parameters were sent
        // Expected json objects: {"10": "value 1", "12": "value 2"}
        if ($requestDetailData) {
            /** @var array $requestDetailData */
            foreach ($requestDetailData as $key => $value) {
                $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($key);
                if ($taskAttribute instanceof TaskAttribute) {
                    $cd = $this->getDoctrine()->getRepository('APITaskBundle:TaskData')->findOneBy(['taskAttribute' => $taskAttribute,
                        'task' => $task,]);

                    if (!$cd instanceof TaskData) {
                        $cd = new TaskData();
                        $cd->setTask($task);
                        $cd->setTaskAttribute($taskAttribute);
                    }

                    $cdErrors = $this->get('entity_processor')->processEntity($cd, ['value' => $value]);
                    if (false === $cdErrors) {
                        //Check the data format
                        $taskAttributeDataFormat = $taskAttribute->getType();
                        switch ($taskAttributeDataFormat) {
                            case VariableHelper::INTEGER_NUMBER:
                                if (!\is_int($value)) {
                                    return $this->createApiResponse([
                                        'message' => 'The value format of task_data with key: ' . $key . ' is invalid. Expected format: INTEGER',
                                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                                }
                                break;
                            case VariableHelper::DECIMAL_NUMBER:
                                if (!\is_float($value)) {
                                    return $this->createApiResponse([
                                        'message' => 'The value format of task_data with key: ' . $key . ' is invalid. Expected format: DECIMAL NUMBER',
                                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                                }
                                break;
                            case VariableHelper::SIMPLE_SELECT:
                                $selectionOptions = $taskAttribute->getOptions();
                                if (!\in_array($value, $selectionOptions, true)) {
                                    return $this->createApiResponse([
                                        'message' => 'The value of task_data with key: ' . $key . ' is invalid. Expected is value from ATTRIBUTE OPTIONS',
                                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                                }
                                break;
                            case VariableHelper::MULTI_SELECT:
                                $selectionOptions = $taskAttribute->getOptions();
                                $sentOptions = json_decode($value);
                                foreach ($sentOptions as $option) {
                                    if (!\in_array($option, $selectionOptions, true)) {
                                        return $this->createApiResponse([
                                            'message' => 'The value of task_data with key: ' . $key . ' is invalid. Expected is value from ATTRIBUTE OPTIONS',
                                        ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                                    }
                                }
                                break;
                            case VariableHelper::DATE:
                                $intDateData = (int)$value;
                                try {
                                    $timeObject = new \DateTime("@$intDateData");
                                    $cd->setValue($timeObject);
                                } catch (\Exception $e) {
                                    return $this->createApiResponse([
                                        'message' => 'The value of task_data with key: ' . $key . ' is invalid. Expected is TIMESTAMP',
                                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                                }

                                break;
                            default:
                                break;
                        }

                        if (null === $value || 'null' === $value) {
                            $cd->setValue(null);
                        }
                        $task->addTaskDatum($cd);
                        $this->getDoctrine()->getManager()->persist($task);
                        $this->getDoctrine()->getManager()->persist($cd);
                    } else {
                        return $this->createApiResponse([
                            'message' => 'The value of task_data with key: ' . $key . ' is invalid',
                        ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    }
                } else {
                    return $this->createApiResponse([
                        'message' => 'The key: ' . $key . ' of Task Attribute is not valid (Task Attribute with this ID doesn\'t exist)',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
        }
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
        return $this->json($taskArray, StatusCodesHelper::CREATED_CODE);
    }

    /**
     *   ### Response ###
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
     *  description="Update the Task Entity with extra Task Data.
     *  These could be updated by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.
     *  Project and Requested User could be updated by Id-s in URL.",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  parameters={
     *      {"name"="project", "dataType"="int", "required"=false, "description"="id of the Project"},
     *      {"name"="requester", "dataType"="int", "required"=false,  "description"="id of the User"},
     *      {"name"="company", "dataType"="int", "required"=false,  "description"="id of the Company"},
     *      {"name"="assigned", "dataType"="array", "required"=false,  "description"="array of the Users assigned to the task: [userId => 12, statusId => 5]"},
     *      {"name"="startedAt", "dataType"="datetime", "required"=false,  "description"="the date of planned start"},
     *      {"name"="deadline", "dataType"="datetime", "required"=false,  "description"="the date of deadline"},
     *      {"name"="closedAt", "dataType"="datetime", "required"=false,  "description"="the date of closure"},
     *      {"name"="tag", "dataType"="array", "required"=false,  "description"="array of the Tag titles: [tag1, tag2]"},
     *      {"name"="title", "dataType"="string", "required"=false,  "description"="tile of the Task"},
     *      {"name"="description", "dataType"="string", "required"=false,  "description"="description of the Task"},
     *      {"name"="important", "dataType"="boolean", "required"=false,  "description"="set TRUE if the Task should be checked as IMPORTANT"},
     *      {"name"="work", "dataType"="string", "required"=false,  "description"="work"},
     *      {"name"="workTime", "dataType"="string", "required"=false,  "description"="work time"}
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request)
    {
        return $this->quickUpdateTaskAction($id, $request);
    }

    /**
     * @ApiDoc(
     *  description="Delete the Task entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
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
     *      204 ="The Entity was successfully deleted",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function deleteAction(int $id)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::DELETE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
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
     *  description="Partially update the Task Entity with extra Task Data.
     *  These could be updated by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.
     *  Project and Requested User could be updated by Id-s in URL.",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  parameters={
     *      {"name"="project", "dataType"="int", "required"=false, "description"="id of the Project"},
     *      {"name"="requester", "dataType"="int", "required"=false,  "description"="id of the User"},
     *      {"name"="company", "dataType"="int", "required"=false,  "description"="id of the Company"},
     *      {"name"="assigned", "dataType"="array", "required"=false,  "description"="array of the Users assigned to the task: [userId => 12, statusId => 5]"},
     *      {"name"="startedAt", "dataType"="datetime", "required"=false,  "description"="the date of planned start"},
     *      {"name"="deadline", "dataType"="datetime", "required"=false,  "description"="the date of deadline"},
     *      {"name"="closedAt", "dataType"="datetime", "required"=false,  "description"="the date of closure"},
     *      {"name"="tag", "dataType"="array", "required"=false,  "description"="array of the Tag titles: [tag1, tag2]"},
     *      {"name"="title", "dataType"="string", "required"=false,  "description"="tile of the Task"},
     *      {"name"="description", "dataType"="string", "required"=false,  "description"="description of the Task"},
     *      {"name"="important", "dataType"="boolean", "required"=false,  "description"="set TRUE if the Task should be checked as IMPORTANT"},
     *      {"name"="work", "dataType"="string", "required"=false,  "description"="work"},
     *      {"name"="workTime", "dataType"="string", "required"=false,  "description"="work time"},
     *      {"name"="task_data", "dataType"="array", "required"=false,  "description"="array of the additional task attributes: [task_attribute_id => value, task_attribute_id2 => values]. Format: $json array - http://php.net/manual/en/function.json-decode.php"},
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $taskId
     * @param Request $request
     *
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function quickUpdateTaskAction(int $taskId, Request $request)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $canEdit = true;

//      $requestData = '{"assigned":[{"userId": 212, "statusId": 8}],"company":202}';
        $requestDataFromContent = json_decode($request->getContent(), true);
        if (null === $requestDataFromContent) {
            $requestData = $request->request->all();
        } else {
            $requestData = $requestDataFromContent;
        }
        $changedParams = [];

        if (isset($requestData['title'])) {
            $title = $requestData['title'];
            if (\strlen($title) > 0) {
                $task->setTitle($title);
                $changedParams[] = 'title';
            } else {
                return $this->createApiResponse([
                    'message' => 'Title of the task can not be empty!',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }

        if (isset($requestData['description'])) {
            $desc = $requestData['description'];
            $task->setDescription($desc);
            $changedParams[] = 'description';
        }

        if (isset($requestData['work'])) {
            $work = $requestData['work'];
            $task->setWork($work);
            $changedParams[] = 'work';
        }

        if (isset($requestData['workTime'])) {
            $workTime = $requestData['workTime'];
            $task->setWorkTime($workTime);
            $changedParams[] = 'workTime';
        }

        if (isset($requestData['important'])) {
            $important = $requestData['important'];
            $task->setImportant($important);
            $changedParams[] = 'important';
        }

        if (isset($requestData['project'])) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($requestData['project']);
            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            // Check if user can create task in selected project
            if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK_IN_PROJECT, $project)) {
                return $this->createApiResponse([
                    'message' => 'Permission denied! Can not create task in selected project!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            }
            $task->setProject($project);
            $changedParams[] = 'project';

            // Remove users assigned to task which haven't RESOLVE_TASK permission in selected project
            $taskHasAssignedUsers = $task->getTaskHasAssignedUsers();
            if (count($taskHasAssignedUsers) > 0) {
                /** @var TaskHasAssignedUser $entity */
                foreach ($taskHasAssignedUsers as $entity) {
                    if (!$this->checkIfUserHasResolveTaskAclPermission($entity, $project)) {
                        $this->getDoctrine()->getManager()->remove($entity);
                    }
                }
                $this->getDoctrine()->getManager()->flush();
            }
        }

        if (isset($requestData['requester'])) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestData['requester']);
            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $task->setRequestedBy($requestedUser);
            $changedParams[] = 'requester';
        }

        if (isset($requestData['company'])) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($requestData['company']);
            if (!$company instanceof Company) {
                return $this->createApiResponse([
                    'message' => 'Company with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $task->setCompany($company);
            $changedParams[] = 'company';
        }

        if (isset($requestData['assigned'])) {
            $this->getDoctrine()->getConnection()->beginTransaction();
            try {
                $assignedUsersArray = $requestData['assigned'];
                if (\is_string($assignedUsersArray)) {
                    $assignedUsersArray = json_decode($assignedUsersArray, true);
                }

                // Add new requested users to the task
                foreach ($assignedUsersArray as $value) {
                    $assignedUserId = $value['userId'];

                    $assignedUserStatusId = null;
                    if (isset($value['statusId'])) {
                        $assignedUserStatusId = $value['statusId'];
                    }

                    // USER
                    $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($assignedUserId);
                    if (!$user instanceof User) {
                        return $this->createApiResponse([
                            'message' => 'User with requested Id does not exist!',
                        ], StatusCodesHelper::NOT_FOUND_CODE);
                    }

                    // Check if user can be assigned to task
                    $options = [
                        'task' => $task,
                        'user' => $user
                    ];

                    if (!$this->get('task_voter')->isGranted(VoteOptions::ASSIGN_USER_TO_TASK, $options)) {
                        return $this->createApiResponse([
                            'message' => 'User with id: ' . $assignedUserId . 'has not permission to be assigned to requested task!',
                        ], StatusCodesHelper::NOT_FOUND_CODE);
                    }

                    // Check if task already has another one assigned user - if yes, delete that user
                    // because system temporary support ONLY ONE assigned USER
                    $taskHasOtherAssignedUserArray = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAssignedUser')->findOtherUsersAssignedToTask($options);
                    if (\count($taskHasOtherAssignedUserArray) !== 0) {
                        foreach ($taskHasOtherAssignedUserArray as $tau) {
                            $this->getDoctrine()->getManager()->remove($tau);
                        }
                        $this->getDoctrine()->getManager()->flush();
                    }

                    // Set all other Users previous statuses to NOT ACTUAL
                    $taskHasAlreadyAssignedUser = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAssignedUser')->findAssignedUsersEntities($options);

                    // If we are only changing user's status, we don't reset StartedAt time
                    // If we are changing assigner, we hav to reset StartedAt task time
                    $keepStartedAtDatetime = false;
                    if (\count($taskHasAlreadyAssignedUser) !== 0) {
                        /** @var TaskHasAssignedUser $tau */
                        foreach ($taskHasAlreadyAssignedUser as $tau) {
                            $tau->setActual(false);
                            $this->getDoctrine()->getManager()->persist($tau);
                        }
                        $this->getDoctrine()->getManager()->flush();
                        $keepStartedAtDatetime = true;
                    }

                    if ($keepStartedAtDatetime) {
                        $task->setStartedAt(null);
                    }

                    // Create new task has assigned entity and set it as ACTUAL
                    $userIsAssignedToTask = new TaskHasAssignedUser();
                    $userIsAssignedToTask->setActual(true);

                    // STATUS
                    if (null !== $assignedUserStatusId) {
                        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($assignedUserStatusId);

                        // ONLY TASK WITH PROJECT, REQUESTER AND COMPANY CAN BE CLOSED
                        if ($status->getDefault() === TRUE && $status->getFunction() === StatusFunctionOptions::CLOSED_TASK) {

                            $tasksProject = $task->getProject();
                            if (!$tasksProject instanceof Project) {
                                return $this->createApiResponse([
                                    'message' => 'Task without PROJECT can not be closed!',
                                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                            }

                            $tasksRequester = $task->getRequestedBy();
                            if (!$tasksRequester instanceof User) {
                                return $this->createApiResponse([
                                    'message' => 'Task without REQUESTER can not be closed!',
                                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                            }

                            $tasksCompany = $task->getCompany();
                            if (!$tasksCompany instanceof Company) {
                                return $this->createApiResponse([
                                    'message' => 'Task without COMPANY can not be closed!',
                                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                            }

                            $closedAtDateTimeObject = new \Datetime();
                            $task->setClosedAt($closedAtDateTimeObject);

                            if (!$task->getStartedAt()) {
                                $task->setStartedAt($closedAtDateTimeObject);
                            }
                        } else {
                            $task->setClosedAt(null);
                        }
                    } else {
                        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->findOneBy([
                            'function' => StatusFunctionOptions::NEW_TASK,
                            'default' => true
                        ]);
                        $task->setStartedAt(null);
                        $task->setClosedAt(null);
                    }

                    if (!$status instanceof Status) {
                        return $this->createApiResponse([
                            'message' => 'New Status or your requested Status does not exist!',
                        ], StatusCodesHelper::NOT_FOUND_CODE);
                    }

                    if ((null === $task->getStartedAt() || false === $keepStartedAtDatetime) && $status->getDefault() === true && $status->getFunction() !== StatusFunctionOptions::NEW_TASK) {
                        $startedAtDateTimeObject = new \Datetime();
                        $task->setStartedAt($startedAtDateTimeObject);
                    }

                    $userIsAssignedToTask->setTask($task);
                    $userIsAssignedToTask->setStatus($status);
                    $userIsAssignedToTask->setUser($user);
                    $this->getDoctrine()->getManager()->persist($userIsAssignedToTask);
                }
                $this->getDoctrine()->getConnection()->commit();
                $this->getDoctrine()->getManager()->flush();
                $changedParams[] = 'assigned user/s';
            } catch (\Exception $e) {
                $this->getDoctrine()->getConnection()->rollBack();
                return $this->createApiResponse([
                    'message' => 'Assign problem: ' . $e->getMessage(),
                ], StatusCodesHelper::BAD_REQUEST_CODE);
            }
        }

        if (isset($requestData['startedAt'])) {
            $intDateData = (int)$requestData['startedAt'];
            if (null === $requestData['startedAt'] || 'null' === $requestData['startedAt']) {
                $task->setStartedAt(null);
                $changedParams[] = 'started at';
            } else {
                try {
                    $startedAtDateTimeObject = new \DateTime("@$intDateData");
                    $task->setStartedAt($startedAtDateTimeObject);
                    $changedParams[] = 'started at';
                } catch (\Exception $e) {
                    return $this->createApiResponse([
                        'message' => 'startedAt parameter is not in a valid format! Expected format: Timestamp',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
        }

        if (isset($requestData['deadline'])) {
            $intDateData = (int)$requestData['deadline'];
            if (null === $requestData['deadline'] || 'null' === $requestData['deadline']) {
                $task->setDeadline(null);
                $changedParams[] = 'deadline';
            } else {
                try {
                    $deadlineDateTimeObject = new \Datetime("@$intDateData");
                    $task->setDeadline($deadlineDateTimeObject);
                    $changedParams[] = 'deadline';
                } catch (\Exception $e) {
                    return $this->createApiResponse([
                        'message' => 'deadline parameter is not in a valid format! Expected format: Timestamp',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
        }

        if (isset($requestData['closedAt'])) {
            $intDateData = (int)$requestData['deadline'];
            if (null === $requestData['closedAt'] || 'null' === $requestData['closedAt']) {
                $task->setClosedAt(null);
                $changedParams[] = 'closed At';
            } else {
                try {
                    $closedAtDateTimeObject = new \Datetime("@$intDateData");
                    $task->setClosedAt($closedAtDateTimeObject);
                    $changedParams[] = 'closed At';
                } catch (\Exception $e) {
                    return $this->createApiResponse([
                        'message' => 'closedAt parameter is not in a valid format! Expected format: Timestamp',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
        }

        //$requestData = '{"tag":["tag1"."tag2"]}';
        if (isset($requestData['tag'])) {
            $this->getDoctrine()->getConnection()->beginTransaction();
            try {
                // Remove all task's tags
                $taskHasTags = $task->getTags();
                if (\count($taskHasTags) > 0) {
                    /** @var Tag $taskTag */
                    foreach ($taskHasTags as $taskTag) {
                        $task->removeTag($taskTag);
                        $taskTag->removeTask($task);
                        $this->getDoctrine()->getManager()->persist($task);
                        $this->getDoctrine()->getManager()->persist($taskTag);
                    }
                    $this->getDoctrine()->getManager()->flush();
                }

                // Add tags to task
                $tagsArray = $requestData['tag'];
                if (\is_string($tagsArray)) {
                    $tagsArray = json_decode($tagsArray, true);
                }

                foreach ($tagsArray as $data) {
                    if (isset($data['title'])) {
                        $tagTitle = $data['title'];
                        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
                            'title' => $data['title']
                        ]);
                    } else {
                        $tagTitle = $data;
                        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
                            'title' => $data
                        ]);
                    }

                    if ($tag instanceof Tag) {
                        //Check if user can add tag to requested Task
                        $options = [
                            'task' => $task,
                            'tag' => $tag
                        ];

                        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TAG_TO_TASK, $options)) {
                            return $this->createApiResponse([
                                'message' => 'Tag with title: ' . $tagTitle . 'can not be added to requested task!',
                            ], StatusCodesHelper::NOT_FOUND_CODE);
                        }

                        //Check if tag is already added to task
                        $taskHasTags = $task->getTags();
                        if (\in_array($tag, $taskHasTags->toArray(), true)) {
                            continue;
                        }
                    } else {
                        //Create a new tag
                        $tag = new Tag();
                        $tag->setTitle($tagTitle);
                        $tag->setPublic(false);
                        $tag->setColor('FFFF66');
                        $tag->setCreatedBy($this->getUser());

                        $this->getDoctrine()->getManager()->persist($tag);
                        $this->getDoctrine()->getManager()->flush();
                    }

                    //Add tag to task
                    $task->addTag($tag);
                    $this->getDoctrine()->getManager()->persist($task);
                }
                $this->getDoctrine()->getConnection()->commit();
                $changedParams[] = 'tag/s';
            } catch (\Exception $e) {
                $this->getDoctrine()->getConnection()->rollBack();
                return $this->createApiResponse([
                    'message' => 'Tag problem: ' . $e->getMessage(),
                ], StatusCodesHelper::BAD_REQUEST_CODE);
            }
        }
        $this->getDoctrine()->getManager()->persist($task);
        $this->getDoctrine()->getManager()->flush();

        // Fill TaskData Entity if some of its parameters were sent
        // Expected json objects: {"10": "value 1", "12": "value 2"}
        if (isset($requestData['task_data'])) {
//            $requestDetailData = json_decode($requestData['task_data'], true);
//            unset($requestData['task_data']);
//
//            /** @var array $requestDetailData */
//            foreach ($requestDetailData as $key => $value) {
//                $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($key);
//                if ($taskAttribute instanceof TaskAttribute) {
//                    $cd = $this->getDoctrine()->getRepository('APITaskBundle:TaskData')->findOneBy(['taskAttribute' => $taskAttribute,
//                        'task' => $task,]);
//
//                    if (!$cd instanceof TaskData) {
//                        $cd = new TaskData();
//                        $cd->setTask($task);
//                        $cd->setTaskAttribute($taskAttribute);
//                    }
//
//                    $cdErrors = $this->get('entity_processor')->processEntity($cd, ['value' => $value]);
//                    if (false === $cdErrors) {
//                        //Check the data format
//                        $taskAttributeDataFormat = $taskAttribute->getType();
//                        switch ($taskAttributeDataFormat) {
//                            case VariableHelper::INTEGER_NUMBER:
//                                if (!is_int($value)) {
//                                    return $this->createApiResponse([
//                                        'message' => 'The value format of task_data with key: ' . $key . ' is invalid. Expected format: INTEGER',
//                                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
//                                }
//                                break;
//                            case VariableHelper::DECIMAL_NUMBER:
//                                if (!is_float($value)) {
//                                    return $this->createApiResponse([
//                                        'message' => 'The value format of task_data with key: ' . $key . ' is invalid. Expected format: DECIMAL NUMBER',
//                                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
//                                }
//                                break;
//                            case VariableHelper::SIMPLE_SELECT:
//                                $selectionOptions = $taskAttribute->getOptions();
//                                if (!in_array($value, $selectionOptions, true)) {
//                                    return $this->createApiResponse([
//                                        'message' => 'The value of task_data with key: ' . $key . ' is invalid. Expected is value from ATTRIBUTE OPTIONS',
//                                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
//                                }
//                                break;
//                            case VariableHelper::MULTI_SELECT:
//                                $selectionOptions = $taskAttribute->getOptions();
//                                $sentOptions = json_decode($value);
//                                foreach ($sentOptions as $option) {
//                                    if (!in_array($option, $selectionOptions, true)) {
//                                        return $this->createApiResponse([
//                                            'message' => 'The value of task_data with key: ' . $key . ' is invalid. Expected is value from ATTRIBUTE OPTIONS',
//                                        ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
//                                    }
//                                }
//                                break;
//                            case VariableHelper::DATE:
//                                $intDateData = (int)$value;
//                                try {
//                                    $timeObject = new \DateTime("@$intDateData");
//                                    $cd->setValue($timeObject);
//                                } catch (\Exception $e) {
//                                    return $this->createApiResponse([
//                                        'message' => 'The value of task_data with key: ' . $key . ' is invalid. Expected is TIMESTAMP',
//                                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
//                                }
//
//                                break;
//                            default:
//                                break;
//                        }
//
//                        if (null === $value || 'null' === $value) {
//                            $cd->setValue(null);
//                        }
//                        $task->addTaskDatum($cd);
//                        $this->getDoctrine()->getManager()->persist($task);
//                        $this->getDoctrine()->getManager()->persist($cd);
//                    } else {
//                        return $this->createApiResponse([
//                            'message' => 'The value of task_data with key: ' . $key . ' is invalid',
//                        ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
//                    }
//                } else {
//                    return $this->createApiResponse([
//                        'message' => 'The key: ' . $key . ' of Task Attribute is not valid (Task Attribute with this ID doesn\'t exist)',
//                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
//                }
//            }
        }
        $this->getDoctrine()->getManager()->flush();


        /** @var User $loggedUser */
        $loggedUser = $this->getUser();

        // Sent Notification Emails about updating of Task to task REQUESTER, ASSIGNED USERS, FOLLOWERS
        if (\count($changedParams) > 0) {
//            $notificationEmailAddresses = $this->getEmailForUpdateTaskNotification($task, $loggedUser->getEmail());
            $notificationEmailAddresses = [];
            if (\count($notificationEmailAddresses) > 0) {
                $templateParams = $this->getTemplateParams($task->getId(), $task->getTitle(), $notificationEmailAddresses, $loggedUser, $changedParams);
                $sendingError = $this->get('email_service')->sendEmail($templateParams);
                if (true !== $sendingError) {
                    $data = [
                        'errors' => $sendingError,
                        'message' => 'Error with sending notifications!'
                    ];
                    return $this->createApiResponse($data, StatusCodesHelper::PROBLEM_WITH_EMAIL_SENDING);
                }
            }
        }

        // Check if logged user Is ADMIN
        $isAdmin = $this->get('task_voter')->isAdmin();

        $taskArray = $this->get('task_service')->getFullTaskEntity($task, $canEdit, $this->getUser(), $isAdmin);
        return $this->json($taskArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *         "status":
     *         [
     *            {
     *               "id": 178,
     *               "title": "new"
     *            },
     *            {
     *               "id": 179,
     *               "title": "In Progress"
     *            },
     *         ],
     *         "project":
     *         [
     *            {
     *               "id": 207,
     *               "title": "Project of user 2"
     *            },
     *            {
     *              "id": 208,
     *              "title": "Project of admin"
     *            },
     *         ],
     *         "requester":
     *         [
     *            {
     *               "id": 1014,
     *               "username": "admin",
     *               "name": null,
     *               "surname": null
     *            },
     *            {
     *               "id": 1015,
     *               "username": "manager",
     *               "name": null,
     *               "surname": null
     *            },
     *         ],
     *         "company":
     *         [
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions"
     *           },
     *           {
     *              "id": 318,
     *              "title": "LanSystems"
     *           }
     *         ],
     *         "tag":
     *         [
     *           {
     *              "id": 9,
     *              "title": "Free Time"
     *            },
     *           {
     *              "id": 10,
     *              "title": "Work"
     *            },
     *            {
     *               "id": 12,
     *               "title": "Another Admin Public Tag"
     *             }
     *          ],
     *          "assigner":
     *          [
     *            {
     *               "id": 1014,
     *               "username": "admin",
     *               "name": null,
     *               "surname": null
     *            }
     *          ],
     *          "unit":[],
     *          "taskAttributes":
     *          [
     *            {
     *               "title": "input task additional attribute",
     *               "type": "input",
     *               "options": null
     *             },
     *             {
     *                "title": "select task additional attribute",
     *                "type": "simple_select",
     *                "options":
     *                {
     *                   "select1": "select1",
     *                   "select2": "select2",
     *                   "select3": "select3"
     *                }
     *             },
     *             {
     *                "title": "integer number task additional attribute",
     *                "type": "integer_number",
     *                "options": null
     *             }
     *          ]
     *      }
     * @ApiDoc(
     *  description="Get all options for task: statuses, available projects, available requesters, available companies, available assigners, available tags",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed task"
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
     *  })
     *
     * @param int $taskId
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function getTaskOptionsAction(int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can view requested Task
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        // Return arrays of options
        $statusesArray = $this->get('status_service')->getListOfExistedStatuses();

        // Available projects are where logged user have CREATE_TASK ACL
        // If task is moved to project where assigned user has not permission to RESOLVE_TASK, this assigned user will be removed
        // Admin can use All existed projects
        $isAdmin = $this->get('task_voter')->isAdmin();
        $projectsArray = $this->get('project_service')->getListOfAvailableProjects($this->getUser(), $isAdmin, ProjectAclOptions::CREATE_TASK);

        // Every user can be requester
        $requesterArray = $this->get('api_user.service')->getListOfAllUsers();

        // Every company is available
        $companyArray = $this->get('api_company.service')->getListOfAllCompanies();

        // Public and logged user's tags are available
        $tagArray = $this->get('tag_service')->getListOfUsersTags($this->getUser()->getId());

        // Every unit is available
        $unitArray = $this->get('unit_service')->getListOfAllUnits();

        // Available assigners are based on project of task
        $project = $task->getProject();
        if (!$project instanceof Project) {
            // If task has not project, just creator of the task can be assigned to it
            $assignArray = [
                [
                    'id' => $task->getCreatedBy()->getId(),
                    'username' => $task->getCreatedBy()->getUsername()
                ]
            ];
        } else {
            // If task has project, assigner has to have RESOLVE_TASK ACL in user_has_project
            $assignArray = $this->get('api_user.service')->getListOfAvailableProjectAssigners($project, ProjectAclOptions::RESOLVE_TASK);
        }

        // Task attributes - the list of active task attributes with TITLE, TYPE and OPTIONS
        $taskAttributes = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->getAllActiveEntitiesWithTypeOptions();

        $response = [
            'status' => $statusesArray,
            'project' => $projectsArray,
            'requester' => $requesterArray,
            'company' => $companyArray,
            'tag' => $tagArray,
            'assigner' => $assignArray,
            'unit' => $unitArray,
            'taskAttributes' => $taskAttributes
        ];
        return $this->json($response, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \LogicException
     */
    private function getFilterData(Request $request): array
    {
        $data = [];

        $search = $request->get('search');
        $status = $request->get('status');
        $project = $request->get('project');
        $creator = $request->get('creator');
        $requester = $request->get('requester');
        $company = $request->get('company');
        $assigned = $request->get('assigned');
        $tag = $request->get('tag');
        $follower = $request->get('follower');
        $created = $request->get('createdTime');
        $started = $request->get('startedTime');
        $deadline = $request->get('deadlineTime');
        $closed = $request->get('closedTime');
        $archived = $request->get('archived');
        $important = $request->get('important');
        $addedParameters = $request->get('addedParameters');

        if (null !== $search) {
            $data[FilterAttributeOptions::SEARCH] = $search;
        }
        if (null !== $status) {
            $data[FilterAttributeOptions::STATUS] = $status;
        }
        if (null !== $project) {
            $data[FilterAttributeOptions::PROJECT] = $project;
        }
        if (null !== $creator) {
            $data[FilterAttributeOptions::CREATOR] = $creator;
        }
        if (null !== $requester) {
            $data[FilterAttributeOptions::REQUESTER] = $requester;
        }
        if (null !== $company) {
            $data[FilterAttributeOptions::COMPANY] = $company;
        }
        if (null !== $assigned) {
            $data[FilterAttributeOptions::ASSIGNED] = $assigned;
        }
        if (null !== $tag) {
            $data[FilterAttributeOptions::TAG] = $tag;
        }
        if (null !== $follower) {
            $data[FilterAttributeOptions::FOLLOWER] = $follower;
        }
        if (null !== $created) {
            $data[FilterAttributeOptions::CREATED] = $created;
        }
        if (null !== $started) {
            $data[FilterAttributeOptions::STARTED] = $started;
        }
        if (null !== $deadline) {
            $data[FilterAttributeOptions::DEADLINE] = $deadline;
        }
        if (null !== $closed) {
            $data[FilterAttributeOptions::CLOSED] = $closed;
        }
        if ('true' === strtolower($archived)) {
            $data[FilterAttributeOptions::ARCHIVED] = $archived;
        }
        if ('true' === strtolower($important)) {
            $data[FilterAttributeOptions::IMPORTANT] = $important;
        }
        if (null !== $addedParameters) {
            $data[FilterAttributeOptions::ADDED_PARAMETERS] = $addedParameters;
        }

        return $this->processFilterData($data);
    }

    /**
     * @param array $filterDataArray
     *
     * @return array
     * @throws \LogicException
     */
    private function getFilterDataFromSavedFilterArray(array $filterDataArray): array
    {
        $data = [];

        if (isset($filterDataArray[FilterAttributeOptions::SEARCH])) {
            $data[FilterAttributeOptions::SEARCH] = $filterDataArray[FilterAttributeOptions::SEARCH];
        }
        if (isset($filterDataArray[FilterAttributeOptions::STATUS])) {
            $data[FilterAttributeOptions::STATUS] = $filterDataArray[FilterAttributeOptions::STATUS];
        }
        if (isset($filterDataArray[FilterAttributeOptions::PROJECT])) {
            $data[FilterAttributeOptions::PROJECT] = $filterDataArray[FilterAttributeOptions::PROJECT];
        }
        if (isset($filterDataArray[FilterAttributeOptions::CREATOR])) {
            $data[FilterAttributeOptions::CREATOR] = $filterDataArray[FilterAttributeOptions::CREATOR];
        }
        if (isset($filterDataArray[FilterAttributeOptions::REQUESTER])) {
            $data[FilterAttributeOptions::REQUESTER] = $filterDataArray[FilterAttributeOptions::REQUESTER];
        }
        if (isset($filterDataArray[FilterAttributeOptions::COMPANY])) {
            $data[FilterAttributeOptions::COMPANY] = $filterDataArray[FilterAttributeOptions::COMPANY];
        }
        if (isset($filterDataArray[FilterAttributeOptions::ASSIGNED])) {
            $data[FilterAttributeOptions::ASSIGNED] = $filterDataArray[FilterAttributeOptions::ASSIGNED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::TAG])) {
            $data[FilterAttributeOptions::TAG] = $filterDataArray[FilterAttributeOptions::TAG];
        }
        if (isset($filterDataArray[FilterAttributeOptions::FOLLOWER])) {
            $data[FilterAttributeOptions::FOLLOWER] = $filterDataArray[FilterAttributeOptions::FOLLOWER];
        }
        if (isset($filterDataArray[FilterAttributeOptions::CREATED])) {
            $data[FilterAttributeOptions::CREATED] = $filterDataArray[FilterAttributeOptions::CREATED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::STARTED])) {
            $data[FilterAttributeOptions::STARTED] = $filterDataArray[FilterAttributeOptions::STARTED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::DEADLINE])) {
            $data[FilterAttributeOptions::DEADLINE] = $filterDataArray[FilterAttributeOptions::DEADLINE];
        }
        if (isset($filterDataArray[FilterAttributeOptions::CLOSED])) {
            $data[FilterAttributeOptions::CLOSED] = $filterDataArray[FilterAttributeOptions::CLOSED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::ARCHIVED])) {
            $data[FilterAttributeOptions::ARCHIVED] = $filterDataArray[FilterAttributeOptions::ARCHIVED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::IMPORTANT])) {
            $data[FilterAttributeOptions::IMPORTANT] = $filterDataArray[FilterAttributeOptions::IMPORTANT];
        }
        if (isset($filterDataArray[FilterAttributeOptions::ADDED_PARAMETERS])) {
            $data[FilterAttributeOptions::ADDED_PARAMETERS] = $filterDataArray[FilterAttributeOptions::ADDED_PARAMETERS];
        }

        return $this->processFilterData($data);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \LogicException
     */
    private function processFilterData(array $data): array
    {
        // Ina-beznejsia moznost ako zadavat pole hodnot v URL adrese, ktora vracia priamo pole: index.php?id[]=1&id[]=2&id[]=3&name=john
        // na zakodovanie dat do URL je mozne pouzit encodeURIComponent

        $inFilter = [];
        $dateFilter = [];
        $equalFilter = [];
        $isNullFilter = [];
        $notAndCurrentFilter = [];
        $searchFilter = null;

        $inFilterAddedParams = [];
        $dateFilterAddedParams = [];
        $equalFilterAddedParams = [];

        $filterForUrl = [];

        if (isset($data[FilterAttributeOptions::SEARCH])) {
            $searchFilter = $data[FilterAttributeOptions::SEARCH];
            $filterForUrl['search'] = '&search=' . $data['search'];
        }
        if (isset($data[FilterAttributeOptions::STATUS])) {
            $inFilter['status.id'] = explode(',', $data[FilterAttributeOptions::STATUS]);
            $equalFilter['taskHasAssignedUsers.actual'] = 1;
            $filterForUrl['status'] = '&status=' . $data[FilterAttributeOptions::STATUS];
        }
        if (isset($data[FilterAttributeOptions::PROJECT])) {
            $project = $data[FilterAttributeOptions::PROJECT];
            if ('not' === strtolower($project)) {
                $isNullFilter[] = 'task.project';
            } elseif ('current-user' === strtolower($project)) {
                $equalFilter['projectCreator.id'] = $this->getUser()->getId();
            } else {
                $inFilter['project.id'] = explode(',', $project);
            }
            $filterForUrl['project'] = '&project=' . $project;
        }
        if (isset($data[FilterAttributeOptions::CREATOR])) {
            $creator = $data[FilterAttributeOptions::CREATOR];
            if ('current-user' === strtolower($creator)) {
                $equalFilter['createdBy.id'] = $this->getUser()->getId();
            } else {
                $inFilter['createdBy.id'] = explode(',', $creator);
            }
            $filterForUrl['createdBy'] = '&creator=' . $creator;
        }
        if (isset($data[FilterAttributeOptions::REQUESTER])) {
            $requester = $data[FilterAttributeOptions::REQUESTER];
            if ('current-user' === strtolower($requester)) {
                $equalFilter['requestedBy.id'] = $this->getUser()->getId();
            } else {
                $inFilter['requestedBy.id'] = explode(',', $requester);
            }
            $filterForUrl['requestedBy'] = '&requester=' . $requester;
        }
        if (isset($data[FilterAttributeOptions::COMPANY])) {
            $company = $data[FilterAttributeOptions::COMPANY];
            if ('current-user' === strtolower($company)) {
                $equalFilter['taskCompany.id'] = $this->getUser()->getCompany()->getId();
            } else {
                $inFilter['taskCompany.id'] = explode(',', $company);
            }
            $filterForUrl['taskCompany'] = '&taskCompany=' . $company;
        }
        if (isset($data[FilterAttributeOptions::ASSIGNED])) {
            $assigned = $data[FilterAttributeOptions::ASSIGNED];
            $assignedArray = explode(',', $assigned);

            if (\in_array('not', $assignedArray, true) && \in_array('current-user', $assignedArray, true)) {
                $notAndCurrentFilter[] = [
                    'not' => 'thau.user',
                    'equal' => [
                        'key' => 'assignedUser.id',
                        'value' => $this->getUser()->getId(),
                    ],
                ];
            } elseif ('not' === strtolower($assigned)) {
                $isNullFilter[] = 'thau.user';
            } elseif ('current-user' === strtolower($assigned)) {
                $equalFilter['assignedUser.id'] = $this->getUser()->getId();
            } else {
                $inFilter['assignedUser.id'] = explode(',', $assigned);
            }

            $filterForUrl['assigned'] = '&assigned=' . $assigned;
        }
        if (isset($data[FilterAttributeOptions::TAG])) {
            $tag = $data[FilterAttributeOptions::TAG];
            $inFilter['tags.id'] = explode(',', $tag);
            $filterForUrl['tag'] = '&tag=' . $tag;
        }
        if (isset($data[FilterAttributeOptions::FOLLOWER])) {
            $follower = $data[FilterAttributeOptions::FOLLOWER];
            if ('current-user' === $follower) {
                $equalFilter['followers.id'] = $this->getUser()->getId();
            } else {
                $inFilter['followers.id'] = explode(',', $follower);
            }
            $filterForUrl['followers'] = '&follower=' . $follower;
        }
        if (isset($data[FilterAttributeOptions::CREATED])) {
            $created = $data[FilterAttributeOptions::CREATED];
            $fromToData = $this->separateFromToDateData($created);
            $dateFilter['task.createdAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['created'] = '&createdTime=' . $created;
        }
        if (isset($data[FilterAttributeOptions::STARTED])) {
            $started = $data[FilterAttributeOptions::STARTED];
            $fromToData = $this->separateFromToDateData($started);
            $dateFilter['task.startedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['started'] = '&startedTime=' . $started;
        }
        if (isset($data[FilterAttributeOptions::DEADLINE])) {
            $deadline = $data[FilterAttributeOptions::DEADLINE];
            $fromToData = $this->separateFromToDateData($deadline);
            $dateFilter['task.deadline'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['deadline'] = '&deadlineTime=' . $deadline;
        }
        if (isset($data[FilterAttributeOptions::CLOSED])) {
            $closed = $data[FilterAttributeOptions::CLOSED];
            $fromToData = $this->separateFromToDateData($closed);
            $dateFilter['task.closedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['closed'] = '&closedTime=' . $closed;
        }
        if (isset($data[FilterAttributeOptions::ARCHIVED])) {
            if ('true' === strtolower($data[FilterAttributeOptions::ARCHIVED])) {
                $equalFilter['project.is_active'] = 0;
                $filterForUrl['archived'] = '&archived=TRUE';
            }
        }
        if (isset($data[FilterAttributeOptions::IMPORTANT])) {
            if ('true' === strtolower($data[FilterAttributeOptions::IMPORTANT])) {
                $equalFilter['task.important'] = 1;
                $filterForUrl['important'] = '&important=TRUE';
            }
        }
        if (isset($data[FilterAttributeOptions::ADDED_PARAMETERS])) {
            $addedParameters = $data[FilterAttributeOptions::ADDED_PARAMETERS];
            $arrayOfAddedParameters = explode('&', $addedParameters);

            if (!empty($arrayOfAddedParameters[0])) {
                $filterForUrl['addedParameters'] = '&addedParameters=' . $addedParameters;

                foreach ($arrayOfAddedParameters as $value) {
                    $strpos = explode('=', $value);
                    $attributeId = $strpos[0];

                    // Check if TaskAttribute exists, select filter type based on it's TYPE
                    $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($attributeId);
                    if ($taskAttribute instanceof TaskAttribute) {
                        $typeOfTaskAttribute = $taskAttribute->getType();
                        $attributeValues = explode(',', $strpos[1]);

                        if ('checkbox' === $typeOfTaskAttribute) {
                            if ('true' === strtolower($strpos[1])) {
                                $equalFilterAddedParams[$attributeId] = 1;
                            } elseif ('false' === strtolower($strpos[1])) {
                                $equalFilterAddedParams[$attributeId] = 0;
                            }
                        }

                        if ('date' === $typeOfTaskAttribute) {
                            $dateFilterAddedParams[$attributeId] = $attributeValues;
                        }

                        $inFilterAddedParams[$attributeId] = $attributeValues;
                    }
                }
            }
        }

        return [
            'inFilter' => $inFilter,
            'equalFilter' => $equalFilter,
            'dateFilter' => $dateFilter,
            'isNullFilter' => $isNullFilter,
            'searchFilter' => $searchFilter,
            'notAndCurrentFilter' => $notAndCurrentFilter,
            'inFilterAddedParams' => $inFilterAddedParams,
            'equalFilterAddedParams' => $equalFilterAddedParams,
            'dateFilterAddedParams' => $dateFilterAddedParams,
            'filterForUrl' => $filterForUrl,
        ];
    }

    /**
     * @param string $created
     *
     * @return array
     */
    private function separateFromToDateData(string $created): array
    {
        $fromPosition = strpos($created, 'FROM=');
        $toPosition = strpos($created, 'TO=');

        $toDataDate = null;
        $fromDataDate = null;
        if (false !== $fromPosition && false !== $toPosition) {
            $fromData = substr($created, $fromPosition + 5, $toPosition - 6);
            $toData = substr($created, $toPosition + 3);

            $fromDataTimestamp = (int)$fromData;
            $fromDataDate = new \DateTime("@$fromDataTimestamp");

            if ('now' === $toData) {
                $toDataDate = new \DateTime();
            } else {
                $toDataTimestamp = (int)$toData;
                $toDataDate = new \DateTime("@$toDataTimestamp");
            }
        } elseif (false !== $fromPosition && false === $toPosition) {
            $fromData = substr($created, $fromPosition + 5);
            $fromDataTimestamp = (int)$fromData;
            $fromDataDate = new \DateTime("@$fromDataTimestamp");
        } elseif (false !== $toPosition && false === $fromPosition) {
            $toData = substr($created, $toPosition + 3);
            if ('now' === $toData) {
                $toDataDate = new \DateTime();
            } else {
                $toDataTimestamp = (int)$toData;
                $toDataDate = new \DateTime("@$toDataTimestamp");
            }
        }

        $response = [
            'from' => $fromDataDate,
            'to' => $toDataDate,
        ];

        return $response;
    }

    /**
     * @param TaskHasAssignedUser $entity
     * @param Project $project
     * @return bool
     * @throws \LogicException
     */
    private function checkIfUserHasResolveTaskAclPermission(TaskHasAssignedUser $entity, Project $project): bool
    {
        $user = $entity->getUser();
        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $user,
            'project' => $project
        ]);
        if ($userHasProject instanceof UserHasProject) {
            $acl = $userHasProject->getAcl();
            if (\in_array(ProjectAclOptions::RESOLVE_TASK, $acl, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string|null $orderString
     * @return array|Response
     * @throws \InvalidArgumentException
     */
    private function processOrderData($orderString)
    {
        $order = [];
        if (null !== $orderString) {
            $orderArray = explode(',', $orderString);
            foreach ($orderArray as $item) {
                $orderArrayKeyValue = explode('=>', $item);
                //Check if param to order by is allowed
                if (!\in_array($orderArrayKeyValue[0], FilterAttributeOptions::getConstants(), true)) {
                    return $this->createApiResponse([
                        'message' => 'Requested filter parameter ' . $orderArrayKeyValue[0] . ' is not allowed!',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
                $orderArrayKeyValueLowwer = strtolower($orderArrayKeyValue[1]);
                if (!($orderArrayKeyValueLowwer === 'asc' || $orderArrayKeyValueLowwer === 'desc')) {
                    return $this->createApiResponse([
                        'message' => $orderArrayKeyValue[1] . ' Is not allowed! You can order data only ASC or DESC!',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
                $order[$orderArrayKeyValue[0]] = $orderArrayKeyValue[1];
            }
        }
        if (\count($order) === 0) {
            $order[FilterAttributeOptions::ID] = 'DESC';
        }
        return $order;
    }

    /**
     * @param Task $task
     * @param string $loggedUserEmail
     * @return array
     */
    private function getEmailForUpdateTaskNotification(Task $task, string $loggedUserEmail): array
    {
        $notificationEmailAddresses = [];

        $requesterEmail = $task->getRequestedBy()->getEmail();
        if ($loggedUserEmail !== $requesterEmail && !in_array($requesterEmail, $notificationEmailAddresses, true)) {
            $notificationEmailAddresses[] = $requesterEmail;
        }

        $followers = $task->getFollowers();
        if (count($followers) > 0) {
            /** @var User $follower */
            foreach ($followers as $follower) {
                $followerEmail = $follower->getEmail();
                if ($loggedUserEmail !== $followerEmail && !in_array($followerEmail, $notificationEmailAddresses)) {
                    $notificationEmailAddresses[] = $followerEmail;
                }
            }
        }

        $assignedUsers = $task->getTaskHasAssignedUsers();
        if (count($assignedUsers) > 0) {
            /** @var TaskHasAssignedUser $item */
            foreach ($assignedUsers as $item) {
                $assignedUserEmail = $item->getUser()->getEmail();
                if ($loggedUserEmail !== $assignedUserEmail && !in_array($assignedUserEmail, $notificationEmailAddresses, true)) {
                    $notificationEmailAddresses[] = $assignedUserEmail;
                }
            }
        }

        return $notificationEmailAddresses;
    }

    /**
     * @param int $taskId
     * @param string $title
     * @param array $emailAddresses
     * @param User $user
     * @param array $changedParams
     * @return array
     * @throws \LogicException
     */
    private
    function getTemplateParams(int $taskId, string $title, array $emailAddresses, User $user, array $changedParams): array
    {
        $userDetailData = $user->getDetailData();
        if ($userDetailData instanceof UserData) {
            $username = $userDetailData->getName() . ' ' . $userDetailData->getSurname();
        } else {
            $username = '';
        }
        $todayDate = new \DateTime();
        $email = $user->getEmail();
        $baseFrontURL = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->findOneBy([
            'title' => 'Base Front URL'
        ]);
        $templateParams = [
            'date' => $todayDate,
            'username' => $username,
            'email' => $email,
            'taskId' => $taskId,
            'subject' => $title,
            'taskLink' => $baseFrontURL->getValue() . '/tasks/' . $taskId,
            'changedParams' => implode(', ', $changedParams)
        ];
        $params = [
            'subject' => 'LanHelpdesk - ' . '[#' . $taskId . '] ' . 'Úloha bola zmenená',
            'from' => $email,
            'to' => $emailAddresses,
            'body' => $this->renderView('@APITask/Emails/taskUpdate.html.twig', $templateParams)
        ];

        return $params;
    }

    /**
     * @param array $requestData
     * @param array $changedParams
     * @return array
     */
    private
    function getChangedParams(array &$requestData, array &$changedParams): array
    {
        if (\count($requestData) > 0) {
            foreach ($requestData as $key => $value) {
                $changedParams[] = $key;
            }
        }

        return $changedParams;
    }

    /**
     * @param array $tasksArray
     * @return array
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function addCanEditParamToEveryTask(array $tasksArray): array
    {
        $tasksModified = [];
        if (\count($tasksArray['data']) > 0) {
            foreach ($tasksArray['data'] as $task) {
                $taskEntityFromDb = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($task['id']);
                // Check if user can update selected task
                if ($this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $taskEntityFromDb)) {
                    $canEditArray = ['canEdit' => true];
                } else {
                    $canEditArray = ['canEdit' => false];
                }
                $task = array_merge($task, $canEditArray);
                $tasksModified['data'][] = $task;
            }
            $tasksModified['_links'] = $tasksArray['_links'];
            $tasksModified['total'] = $tasksArray['total'];
            $tasksModified['page'] = $tasksArray['page'];
            $tasksModified['numberOfPages'] = $tasksArray['numberOfPages'];
        } else {
            $tasksModified = $tasksArray;
        }

        return $tasksModified;
    }
}
