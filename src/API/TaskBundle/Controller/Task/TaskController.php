<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\File;
use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Notification;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Entity\TaskData;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\TaskHasAttachment;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Security\StatusFunctionOptions;
use API\TaskBundle\Security\StatusOptions;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Security\VoteOptions;
use API\TaskBundle\Security\FilterAttributeOptions;
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
     *            "status":
     *            {
     *               "id": 1802,
     *               "title": "New",
     *               "color": "#FF4500"
     *            },
     *            "taskData":
     *            [
     *              {
     *                 "id": 113,
     *                 "value": "some input",
     *                 "boolValue": null,
     *                 "dateValue": null,
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
     *            "taskHasAssignedUsers":
     *            {
     *               "313":
     *               {
     *                  "id": 7,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt": 1519237291,
     *                  "updatedAt": 1519237291,
     *                  "status":
     *                  {
     *                      "id": 15,
     *                      "title": "Completed",
     *                      "color": "#FF4500"
     *                  },
     *                  "user":
     *                  {
     *                      "id": 313,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk",
     *                      "name": "Admin",
     *                      "surname": "Adminovic"
     *                  }
     *              }
     *            },
     *            "taskHasAttachments":
     *            [
     *              {
     *                  "id": 17,
     *                  "slug": "coming-soon1-png-2018-04-06-06-50",
     *                  "name": "coming-soon1.png"
     *              },
     *              {
     *                  "id": 19,
     *                  "slug": "left-png-2018-04-14-10-33",
     *                  "name": "left.png"
     *              }
     *            ],
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
     *  description="Returns a list of full Task Entities which include extended Task Data",
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
     *       "description"="String of key=>value values, where KEY is a column to sort by, VALUE is ASC or DESC order chart"
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
     *       "description"="& separated data in a form: taskAttributeId=value1,value2&taskAttributeId=value"
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
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody, true);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];
            $processedArray = $this->processOrderData($order);
            if (false === $processedArray['correct']) {
                $response = $response->setContent(json_encode(['message' => $processedArray['message']]));
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                return $response;
            } else {
                $orderProcessed = $processedArray['message'];
            }

            $processedFilterData = $this->getFilterData($requestBody);

            $options = [
                'loggedUser' => $this->getUser(),
                'isAdmin' => $this->get('task_voter')->isAdmin(),
                'inFilter' => $processedFilterData['inFilter'],
                'equalFilter' => $processedFilterData['equalFilter'],
                'isNullFilter' => $processedFilterData['isNullFilter'],
                'dateFilter' => $processedFilterData['dateFilter'],
                'searchFilter' => $processedFilterData['searchFilter'],
                'notAndCurrentFilter' => $processedFilterData['notAndCurrentFilter'],
                'inFilterAddedParams' => $processedFilterData['inFilterAddedParams'],
                'equalFilterAddedParams' => $processedFilterData['equalFilterAddedParams'],
                'dateFilterAddedParams' => $processedFilterData['dateFilterAddedParams'],
                'filtersForUrl' => $processedFilterData['filterForUrl'],
                'order' => $orderProcessed,
                'limit' => $limit,
                'project' => null
            ];

            $tasksArray = $this->get('task_service')->getTasksResponse($page, $options);

            // Every Task need an additional canEdit Value
            $tasksModified = $this->addCanEditParamToEveryTask($tasksArray);

            $response = $response->setContent(json_encode($tasksModified));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }

        return $response;
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
     *            "status":
     *            {
     *               "id": 1802,
     *               "title": "New",
     *               "color": "#FF4500"
     *            },
     *            "taskData":
     *            [
     *              {
     *                 "id": 113,
     *                 "value": "some input",
     *                 "boolValue": null,
     *                 "dateValue": null,
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
     *            "taskHasAssignedUsers":
     *            {
     *               "313":
     *               {
     *                  "id": 7,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt": 1519237291,
     *                  "updatedAt": 1519237291,
     *                  "status":
     *                  {
     *                      "id": 15,
     *                      "title": "Completed",
     *                      "color": "#FF4500"
     *                  },
     *                  "user":
     *                  {
     *                      "id": 313,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk",
     *                      "name": "Admin",
     *                      "surname": "Adminovic"
     *                  }
     *              }
     *            },
     *            "taskHasAttachments":
     *            [
     *              {
     *                  "id": 17,
     *                  "slug": "coming-soon1-png-2018-04-06-06-50",
     *                  "name": "coming-soon1.png"
     *              },
     *              {
     *                  "id": 19,
     *                  "slug": "left-png-2018-04-14-10-33",
     *                  "name": "left.png"
     *              }
     *            ],
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
     *  description="Returns a list of full Task Entities selected by rules of a requested Filter",
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
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listSavedFilterAction(Request $request, int $filterId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_list_saved_filter', ['filterId' => $filterId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($filterId);

        if (!$filter instanceof Filter) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));
            return $response;
        }

        // Check if logged user has permission to see requested filter
        if (!$this->get('filter_voter')->isGranted(VoteOptions::SHOW_FILTER, $filter)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody, true);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];
            $processedArray = $this->processOrderData($order);
            if (false === $processedArray['correct']) {
                $response = $response->setContent(json_encode(['message' => $processedArray['message']]));
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                return $response;
            } else {
                $orderProcessed = $processedArray['message'];
            }

            $filterDataArray = $filter->getFilter();
            $filterData = $this->getFilterDataFromSavedFilterArray($filterDataArray);

            if ($filter->getProject()) {
                $projectId = $filter->getProject()->getId();
            } else {
                $projectId = null;
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
                'filtersForUrl' => array_merge($filterData['filterForUrl'], ['order' => '&order=' . $order]),
                'order' => $orderProcessed,
                'limit' => $limit,
                'project' => $projectId
            ];
            $tasksArray = $this->get('task_service')->getTasksResponse($page, $options);

            // Every Task need an additional canEdit Value
            $tasksModified = $this->addCanEditParamToEveryTask($tasksArray);

            $response = $response->setContent(json_encode($tasksModified));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }

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
     *            "status":
     *            {
     *               "id": 1802,
     *               "title": "New",
     *               "color": "#FF4500"
     *            },
     *            "taskData":
     *            [
     *              {
     *                 "id": 113,
     *                 "value": "some input",
     *                 "boolValue": null,
     *                 "dateValue": null,
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
     *            {
     *               "313":
     *               {
     *                  "id": 7,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt": 1519237291,
     *                  "updatedAt": 1519237291,
     *                  "status":
     *                  {
     *                      "id": 15,
     *                      "title": "Completed",
     *                      "color": "#FF4500"
     *                  },
     *                  "user":
     *                  {
     *                      "id": 313,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk",
     *                      "name": "Admin",
     *                      "surname": "Adminovic"
     *                  }
     *              }
     *            },
     *            "taskHasAttachments":
     *            [
     *              {
     *                  "id": 17,
     *                  "slug": "coming-soon1-png-2018-04-06-06-50",
     *                  "name": "coming-soon1.png"
     *              },
     *              {
     *                  "id": 19,
     *                  "slug": "left-png-2018-04-14-10-33",
     *                  "name": "left.png"
     *              }
     *            ],
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
     *            "update 1": "/api/v1/task-bundle/tasks/11998/project/20/status/14/requester/318/company/300",
     *            "update 2": "/api/v1/task-bundle/tasks/11998/project/20",
     *            "update 3": "/api/v1/task-bundle/tasks/11998/project/20/status/14",
     *            "update 4": "/api/v1/task-bundle/tasks/11998/project/20/status/14/requester/318",
     *            "update 5": "/api/v1/task-bundle/tasks/11998/status/14",
     *            "update 6": "/api/v1/task-bundle/tasks/11998/status/14/requester/318",
     *            "update 7": "/api/v1/task-bundle/tasks/11998/status/14/requester/318/company/300",
     *            "update 8": "/api/v1/task-bundle/tasks/11998/requester/318",
     *            "update 9": "/api/v1/task-bundle/tasks/11998/requester/318/company/300",
     *            "update 10": "/api/v1/task-bundle/tasks/11998/company/300",
     *            "delete": "/api/v1/task-bundle/tasks/11998"
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
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getAction(int $id):Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('task', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
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
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($taskArray));
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
     *            "status":
     *            {
     *               "id": 1802,
     *               "title": "New",
     *               "color": "#FF4500"
     *            },
     *            "taskData":
     *            [
     *              {
     *                 "id": 113,
     *                 "value": "some input",
     *                 "boolValue": null,
     *                 "dateValue": null,
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
     *            {
     *               "313":
     *               {
     *                  "id": 7,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt": 1519237291,
     *                  "updatedAt": 1519237291,
     *                  "status":
     *                  {
     *                      "id": 15,
     *                      "title": "Completed",
     *                      "color": "#FF4500"
     *                  },
     *                  "user":
     *                  {
     *                      "id": 313,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk",
     *                      "name": "Admin",
     *                      "surname": "Adminovic"
     *                  }
     *              }
     *            },
     *            "taskHasAttachments":
     *            [
     *              {
     *                  "id": 17,
     *                  "slug": "coming-soon1-png-2018-04-06-06-50",
     *                  "name": "coming-soon1.png"
     *              },
     *              {
     *                  "id": 19,
     *                  "slug": "left-png-2018-04-14-10-33",
     *                  "name": "left.png"
     *              }
     *            ],
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
     *        "_links":
     *       {
     *            "update 1": "/api/v1/task-bundle/tasks/11998/project/20/status/14/requester/318/company/300",
     *            "update 2": "/api/v1/task-bundle/tasks/11998/project/20",
     *            "update 3": "/api/v1/task-bundle/tasks/11998/project/20/status/14",
     *            "update 4": "/api/v1/task-bundle/tasks/11998/project/20/status/14/requester/318",
     *            "update 5": "/api/v1/task-bundle/tasks/11998/status/14",
     *            "update 6": "/api/v1/task-bundle/tasks/11998/status/14/requester/318",
     *            "update 7": "/api/v1/task-bundle/tasks/11998/status/14/requester/318/company/300",
     *            "update 8": "/api/v1/task-bundle/tasks/11998/requester/318",
     *            "update 9": "/api/v1/task-bundle/tasks/11998/requester/318/company/300",
     *            "update 10": "/api/v1/task-bundle/tasks/11998/company/300",
     *            "delete": "/api/v1/task-bundle/tasks/11998"
     *       }
     *    }
     *
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Task Entity.",
     *  parameters={
     *      {"name"="title", "dataType"="string", "required"=true,  "description"="Tasks title"},
     *      {"name"="description", "dataType"="string", "required"=false,  "description"="Tasks description"},
     *      {"name"="deadline", "dataType"="datetime", "required"=false,  "description"="Deadline date"},
     *      {"name"="startedAt", "dataType"="datetime", "required"=false,  "description"="Planned start date"},
     *      {"name"="closedAt", "dataType"="datetime", "required"=false,  "description"="Closure date"},
     *      {"name"="important", "dataType"="boolean", "required"=false,  "description"="set TRUE if the Task should be checked as IMPORTANT"},
     *      {"name"="work", "dataType"="string", "required"=false,  "description"="Work description"},
     *      {"name"="workTime", "dataType"="string", "required"=false,  "description"="Work time"},
     *      {"name"="tag", "dataType"="array", "required"=false,  "description"="Tag titles array: [tag1, tag2]"},
     *      {"name"="assigned", "dataType"="array", "required"=false,  "description"="UserId - assigner and StatusId collection: [userId => 12, statusId => 5]"},
     *      {"name"="attachment", "dataType"="array", "required"=false,  "description"="Attachment slugs array: [slug1, slug2]"},
     *      {"name"="taskData", "dataType"="array", "required"=false,  "description"="Tasks additional attributes array: [taskAttributeId => value, taskAttributeId2 => values]. Format: $json array - http://php.net/manual/en/function.json-decode.php"},
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
     * @param int $projectId
     * @param int $statusId
     * @param bool|int $requesterId
     * @param bool|int $companyId
     * @return Response
     * @throws \LogicException
     */
    public function createAction(Request $request, $projectId, $statusId, $requesterId = false, $companyId = false): Response
    {
        // JSON API Response - Content type and Location settings
        if ($requesterId && $companyId) {
            $locationURL = $this->generateUrl('tasks_create_project_status_requester_company', ['projectId' => $projectId, 'statusId' => $statusId, 'requesterId' => $requesterId, 'companyId' => $companyId]);
        } elseif ($requesterId) {
            $locationURL = $this->generateUrl('tasks_create_project_status_requester', ['projectId' => $projectId, 'statusId' => $statusId, 'requesterId' => $requesterId]);
        } elseif ($companyId) {
            $locationURL = $this->generateUrl('tasks_create_project_status_company', ['projectId' => $projectId, 'statusId' => $statusId, 'companyId' => $companyId]);
        } else {
            $locationURL = $this->generateUrl('tasks_create_project_status', ['projectId' => $projectId, 'statusId' => $statusId]);
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        // Check if logged user has ACL to create task
        $aclOptions = [
            'acl' => UserRoleAclOptions::CREATE_TASKS,
            'user' => $this->getUser(),
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $task = new Task();
        $task->setCreatedBy($this->getUser());

        //Decode sent parameters
        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        } else {
            // Check if user can create task in a selected project
            if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK_IN_PROJECT, $project)) {
                $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                $response = $response->setContent(json_encode(['message' => 'Permission denied! You can not create task in a selected project!']));
                return $response;
            }
            $task->setProject($project);
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($statusId);
        if (!$status instanceof Status) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Status with requested Id does not exist!']));
            return $response;
        } else {
            // Check Status function. If it is CLOSED, closedAt param is required.
            // Only Task with a COMPANY can be closed!
            // If it IS IN PROGRESS or COMPLETED, startedAt param has to be set
            if ($status->getFunction() === StatusFunctionOptions::CLOSED_TASK) {
                if (null === $task->getCompany()) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Company is required for a tasks with CLOSED Status!']));
                    return $response;
                }

                if (!isset($requestBody['closedAt'])) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => ' ClosedAt param is required for a tasks with CLOSED Status!']));
                    return $response;
                }
            } elseif ($status->getFunction() === StatusFunctionOptions::IN_PROGRESS_TASK || $status->getFunction() === StatusFunctionOptions::COMPLETED_TASK) {
                if (!isset($requestBody['startedAt'])) {
                    $task->setStartedAt(new \DateTime());
                }
            }
            $task->setStatus($status);
        }

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        if ($requesterId) {
            $requester = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requesterId);
            if (!$requester instanceof User) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Requester with requested Id does not exist!']));
                return $response;
            }
            $task->setRequestedBy($requester);
        } else {
            $task->setRequestedBy($loggedUser);
        }

        if ($companyId) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($companyId);
            if (!$company instanceof Company) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Company with requested Id does not exist!']));
                return $response;
            }
            $task->setCompany($company);
        } else {
            $usersCompany = $loggedUser->getCompany();
            if (!$usersCompany instanceof Company) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Company has to be set for a task! Logged user does not have a company what could be set up!']));
                return $response;
            }
            $task->setCompany($usersCompany);
        }

        return $this->updateTask($task, $requestBody, $locationURL, $status, true);
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
     *            "status":
     *            {
     *               "id": 1802,
     *               "title": "New",
     *               "color": "#FF4500"
     *            },
     *            "taskData":
     *            [
     *              {
     *                 "id": 113,
     *                 "value": "some input",
     *                 "bollValue": null,
     *                 "dateValue": null,
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
     *            {
     *               "313":
     *               {
     *                  "id": 7,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt": 1519237291,
     *                  "updatedAt": 1519237291,
     *                  "status":
     *                  {
     *                      "id": 15,
     *                      "title": "Completed",
     *                      "color": "#FF4500"
     *                  },
     *                  "user":
     *                  {
     *                      "id": 313,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk",
     *                      "name": "Admin",
     *                      "surname": "Adminovic"
     *                  }
     *              }
     *            },
     *            "taskHasAttachments":
     *            [
     *              {
     *                  "id": 17,
     *                  "slug": "coming-soon1-png-2018-04-06-06-50",
     *                  "name": "coming-soon1.png"
     *              },
     *              {
     *                  "id": 19,
     *                  "slug": "left-png-2018-04-14-10-33",
     *                  "name": "left.png"
     *              }
     *            ],
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
     *        "_links":
     *       {
     *            "update 1": "/api/v1/task-bundle/tasks/11998/project/20/status/14/requester/318/company/300",
     *            "update 2": "/api/v1/task-bundle/tasks/11998/project/20",
     *            "update 3": "/api/v1/task-bundle/tasks/11998/project/20/status/14",
     *            "update 4": "/api/v1/task-bundle/tasks/11998/project/20/status/14/requester/318",
     *            "update 5": "/api/v1/task-bundle/tasks/11998/status/14",
     *            "update 6": "/api/v1/task-bundle/tasks/11998/status/14/requester/318",
     *            "update 7": "/api/v1/task-bundle/tasks/11998/status/14/requester/318/company/300",
     *            "update 8": "/api/v1/task-bundle/tasks/11998/requester/318",
     *            "update 9": "/api/v1/task-bundle/tasks/11998/requester/318/company/300",
     *            "update 10": "/api/v1/task-bundle/tasks/11998/company/300",
     *            "delete": "/api/v1/task-bundle/tasks/11998"
     *       }
     *    }
     *
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Update Task Entity.",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a processed task"
     *     }
     *  },
     *  parameters={
     *      {"name"="title", "dataType"="string", "required"=true,  "description"="Tasks title"},
     *      {"name"="description", "dataType"="string", "required"=false,  "description"="Tasks description"},
     *      {"name"="deadline", "dataType"="datetime", "required"=false,  "description"="Deadline date"},
     *      {"name"="startedAt", "dataType"="datetime", "required"=false,  "description"="Planned start date"},
     *      {"name"="closedAt", "dataType"="datetime", "required"=false,  "description"="Closure date"},
     *      {"name"="important", "dataType"="boolean", "required"=false,  "description"="set TRUE if the Task should be checked as IMPORTANT"},
     *      {"name"="work", "dataType"="string", "required"=false,  "description"="Work description"},
     *      {"name"="workTime", "dataType"="string", "required"=false,  "description"="Work time"},
     *      {"name"="tag", "dataType"="array", "required"=false,  "description"="Tag titles array: [tag1, tag2]"},
     *      {"name"="assigned", "dataType"="array", "required"=false,  "description"="UserId - assigner and StatusId collection: [userId => 12, statusId => 5]"},
     *      {"name"="attachment", "dataType"="array", "required"=false,  "description"="Attachment slugs array: [slug1, slug2]"},
     *      {"name"="taskData", "dataType"="array", "required"=false,  "description"="Tasks additional attributes array: [taskAttributeId => value, taskAttributeId2 => values]. Format: $json array - http://php.net/manual/en/function.json-decode.php"},
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
     *      200 ="The entity was successfully updated",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $taskId
     * @param Request $request
     *
     * @param bool|int $projectId
     * @param bool|int $statusId
     * @param bool|int $requesterId
     * @param bool|int $companyId
     * @return Response
     * @throws \LogicException
     */
    public function updateAction(int $taskId, Request $request, $projectId = false, $statusId = false, $requesterId = false, $companyId = false): Response
    {
        // JSON API Response - Content type and Location settings
        if ($requesterId && $companyId && $statusId && $projectId) {
            $locationURL = $this->generateUrl('tasks_update_project_status_requester_company', ['taskId' => $taskId, 'projectId' => $projectId, 'statusId' => $statusId, 'requesterId' => $requesterId, 'companyId' => $companyId]);
        } elseif ($projectId && !$companyId && !$statusId && !$requesterId) {
            $locationURL = $this->generateUrl('tasks_update_project', ['taskId' => $taskId, 'projectId' => $projectId]);
        } elseif ($projectId && $statusId && !$companyId && !$requesterId) {
            $locationURL = $this->generateUrl('tasks_update_project_status', ['taskId' => $taskId, 'projectId' => $projectId, 'statusId' => $statusId]);
        } elseif ($projectId && $statusId && $requesterId && !$companyId) {
            $locationURL = $this->generateUrl('tasks_update_project_status_requester', ['taskId' => $taskId, 'projectId' => $projectId, 'statusId' => $statusId, 'requesterId' => $requesterId]);
        } elseif ($statusId && !$companyId && !$projectId && !$requesterId) {
            $locationURL = $this->generateUrl('tasks_update_status', ['taskId' => $taskId, 'statusId' => $statusId]);
        } elseif ($statusId && $requesterId && !$companyId && !$projectId) {
            $locationURL = $this->generateUrl('tasks_update_status_requester', ['taskId' => $taskId, 'statusId' => $statusId, 'requesterId' => $requesterId]);
        } elseif ($statusId && $requesterId && $companyId && !$projectId) {
            $locationURL = $this->generateUrl('tasks_update_status_requester_company', ['taskId' => $taskId, 'statusId' => $statusId, 'requesterId' => $requesterId, 'companyId' => $companyId]);
        } elseif ($requesterId && !$companyId && !$statusId && !$projectId) {
            $locationURL = $this->generateUrl('tasks_update_requester', ['taskId' => $taskId, 'requesterId' => $requesterId]);
        } elseif ($requesterId && $companyId && !$statusId && !$projectId) {
            $locationURL = $this->generateUrl('tasks_update_requester_company', ['taskId' => $taskId, 'requesterId' => $requesterId, 'companyId' => $companyId]);
        } elseif ($companyId && !$projectId && !$statusId && !$requesterId) {
            $locationURL = $this->generateUrl('tasks_update_company', ['taskId' => $taskId, 'companyId' => $companyId]);
        } else {
            $locationURL = $this->generateUrl('tasks_update', ['taskId' => $taskId]);
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);
        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        //Decode sent parameters
        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        $changedParams = [];

        if ($projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
            if (!$project instanceof Project) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
                return $response;
            } else {
                // Check if user can create task in a selected project
                if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK_IN_PROJECT, $project)) {
                    $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Permission denied! You can not create task in a selected project!']));
                    return $response;
                }
                $oldProject = $task->getProject();
                $oldParam = $oldProject->getTitle();
                $newParam = $project->getTitle();
                $task->setProject($project);

                //Notification
                if ($this->paramsAreDifferent($oldParam, $newParam)) {
                    $changedParams['project'] = $this->setChangedParams($oldParam, $newParam);
                }

                // Delete all assigners from the old project
                if ($oldProject->getId() !== $project->getId()) {
                    $assigners = $task->getTaskHasAssignedUsers();

                    $oldParams = $this->createArrayOfUsernames($assigners);
                    $newParams = [];
                    if (\count($assigners) > 0) {
                        foreach ($assigners as $assigner) {
                            $this->getDoctrine()->getManager()->remove($assigner);
                        }
                        $this->getDoctrine()->getManager()->flush();
                    }
                    //Notification
                    if ($this->arraysAreDifferent($oldParams, $newParams)) {
                        $changedParams['assigner'] = $this->setChangedParams(implode(',', $oldParams), implode(',', $newParams));
                    }
                }
            }
        }

        if ($statusId) {
            $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($statusId);
            if (!$status instanceof Status) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Status with requested Id does not exist!']));
                return $response;
            } else {
                // Check Status function. If it is CLOSED, closedAt and startedAT params are required.
                // Only Task with a COMPANY can be closed!
                // If it IS IN PROGRESS or COMPLETED, startedAt param has to be set
                if ($status->getFunction() === StatusFunctionOptions::CLOSED_TASK) {
                    if (null === $task->getCompany()) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'Company is required for tasks with a CLOSED Status!']));
                        return $response;
                    }

                    if (!isset($requestBody['closedAt'])) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'ClosedAt param is required for a tasks with CLOSED Status!']));
                        return $response;
                    }
                } elseif ($status->getFunction() === StatusFunctionOptions::IN_PROGRESS_TASK || $status->getFunction() === StatusFunctionOptions::COMPLETED_TASK) {
                    if (!$task->getStartedAt() && !isset($requestBody['startedAt'])) {
                        $task->setStartedAt(new \DateTime());
                    }
                }
                $oldParam = $task->getStatus()->getTitle();
                $newParam = $status->getTitle();
                $task->setStatus($status);

                //Notification
                if ($this->paramsAreDifferent($oldParam, $newParam)) {
                    $changedParams['status'] = $this->setChangedParams($oldParam, $newParam);
                }
            }
        } else {
            $status = $task->getStatus();
        }

        if ($requesterId) {
            $requester = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requesterId);
            if (!$requester instanceof User) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Requester with requested Id does not exist!']));
                return $response;
            } else {
                $oldRequester = $task->getRequestedBy();
                if ($oldRequester->getDetailData()) {
                    $name = $oldRequester->getDetailData()->getName();
                    $surname = $oldRequester->getDetailData()->getSurname();
                    $detailData = ' (' . $name . ' ' . $surname . ')';
                } else {
                    $name = null;
                    $surname = null;
                    $detailData = '';
                }

                if ($requester->getDetailData()) {
                    $nameNew = $requester->getDetailData()->getName();
                    $surnameNew = $requester->getDetailData()->getSurname();
                    $detailDataNew = ' (' . $nameNew . ' ' . $surnameNew . ')';
                } else {
                    $nameNew = null;
                    $surnameNew = null;
                    $detailDataNew = '';
                }

                $oldParam = $oldRequester->getUsername() . $detailData;
                $newParam = $requester->getUsername() . $detailDataNew;
                $task->setRequestedBy($requester);

                //Notification
                if ($this->paramsAreDifferent($oldParam, $newParam)) {
                    $changedParams['requester'] = $this->setChangedParams($oldParam, $newParam);
                    $changedParams['requester']['fromEmail'] = $oldRequester->getEmail();
                    $changedParams['requester']['toEmail'] = $requester->getEmail();
                }
            }
        }

        if ($companyId) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($companyId);
            if (!$company instanceof Company) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Company with requested Id does not exist!']));
                return $response;
            } else {
                $oldParam = $task->getCompany()->getTitle();
                $newParam = $company->getTitle();
                $task->setCompany($company);

                //Notification
                if ($this->paramsAreDifferent($oldParam, $newParam)) {
                    $changedParams['company'] = $this->setChangedParams($oldParam, $newParam);
                }
            }
        }

        return $this->updateTask($task, $requestBody, $locationURL, $status, false, $changedParams);
    }

    /**
     * @ApiDoc(
     *  description="Delete Task entity",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="Processed object id."
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
     * @param int $taskId
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function deleteAction(int $taskId): Response
    {
        $locationURL = $this->generateUrl('tasks_delete', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);
        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::DELETE_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $this->getDoctrine()->getManager()->remove($task);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::DELETED_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::DELETED_MESSAGE]));
        return $response;
    }

    /**
     * @param Task $task
     * @param array $requestBody
     * @param $locationURL
     * @param Status $status
     * @param bool $create
     * @param bool|array $changedParams
     * @return Response
     * @throws \LogicException
     */
    private function updateTask(Task $task, array $requestBody, $locationURL, Status $status, $create = false, $changedParams = false): Response
    {
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);
        $statusCode = $this->getCreateUpdateStatusCode($create);

        // REQUIRED PARAMETERS
        if (isset($requestBody['title']) && \strlen($requestBody['title']) > 0) {
            $oldParam = $task->getTitle();
            $newParam = $requestBody['title'];
            $task->setTitle($newParam);

            //Notification
            if (!$create && $this->paramsAreDifferent($oldParam, $newParam)) {
                $changedParams['title'] = $this->setChangedParams($oldParam, $newParam);
            }
        } elseif ($create) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Tasks Title is required!']));
            return $response;
        }

        if (isset($requestBody['important'])) {
            $oldParam = $task->getImportant();
            $newParam = $requestBody['important'];
            $task->setImportant($newParam);

            //Notification
            if (!$create && $this->paramsAreDifferent($oldParam, $newParam)) {
                $changedParams['important'] = $this->setChangedParams($oldParam, $newParam);
            }
        } elseif ($create) {
            $task->setImportant(false);
        }

        // OPTIONAL PARAMETERS
        if (isset($requestBody['description'])) {
            $newParam = $requestBody['description'];
            if (\is_string($newParam) && \strlen($newParam) > 0) {
                $oldParam = $task->getDescription();
                $task->setDescription($requestBody['description']);

                //Notification
                if (!$create && $this->paramsAreDifferent($oldParam, $newParam)) {
                    $changedParams['description'] = $this->setChangedParams($oldParam, $newParam);
                }
            } else {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'Description param required type is TEXT!']));
                return $response;
            }
        }

        if (isset($requestBody['work'])) {
            $newParam = $requestBody['work'];
            if (\is_string($newParam) && \strlen($newParam) > 0) {
                $oldParam = $task->getWork();
                $task->setWork($requestBody['work']);

                //Notification
                if (!$create && $this->paramsAreDifferent($oldParam, $newParam)) {
                    $changedParams['work'] = $this->setChangedParams($oldParam, $newParam);
                }
            } else {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'Work param required type is STRING!']));
                return $response;
            }
        }

        if (isset($requestBody['workTime'])) {
            $newParam = $requestBody['workTime'];
            if (\is_string($newParam) && \strlen($newParam) > 0) {
                $oldParam = $task->getWorkTime();
                $task->setWorkTime($requestBody['workTime']);

                //Notification
                if (!$create && $this->paramsAreDifferent($oldParam, $newParam)) {
                    $changedParams['workTime'] = $this->setChangedParams($oldParam, $newParam);
                }
            } else {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'WorkTime param required type is STRING!']));
                return $response;
            }
        }

        if (isset($requestBody['startedAt'])) {
            $newParam = $requestBody ['startedAt'];
            $oldParam = $task->getStartedAt();
            if (null === $newParam || 'null' === $newParam || 0 == $newParam) {
                $task->setStartedAt(null);

                //Notification
                if (!$create && $this->paramsAreDifferent($oldParam, null)) {
                    $changedParams['startedAt'] = $this->setChangedParams($oldParam, null);
                }
            } else {
                $intDateData = (int)$newParam;
                try {
                    $startedAtDateTimeObject = new \DateTime("@$intDateData");
                    $task->setStartedAt($startedAtDateTimeObject);

                    //Notification
                    if (!$create && $this->paramsAreDifferent($oldParam, $intDateData)) {
                        $changedParams['startedAt'] = $this->setChangedParams($oldParam, $intDateData, true);
                    }
                } catch (\Exception $e) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'startedAt parameter is not in a valid format! Expected format: Timestamp']));
                    return $response;
                }
            }
        }

        if (isset($requestBody['deadline'])) {
            $newParam = $requestBody['deadline'];
            $oldParam = $task->getDeadline();
            if (null === $newParam || 'null' === $newParam || 0 == $newParam) {
                $task->setDeadline(null);

                //Notification
                if (!$create && $this->paramsAreDifferent($oldParam, null)) {
                    $changedParams['deadline'] = $this->setChangedParams($oldParam, null);
                }
            } else {
                $intDateData = (int)$newParam;
                try {
                    $deadlineDateTimeObject = new \Datetime("@$intDateData");
                    $task->setDeadline($deadlineDateTimeObject);

                    //Notification
                    if (!$create && $this->paramsAreDifferent($oldParam, $intDateData)) {
                        $changedParams['deadline'] = $this->setChangedParams($oldParam, $intDateData, true);
                    }
                } catch (\Exception $e) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'deadline parameter is not in a valid format! Expected format: Timestamp']));
                    return $response;
                }
            }
        }

        if (isset($requestBody['closedAt'])) {
            $newParam = $requestBody['closedAt'];
            $oldParam = $task->getClosedAt();
            if (null === $newParam || 'null' === $newParam || 0 == $newParam) {
                // Check if user changed Status from "closed" to another one
                if ($status->getFunction() === StatusFunctionOptions::CLOSED_TASK) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'If you want to delete a closure time, tasks STATUS can not be CLOSED']));
                    return $response;
                }
                $task->setClosedAt(null);
                //Notification
                if (!$create && $this->paramsAreDifferent($oldParam, null)) {
                    $changedParams['closedAt'] = $this->setChangedParams($oldParam, null);
                }
            } else {
                $intDateData = (int)$newParam;
                try {
                    $deadlineDateTimeObject = new \DateTime("@$intDateData");
                    // Check if user changed Status to "closed"
                    if ($status->getFunction() !== StatusFunctionOptions::CLOSED_TASK) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'If you want to set up a closure time, tasks STATUS has to be CLOSED']));
                        return $response;
                    }
                    $task->setClosedAt($deadlineDateTimeObject);
                    //Notification
                    if (!$create && $this->paramsAreDifferent($oldParam, $intDateData)) {
                        $changedParams['deadline'] = $this->setChangedParams($oldParam, $intDateData, true);
                    }
                } catch (\Exception $e) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'closedAt parameter is not in a valid format! Expected format: Timestamp']));
                    return $response;
                }
            }
        }
        $this->getDoctrine()->getManager()->persist($task);

        // OPTIONAL PARAMETERS - ANOTHER NEW ENTITY IS REQUIRED (tag, assigned, attachment, taskData)
        // Add tag(s) to the task
        if (isset($requestBody['tag'])) {
            //Delete old tags: sent body is actual, another data has to be removed
            $oldTags = $task->getTags();
            $oldParams = $this->createArrayOfTitles($oldTags);
            $newParams = [];
            if (\count($oldTags) > 0) {
                foreach ($oldTags as $oldTag) {
                    $task->removeTag($oldTag);
                    $this->getDoctrine()->getManager()->persist($task);
                }
                $this->getDoctrine()->getManager()->flush();
            }

            if (strtolower($requestBody['tag']) !== 'null') {
                $tagsArray = json_decode($requestBody['tag'], true);
                if (!\is_array($tagsArray)) {
                    $tagsArray = explode(',', $requestBody['tag']);
                }

                foreach ($tagsArray as $data) {
                    $tagTitle = $data;
                    $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
                        'title' => $data
                    ]);

                    if ($tag instanceof Tag) {
                        //Check if user can add tag to requested Task
                        $options = [
                            'task' => $task,
                            'tag' => $tag
                        ];

                        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TAG_TO_TASK, $options)) {
                            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                            $response = $response->setContent(json_encode(['message' => 'Tag with title: ' . $tagTitle . ' can not be added to the requested task!']));
                            return $response;
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
                        $tag->setColor('20B2AA');
                        $tag->setCreatedBy($this->getUser());

                        $this->getDoctrine()->getManager()->persist($tag);
                        $this->getDoctrine()->getManager()->flush();
                    }
                    //Add tag to task
                    $task->addTag($tag);
                    $this->getDoctrine()->getManager()->persist($task);
                }
                $newParams = $tagsArray;
            }
            //Notification
            if (!$create && $this->arraysAreDifferent($oldParams, $newParams)) {
                $changedParams['tags'] = $this->setChangedParams(implode(',', $oldParams), implode(',', $newParams));
            }
        }

        // Add assigner(s) to the task
        // $requestData['assigned'] = '[{"userId": 209, "statusId": 8}]';
        if (isset($requestBody['assigned'])) {
            //Delete old assigners: sent body is actual, another data has to be removed
            $oldAssigners = $task->getTaskHasAssignedUsers();
            $oldParams = $this->createArrayOfUsernames($oldAssigners);
            $oldAssignersEmails = $this->createArrayOfEmails($oldAssigners);
            $newParams = [];
            $newAssignersEmails = [];

            $this->getDoctrine()->getManager()->getConnection()->beginTransaction();
            try {
                if (\count($oldAssigners) > 0) {
                    foreach ($oldAssigners as $oldAssigner) {
                        $this->getDoctrine()->getManager()->remove($oldAssigner);
                    }
                    $this->getDoctrine()->getManager()->flush();
                }

                if (strtolower($requestBody['assigned']) !== "null") {
                    $assignedUsersArray = json_decode($requestBody['assigned'], true);
                    if (!\is_array($assignedUsersArray)) {
                        $assignedUsersArray = explode(',', $requestBody['assigned']);
                    }

                    foreach ($assignedUsersArray as $key => $value) {
                        if (isset($value['statusId'])) {
                            $assignedUserStatusId = $value['statusId'];
                            // STATUS
                            $assignerStatus = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($assignedUserStatusId);
                            if (!$assignerStatus instanceof Status) {
                                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                                $response = $response->setContent(json_encode(['message' => 'Assigners requested STATUS does not Exist!']));
                                return $response;
                            }
                        } else {
                            $assignerStatus = $status;
                        }

                        // USER
                        $assignedUserId = $value['userId'];
                        $assignedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($assignedUserId);
                        if (!$assignedUser instanceof User) {
                            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                            $response = $response->setContent(json_encode(['message' => 'Assigner with requested Id ' . $assignedUserId . ' does not exist!']));
                            return $response;
                        }
                        $newParams[] = $assignedUser->getUsername();
                        $newAssignersEmails[] = $assignedUser->getEmail();

                        // Check if user is already assigned to the task
                        $existedEntity = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy(
                            ['task' => $task, 'user' => $assignedUser]
                        );
                        if (!$existedEntity instanceof TaskHasAssignedUser) {
                            $userIsAssignedToTask = new TaskHasAssignedUser();
                        }

                        // Check if user can be assigned to the task
                        $options = [
                            'task' => $task,
                            'user' => $assignedUser
                        ];

                        if (!$this->get('task_voter')->isGranted(VoteOptions::ASSIGN_USER_TO_TASK, $options)) {
                            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                            $response = $response->setContent(json_encode(['message' => 'User with id: ' . $assignedUserId . 'has not permission to be assigned to requested task!']));
                            return $response;
                        }

                        $userIsAssignedToTask->setTask($task);
                        $userIsAssignedToTask->setStatus($assignerStatus);
                        $userIsAssignedToTask->setUser($assignedUser);
                        $userIsAssignedToTask->setActual(true);
                        $this->getDoctrine()->getManager()->persist($userIsAssignedToTask);

                        $task->addTaskHasAssignedUser($userIsAssignedToTask);
                        $this->getDoctrine()->getManager()->persist($task);
                    }
                    $this->getDoctrine()->getManager()->flush();
                    $this->getDoctrine()->getManager()->getConnection()->commit();
                }
                //Notification
                if (!$create && $this->arraysAreDifferent($oldParams, $newParams)) {
                    $changedParams['assigner'] = $this->setChangedParams(implode(',', $oldParams), implode(',', $newParams));
                    $changedParams['assigner']['emailFrom'] = $oldAssignersEmails;
                    $changedParams['assigner']['emailTo'] = $newAssignersEmails;
                }
            } catch (\Exception $exception) {
                $this->getDoctrine()->getManager()->getConnection()->rollBack();
                $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
                $response = $response->setContent(json_encode(['message' => 'Problem with Assigners! Data could not be saved to DB! Problem: ' . $exception]));
                return $response;
            }
        }

        // Add attachment(s) to the task
        if (isset($requestBody['attachment'])) {
            //Delete old attachments: sent body is actual, another data has to be removed
            $oldAttachments = $task->getTaskHasAttachments();
            $oldParams = $this->createArrayOfFileNames($oldAttachments);
            $newParams = [];
            if (\count($oldAttachments) > 0) {
                foreach ($oldAttachments as $oldAttachment) {
                    $this->getDoctrine()->getManager()->remove($oldAttachment);
                }
                $this->getDoctrine()->getManager()->flush();
            }

            if (strtolower($requestBody['attachment']) !== 'null') {
                $attachmentArray = json_decode($requestBody['attachment'], true);
                if (!\is_array($attachmentArray)) {
                    $attachmentArray = explode(',', $requestBody['attachment']);
                }

                foreach ($attachmentArray as $data) {
                    $fileEntity = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
                        'slug' => $data,
                    ]);

                    if (!$fileEntity instanceof File) {
                        $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                        $response = $response->setContent(json_encode(['message' => 'File with requested Slug does not exist in a DB!']));
                        return $response;
                    }

                    // Check if the File exists in a web-page file system
                    $uploadDir = $this->getParameter('upload_dir');
                    $file = $uploadDir . DIRECTORY_SEPARATOR . $fileEntity->getUploadDir() . DIRECTORY_SEPARATOR . $fileEntity->getTempName();

                    if (!file_exists($file)) {
                        $response = $response->setStatusCode(StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
                        $response = $response->setContent(json_encode(['message' => 'File with requested Slug does not exist in a web-page File System!']));
                        return $response;
                    }
                    $newParams[] = $fileEntity->getName();

                    $taskHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAttachment')->findOneBy([
                        'slug' => $data,
                        'task' => $task->getId()
                    ]);

                    if ($taskHasAttachment instanceof TaskHasAttachment) {
                        continue;
                    } else {
                        //Add attachment to the task
                        $taskHasAttachmentNew = new TaskHasAttachment();
                        $taskHasAttachmentNew->setTask($task);
                        $taskHasAttachmentNew->setSlug($data);
                        $task->addTaskHasAttachment($taskHasAttachmentNew);
                        $this->getDoctrine()->getManager()->persist($taskHasAttachmentNew);
                        $this->getDoctrine()->getManager()->persist($task);
                    }
                }
            }
            //Notification
            if (!$create && $this->arraysAreDifferent($oldParams, $newParams)) {
                $changedParams['attachment'] = $this->setChangedParams(implode(',', $oldParams), implode(',', $newParams));
            }
        }


        // Fill TaskData Entity if some of its parameters were sent
        // Check REQUIRED task attributes
        // Expected json objects: {"10": "value 1", "12": "value 2"}
        $allExistedTaskAttributes = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->findAll();
        $requiredTaskAttributeData = [];

        /** @var TaskAttribute|null $attr */
        foreach ($allExistedTaskAttributes as $attr) {
            if ($attr->getIsActive() && $attr->getRequired()) {
                $requiredTaskAttributeData[] = $attr->getId();
            }
        }

        if (isset($requestBody['taskData'])) {
            if (\is_array($requestBody['taskData'])) {
                $requestDetailData = $requestBody['taskData'];
            } else {
                $requestDetailData = json_decode($requestBody['taskData'], true);
            }
            if (!\is_array($requestDetailData)) {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'Problem with task additional data - not a correct format. Expected: "taskData":"{\"27\":\"INPUT+VALUE\",\"28\":\"text\"']));
                return $response;
            }

            $sentTaskAttributeKeys = [];
            /** @var array $requestDetailData */
            foreach ($requestDetailData as $key => $value) {
                $sentTaskAttributeKeys[] = $key;
                $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($key);

                if ($taskAttribute instanceof TaskAttribute) {
                    $taskData = $this->getDoctrine()->getRepository('APITaskBundle:TaskData')->findOneBy([
                        'taskAttribute' => $taskAttribute,
                        'task' => $task,
                    ]);


                    if (!$taskData instanceof TaskData) {
                        $taskData = new TaskData();
                        $taskData->setTask($task);
                        $taskData->setTaskAttribute($taskAttribute);
                        $oldParam = null;
                    } else {
                        $oldParam = $this->getValueBasedOnTaskAttributeType($taskData, $taskAttribute);
                    }
                    $isDate = $this->checkTaskAttributeDateType($taskAttribute);

                    // If value = 'null' is being sent and DataAttribute is not Required - data are deleted
                    if (!\is_array($value) && 'null' === strtolower($value) && !\in_array($key, $requiredTaskAttributeData, true)) {
                        $this->getDoctrine()->getManager()->remove($taskData);
                        $newParam = null;

                        //Notification
                        if (!$create && $this->paramsAreDifferent($oldParam, $newParam)) {
                            $changedParams[$taskAttribute->getTitle()] = $this->setChangedParams($oldParam, $newParam, $isDate);
                        }
                        continue;
                    }

                    if (!\is_array($value) && 'null' === strtolower($value) && \in_array($key, $requiredTaskAttributeData, true)) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'Task Data with a Task Attribute key: ' . $key . ' is Required! It is not possible to delete this data.']));
                        return $response;
                    }

                    $tdValueChecker = $this->get('entity_processor')->checkDataValueFormat($taskAttribute, $value);
                    if (true === $tdValueChecker) {
                        if ($taskAttribute->getType() === 'checkbox') {
                            if (\is_string($value)) {
                                $value = strtolower($value);
                            }
                            if ('true' === $value || '1' === $value || 1 === $value) {
                                $taskData->setBoolValue(true);
                                $newParam = true;
                            } else {
                                $taskData->setBoolValue(false);
                                $newParam = false;
                            }
                        } elseif ($taskAttribute->getType() === 'date') {
                            $intValue = (int)$value;
                            $taskData->setDateValue($intValue);
                            $newParam = $intValue;
                        } else {
                            $taskData->setValue($value);
                            $newParam = $value;
                        }
                        $task->addTaskDatum($taskData);

                        $this->getDoctrine()->getManager()->persist($taskAttribute);
                        $this->getDoctrine()->getManager()->persist($task);
                        $this->getDoctrine()->getManager()->persist($taskData);
                    } else {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $expectation = $this->get('entity_processor')->returnExpectedDataFormat($taskAttribute);
                        $response = $response->setContent(json_encode(['message' => 'Problem with task additional data (taskData) value format! For a Task Attribute with ID: ' . $taskAttribute->getId() . ', ' . $expectation . ' is/are expected.']));
                        return $response;
                    }
                    //Notification
                    if (!$create && $this->paramsAreDifferent($oldParam, $newParam)) {
                        $changedParams[$taskAttribute->getTitle()] = $this->setChangedParams($oldParam, $newParam, $isDate);
                    }

                } else {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'The key: ' . $key . ' of a Task Attribute is not valid (Task Attribute with this ID does not exist)']));
                    return $response;
                }
            }
            if ($create) {
                // Check if All required Task Attribute Data were sent
                $intersect = array_diff($requiredTaskAttributeData, $sentTaskAttributeKeys);
                if (\count($intersect) > 0) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Task Data with Task Attribute ID: ' . implode(',', $intersect) . ' are also required!']));
                    return $response;
                }
            }
        } elseif (\count($requiredTaskAttributeData) > 0 && $create) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task Data with Task Attribute ID: ' . implode(',', $requiredTaskAttributeData) . ' are required!']));
            return $response;
        }

        $this->getDoctrine()->getManager()->flush();
        $taskArray = $this->get('task_service')->getFullTaskEntity($task, true, $this->getUser(), $this->get('task_voter')->isAdmin());

        // Sent Notification Emails about a Task update to tasks: REQUESTER, ASSIGNED USERS, FOLLOWERS
        $processedNotifications = $this->processNotifications($this->getUser(), $task, $changedParams, $create);
        if ($processedNotifications['error']) {
            $taskArray = array_merge($taskArray, ['notification ERROR' => $processedNotifications['error']]);
        } else {
            $taskArray = array_merge($taskArray, ['sent notification EMAILS' => $processedNotifications['sentEmails']]);
        }

        $response = $response->setStatusCode($statusCode);
        $response = $response->setContent(json_encode($taskArray));
        return $response;
    }

    /**
     * @param User $loggedUser
     * @param Task $task
     * @param array $changedParams
     * @param bool $create
     * @return array
     */
    private function processNotifications(User $loggedUser, Task $task, array $changedParams, bool $create): array
    {
        $sentEmailsToGeneral = [];
        $sentEmailsToOldRequester = [];
        $sentEmailsToNewRequester = [];
        $sentEmailsToOldAssigner = [];
        $sentEmailsToNewAssigner = [];
        $sentEmailsToGeneralError = false;
        $sentEmailsToOldRequesterError = false;
        $sentEmailsToNewRequesterError = false;
        $sentEmailsToOldAssignerError = false;
        $sentEmailsToNewAssignerError = false;
        $error = false;

        if (\count($changedParams) > 0 && !$create) {
            $notificationEmailAddresses = $this->getEmailForUpdateTaskNotificationAndCreateNotifications($task, $loggedUser, $changedParams);

            $generalUpdate = $notificationEmailAddresses['generalUpdateAddresses'];
            if (\count($generalUpdate) > 0) {
                $stringifyChangedParams = $this->createStringFromDeepArray($changedParams);
                $templateParams = $this->getTemplateParams($task->getId(), $task->getTitle(), $generalUpdate, $this->getUser(), $stringifyChangedParams, 'taskUpdate.html.twig');
                $processedEmails = $this->get('email_service')->sendEmail($templateParams);
                $sentEmailsToGeneral = $processedEmails['sentEmails'];
                $sentEmailsToGeneralError = $processedEmails['error'];
            }

            $oldRequester[] = $notificationEmailAddresses['oldRequesterAddress'];
            if ($oldRequester[0]) {
                $templateParams = $this->getTemplateParams($task->getId(), $task->getTitle(), $oldRequester, $this->getUser(), $changedParams['requester']['from'], 'taskOldRequester.html.twig');
                $processedEmails = $this->get('email_service')->sendEmail($templateParams);
                $sentEmailsToOldRequester = $processedEmails['sentEmails'];
                $sentEmailsToOldRequesterError = $processedEmails['error'];
            }

            $newRequester[] = $notificationEmailAddresses['newRequesterAddress'];
            if ($newRequester[0]) {
                $templateParams = $this->getTemplateParams($task->getId(), $task->getTitle(), $newRequester, $this->getUser(), $changedParams['requester']['to'], 'taskNewRequester.html.twig');
                $processedEmails = $this->get('email_service')->sendEmail($templateParams);
                $sentEmailsToNewRequester = $processedEmails['sentEmails'];
                $sentEmailsToNewRequesterError = $processedEmails['error'];
            }

            $oldAssigner = $notificationEmailAddresses['oldAssignerAddresses'];
            if ($oldAssigner) {
                $templateParams = $this->getTemplateParams($task->getId(), $task->getTitle(), $oldAssigner, $this->getUser(), $changedParams['assigner']['from'], 'taskOldAssigner.html.twig');
                $processedEmails = $this->get('email_service')->sendEmail($templateParams);
                $sentEmailsToOldAssigner = $processedEmails['sentEmails'];
                $sentEmailsToOldAssignerError = $processedEmails['error'];
            }

            $newAssigner = $notificationEmailAddresses['newAssignerAddresses'];
            if ($newAssigner) {
                $templateParams = $this->getTemplateParams($task->getId(), $task->getTitle(), $newAssigner, $this->getUser(), $changedParams['assigner']['from'], 'taskNewAssigner.html.twig');
                $processedEmails = $this->get('email_service')->sendEmail($templateParams);
                $sentEmailsToNewAssigner = $processedEmails['sentEmails'];
                $sentEmailsToNewAssignerError = $processedEmails['error'];
            }
        }

        if ($sentEmailsToGeneralError || $sentEmailsToNewRequesterError || $sentEmailsToOldRequesterError || $sentEmailsToOldAssignerError || $sentEmailsToNewAssignerError) {
            $error = 'Problem with SMTP settings! Please contact admin!';
        }

        $allSentEmails = array_merge($sentEmailsToGeneral, $sentEmailsToOldRequester, $sentEmailsToNewRequester, $sentEmailsToOldAssigner, $sentEmailsToNewAssigner);
        return [
            'error' => $error,
            'sentEmails' => $allSentEmails
        ];

    }

    /**
     * @param Task $task
     * @param User $loggedUser
     * @param array $changedParams
     * @return array
     * @throws \LogicException
     */
    private function getEmailForUpdateTaskNotificationAndCreateNotifications(Task $task, User $loggedUser, array $changedParams): array
    {
        $notificationEmailAddresses = [];
        $oldRequesterEmailAddress = false;
        $newRequesterEmailAddress = false;
        $oldAssignerEmailAddresses = [];
        $newAssignerEmailAddresses = [];

        $createdNotifications = 0;
        $jsonFromChangedParams = json_encode($changedParams, true);
        $loggedUserEmail = $loggedUser->getEmail();

        if (isset($changedParams['requester'])) {
            $oldRequester = $changedParams['requester']['fromEmail'];
            $newRequester = $changedParams['requester']['toEmail'];

            if ($loggedUserEmail !== $oldRequester) {
                $oldRequesterEmailAddress = $oldRequester;
                /** @var User $oldRequesterUser */
                $oldRequesterUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->findOneBy([
                    'email' => $oldRequester
                ]);

                $notification = new Notification();
                $notification->setTask($task);
                $notification->setCreatedBy($loggedUser);
                $notification->setUser($oldRequesterUser);
                $notification->setChecked(false);
                $notification->setBody('Tasks requester was changed from you to ' . $changedParams['requester']['to'] . '.');
                $notification->setTitle('Task UPDATE');
                $this->getDoctrine()->getManager()->persist($notification);
                $createdNotifications++;
            }

            if ($loggedUserEmail !== $newRequester) {
                $newRequesterEmailAddress = $newRequester;
                /** @var User $newRequesterUser */
                $newRequesterUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->findOneBy([
                    'email' => $newRequester
                ]);

                $notification = new Notification();
                $notification->setTask($task);
                $notification->setCreatedBy($loggedUser);
                $notification->setUser($newRequesterUser);
                $notification->setChecked(false);
                $notification->setBody('Tasks requester was changed from ' . $changedParams['requester']['from'] . ' to you.');
                $notification->setTitle('Task UPDATE');
                $this->getDoctrine()->getManager()->persist($notification);
                $createdNotifications++;
            }
        }

        if (isset($changedParams['assigner'])) {
            $oldAssignersDifferentFromNew = array_diff($changedParams['assigner']['emailFrom'], $changedParams['assigner']['emailTo']);
            $newAssignersDifferentFromOld = array_diff($changedParams['assigner']['emailTo'], $changedParams['assigner']['emailFrom']);

            foreach ($oldAssignersDifferentFromNew as $assigner) {
                if ($assigner !== $loggedUserEmail && !\in_array($assigner, $oldAssignerEmailAddresses, true)) {
                    $oldAssignerEmailAddresses[] = $assigner;
                    /** @var User $oldAssignerUser */
                    $oldAssignerUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->findOneBy([
                        'email' => $assigner
                    ]);

                    $notification = new Notification();
                    $notification->setTask($task);
                    $notification->setCreatedBy($loggedUser);
                    $notification->setUser($oldAssignerUser);
                    $notification->setChecked(false);
                    $notification->setBody('Tasks assigner was changed from you to ' . $changedParams['assigner']['to'] . '.');
                    $notification->setTitle('Task UPDATE');
                    $this->getDoctrine()->getManager()->persist($notification);
                    $createdNotifications++;
                }
            }

            foreach ($newAssignersDifferentFromOld as $assigner) {
                if ($assigner !== $loggedUserEmail && !\in_array($assigner, $newAssignerEmailAddresses, true)) {
                    $newAssignerEmailAddresses[] = $assigner;
                    /** @var User $newAssignerUser */
                    $newAssignerUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->findOneBy([
                        'email' => $assigner
                    ]);

                    $notification = new Notification();
                    $notification->setTask($task);
                    $notification->setCreatedBy($loggedUser);
                    $notification->setUser($newAssignerUser);
                    $notification->setChecked(false);
                    $notification->setBody('Tasks assigner was changed from ' . $changedParams['assigner']['from'] . ' to you.');
                    $notification->setTitle('Task UPDATE');
                    $this->getDoctrine()->getManager()->persist($notification);
                    $createdNotifications++;
                }
            }
        }

        if (!isset($changedParams['requester'])) {
            $requesterEmail = $task->getRequestedBy()->getEmail();

            if ($loggedUserEmail !== $requesterEmail && !\in_array($requesterEmail, $notificationEmailAddresses, true) && !\in_array($requesterEmail, $newAssignerEmailAddresses, true) && !\in_array($requesterEmail, $oldAssignerEmailAddresses, true)) {
                $notificationEmailAddresses[] = $requesterEmail;

                $notification = new Notification();
                $notification->setTask($task);
                $notification->setCreatedBy($loggedUser);
                $notification->setUser($task->getRequestedBy());
                $notification->setChecked(false);
                $notification->setBody($jsonFromChangedParams);
                $notification->setTitle('Task UPDATE');
                $this->getDoctrine()->getManager()->persist($notification);
                $createdNotifications++;
            }
        }

        if (!isset($changedParams['assigner'])) {
            $assignedUsers = $task->getTaskHasAssignedUsers();
            if (\count($assignedUsers) > 0) {
                /** @var TaskHasAssignedUser $item */
                foreach ($assignedUsers as $item) {
                    $assignedUserEmail = $item->getUser()->getEmail();
                    if ($loggedUserEmail !== $assignedUserEmail && !\in_array($assignedUserEmail, $notificationEmailAddresses, true) && $oldRequesterEmailAddress !== $assignedUserEmail && $newRequesterEmailAddress !== $assignedUserEmail) {
                        $notificationEmailAddresses[] = $assignedUserEmail;

                        $notification = new Notification();
                        $notification->setTask($task);
                        $notification->setCreatedBy($loggedUser);
                        $notification->setUser($item->getUser());
                        $notification->setChecked(false);
                        $notification->setBody($jsonFromChangedParams);
                        $notification->setTitle('Task UPDATE');
                        $this->getDoctrine()->getManager()->persist($notification);
                        $createdNotifications++;
                    }
                }
            }
        }

//        $followers = $task->getFollowers();
//        if (count($followers) > 0) {
//            /** @var User $follower */
//            foreach ($followers as $follower) {
//                $followerEmail = $follower->getEmail();
//                if ($loggedUserEmail !== $followerEmail && !in_array($followerEmail, $notificationEmailAddresses)) {
//                    $notificationEmailAddresses[] = $followerEmail;
//                }
//            }
//        }

        if ($createdNotifications > 0) {
            $this->getDoctrine()->getManager()->flush();
        }

        return [
            'generalUpdateAddresses' => $notificationEmailAddresses,
            'oldRequesterAddress' => $oldRequesterEmailAddress,
            'newRequesterAddress' => $newRequesterEmailAddress,
            'oldAssignerAddresses' => $oldAssignerEmailAddresses,
            'newAssignerAddresses' => $newAssignerEmailAddresses
        ];
    }

    /**
     * @param int $taskId
     * @param string $title
     * @param array $emailAddresses
     * @param User $user
     * @param string $changedParams
     * @param string $twigTemplate
     * @return array
     */
    private function getTemplateParams(int $taskId, string $title, array $emailAddresses, User $user, string $changedParams, string $twigTemplate): array
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
            'changedParams' => $changedParams
        ];
        $params = [
            'subject' => 'LanHelpdesk - ' . '[#' . $taskId . '] ' . 'Úloha bola zmenená',
            'from' => $email,
            'to' => $emailAddresses,
            'body' => $this->renderView('@APITask/Emails/' . $twigTemplate, $templateParams)
        ];

        return $params;
    }

    /**
     * @param $params
     * @return string
     */
    private function createStringFromDeepArray($params): string
    {
        $array = [];

        foreach ($params as $key => $value) {
            $array[] = 'Parameter ' . $key . ' changed FROM ' . $value['from'] . ' TO ' . $value['to'];
        }

        return implode(',', $array);
    }

    /**
     * @param TaskData $taskData
     * @param TaskAttribute $taskAttribute
     * @return bool|\DateTime|int|string
     */
    private function getValueBasedOnTaskAttributeType(TaskData $taskData, TaskAttribute $taskAttribute)
    {
        if ($taskAttribute->getType() === 'checkbox') {
            $oldParam = $taskData->getBoolValue();
        } elseif ($taskAttribute->getType() === 'date') {
            $oldParam = $taskData->getDateValue();
        } else {
            $oldParam = $taskData->getValue();
        }

        return $oldParam;
    }

    /**
     * @param TaskAttribute $taskAttribute
     * @return bool
     */
    private function checkTaskAttributeDateType(TaskAttribute $taskAttribute): bool
    {
        if ($taskAttribute->getType() === 'date') {
            return true;
        }

        return false;
    }

    /**
     * @param $objects
     * @return array
     */
    private function createArrayOfTitles($objects): array
    {
        $array = [];

        foreach ($objects as $object) {
            $array[] = $object->getTitle();
        }

        return $array;
    }


    /**
     * @param $objects
     * @return array
     */
    private function createArrayOfEmails($objects): array
    {
        $array = [];

        foreach ($objects as $object) {
            $array[] = $object->getUser()->getEmail();
        }

        return $array;
    }

    /**
     * @param $objects
     * @return array
     */
    private function createArrayOfUsernames($objects): array
    {
        $array = [];

        foreach ($objects as $object) {
            $array[] = $object->getUser()->getUsername();
        }

        return $array;
    }

    /**
     * @param $objects
     * @return array
     */
    private function createArrayOfFileNames($objects): array
    {
        $array = [];

        foreach ($objects as $object) {
            $slug = $object->getSlug();
            $fileEntity = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
                'slug' => $slug,
            ]);

            if (!$fileEntity instanceof File) {
                continue;
            }
            $array[] = $fileEntity->getName();
        }

        return $array;
    }

    /**
     * @param array $oldParams
     * @param array $newParams
     * @return bool
     */
    private function arraysAreDifferent(array $oldParams, array $newParams): bool
    {
        $differentAreOld = array_diff($oldParams, $newParams);
        $differentAreNew = array_diff($newParams, $oldParams);

        if (count($differentAreNew) === 0 && count($differentAreOld) === 0) {
            return false;
        }

        return true;
    }

    /**
     * @param $oldParam
     * @param $newParam
     * @return bool
     */
    private function paramsAreDifferent($oldParam, $newParam): bool
    {
        if (true === $oldParam || false === $oldParam) {
            if ('1' === $newParam || 'true' === $newParam || true === $newParam) {
                $newParam = true;
            } elseif ('0' === $newParam || 'false' === $newParam || false === $newParam) {
                $newParam = false;
            } else {
                return false;
            }
        }

        if (null === $oldParam && (('null' === $newParam) || (null === $newParam) || ('NULL' === $newParam))) {
            return false;
        }

        if ($oldParam === $newParam) {
            return false;
        }

        return true;
    }

    /**
     * @param $oldParam
     * @param $newParam
     * @param $date
     * @return array|bool
     */
    private function setChangedParams($oldParam, $newParam, $date = false)
    {
        if (true === $oldParam || false === $oldParam) {
            if ('1' === $newParam || 'true' === $newParam || true === $newParam) {
                $newParam = true;
            } else {
                $newParam = false;
            }
        }

        if ($date && null !== $oldParam && null !== $newParam) {
            $oldParam = new \DateTime("@$oldParam");
            $newParam = new \DateTime("@$newParam");

            $oldParam = $oldParam->format('d-m-Y H:i:s');
            $newParam = $newParam->format('d-m-Y H:i:s');
        } elseif ($date && null === $oldParam && null !== $newParam) {
            $newParam = new \DateTime("@$newParam");
            $newParam = $newParam->format('d-m-Y H:i:s');
        } elseif ($date && null !== $oldParam && null === $newParam) {
            $oldParam = new \DateTime("@$oldParam");
            $oldParam = $oldParam->format('d-m-Y H:i:s');
        }


        return [
            'from' => $oldParam,
            'to' => $newParam
        ];

    }

    /**
     * @param array $requestBody
     * @return array
     * @throws \LogicException
     */
    private function getFilterData(array $requestBody): array
    {
        $data = [];

        if (isset($requestBody['search'])) {
            $data[FilterAttributeOptions::SEARCH] = $requestBody['search'];
        }
        if (isset($requestBody['status'])) {
            $data[FilterAttributeOptions::STATUS] = $requestBody['status'];
        }
        if (isset($requestBody['project'])) {
            $data[FilterAttributeOptions::PROJECT] = $requestBody['project'];
        }
        if (isset($requestBody['creator'])) {
            $data[FilterAttributeOptions::CREATOR] = $requestBody['creator'];
        }
        if (isset($requestBody['requester'])) {
            $data[FilterAttributeOptions::REQUESTER] = $requestBody['requester'];
        }
        if (isset($requestBody['company'])) {
            $data[FilterAttributeOptions::COMPANY] = $requestBody['company'];
        }
        if (isset($requestBody['assigned'])) {
            $data[FilterAttributeOptions::ASSIGNED] = $requestBody['assigned'];
        }
        if (isset($requestBody['tag'])) {
            $data[FilterAttributeOptions::TAG] = $requestBody['tag'];
        }
        if (isset($requestBody['follower'])) {
            $data[FilterAttributeOptions::FOLLOWER] = $requestBody['follower'];
        }
        if (isset($requestBody['createdTime'])) {
            $data[FilterAttributeOptions::CREATED] = $requestBody['createdTime'];
        }
        if (isset($requestBody['startedTime'])) {
            $data[FilterAttributeOptions::STARTED] = $requestBody['startedTime'];
        }
        if (isset($requestBody['deadlineTime'])) {
            $data[FilterAttributeOptions::DEADLINE] = $requestBody['deadlineTime'];
        }
        if (isset($requestBody['closedTime'])) {
            $data[FilterAttributeOptions::CLOSED] = $requestBody['closedTime'];
        }
        if (isset($requestBody['archived'])) {
            if ('true' === strtolower($requestBody['archived'])) {
                $data[FilterAttributeOptions::ARCHIVED] = true;
            }
        }
        if (isset($requestBody['important']) && 'true' === strtolower($requestBody['important'])) {
            $data[FilterAttributeOptions::IMPORTANT] = true;
        }
        if (isset($requestBody['addedParameters'])) {
            $data[FilterAttributeOptions::ADDED_PARAMETERS] = $requestBody['addedParameters'];
        }

        return $this->processFilterData($data);
    }

    /**
     * @param array $filterDataArray
     *
     * @return array|null
     * @throws \LogicException
     */
    private function getFilterDataFromSavedFilterArray($filterDataArray): array
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
     * @param array|null $data
     *
     * @return array
     * @throws \LogicException
     */
    private function processFilterData($data): array
    {
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
            $status = $data[FilterAttributeOptions::STATUS];
            if (!\is_array($status)) {
                $inFilter['taskGlobalStatus.id'] = explode(',', $status);
                $filterForUrl['status'] = '&status=' . $status;
            } else {
                $inFilter['taskGlobalStatus.id'] = $status;
                $filterForUrl['status'] = '&status=' . implode(',', $status);
            }
        }
        if (isset($data[FilterAttributeOptions::PROJECT])) {
            $project = $data[FilterAttributeOptions::PROJECT];
            if (!\is_array($project)) {
                if ('not' === strtolower($project)) {
                    $isNullFilter[] = 'task.project';
                } elseif ('current-user' === strtolower($project)) {
                    $equalFilter['projectCreator.id'] = $this->getUser()->getId();
                } else {
                    $inFilter['project.id'] = explode(',', $project);
                }
                $filterForUrl['project'] = '&project=' . $project;
            } else {
                $inFilter['project.id'] = $project;
                $filterForUrl['project'] = '&project=' . implode(',', $project);
            }
        }
        if (isset($data[FilterAttributeOptions::CREATOR])) {
            $creator = $data[FilterAttributeOptions::CREATOR];
            if (!\is_array($creator)) {
                if ('current-user' === strtolower($creator)) {
                    $equalFilter['createdBy.id'] = $this->getUser()->getId();
                } else {
                    $inFilter['createdBy.id'] = explode(',', $creator);
                }
                $filterForUrl['createdBy'] = '&creator=' . $creator;
            } else {
                $inFilter['createdBy.id'] = $creator;
                $filterForUrl['createdBy'] = '&creator=' . implode(',', $creator);
            }
        }
        if (isset($data[FilterAttributeOptions::REQUESTER])) {
            $requester = $data[FilterAttributeOptions::REQUESTER];
            if (!\is_array($requester)) {
                if ('current-user' === strtolower($requester)) {
                    $equalFilter['requestedBy.id'] = $this->getUser()->getId();
                } else {
                    $inFilter['requestedBy.id'] = explode(',', $requester);
                }
                $filterForUrl['requestedBy'] = '&requester=' . $requester;
            } else {
                $inFilter['requestedBy.id'] = $requester;
                $filterForUrl['requestedBy'] = '&requester=' . implode(',', $requester);
            }
        }
        if (isset($data[FilterAttributeOptions::COMPANY])) {
            $company = $data[FilterAttributeOptions::COMPANY];
            if (!\is_array($company)) {
                if ('current-user' === strtolower($company)) {
                    $equalFilter['taskCompany.id'] = $this->getUser()->getCompany()->getId();
                } else {
                    $inFilter['taskCompany.id'] = explode(',', $company);
                }
                $filterForUrl['taskCompany'] = '&taskCompany=' . $company;
            } else {
                $inFilter['taskCompany.id'] = $company;
                $filterForUrl['taskCompany'] = '&taskCompany=' . implode(',', $company);
            }
        }
        if (isset($data[FilterAttributeOptions::ASSIGNED])) {
            $assigned = $data[FilterAttributeOptions::ASSIGNED];
            if (!\is_array($assigned)) {
                if ('not' === strtolower($assigned)) {
                    $isNullFilter[] = 'taskHasAssignedUsers.user';
                } elseif ('current-user' === strtolower($assigned)) {
                    $equalFilter['assignedUser.id'] = $this->getUser()->getId();
                } elseif (\is_array(explode(',', $assigned))) {
                    $assignedArray = explode(',', $assigned);
                    if (\in_array('not', $assignedArray, true) && \in_array('current-user', $assignedArray, true)) {
                        $notAndCurrentFilter[] = [
                            'not' => 'taskHasAssignedUsers.user',
                            'equal' => [
                                'key' => 'assignedUser.id',
                                'value' => $this->getUser()->getId(),
                            ],
                        ];
                    }
                } else {
                    $inFilter['assignedUser.id'] = explode(',', $assigned);
                }

                $filterForUrl['assigned'] = '&assigned=' . $assigned;
            } else {
                if (\in_array('not', $assigned, true) && \in_array('current-user', $assigned, true)) {
                    $notAndCurrentFilter[] = [
                        'not' => 'taskHasAssignedUsers.user',
                        'equal' => [
                            'key' => 'assignedUser.id',
                            'value' => $this->getUser()->getId(),
                        ],
                    ];
                } else {
                    $inFilter['assignedUser.id'] = $assigned;
                }
                $filterForUrl['assigned'] = '&assigned=' . implode(',', $assigned);
            }
        }
        if (isset($data[FilterAttributeOptions::TAG])) {
            $tag = $data[FilterAttributeOptions::TAG];
            if (!\is_array($tag)) {
                $inFilter['tags.id'] = explode(',', $tag);
                $filterForUrl['tag'] = '&tag=' . $tag;
            } else {
                $inFilter['tags.id'] = $tag;
                $filterForUrl['tag'] = '&tag=' . implode(',', $tag);
            }
        }
        if (isset($data[FilterAttributeOptions::FOLLOWER])) {
            $follower = $data[FilterAttributeOptions::FOLLOWER];
            if (!\is_array($follower)) {
                if ('current-user' === $follower) {
                    $equalFilter['followers.id'] = $this->getUser()->getId();
                } else {
                    $inFilter['followers.id'] = explode(',', $follower);
                }
                $filterForUrl['followers'] = '&follower=' . $follower;
            } else {
                $inFilter['followers.id'] = $follower;
                $filterForUrl['followers'] = '&follower=' . implode(',', $follower);
            }
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
            if ('true' === strtolower($data[FilterAttributeOptions::ARCHIVED]) || true === $data[FilterAttributeOptions::ARCHIVED]) {
                $equalFilter['project.is_active'] = 0;
                $filterForUrl['archived'] = '&archived=TRUE';
            }
        }
        if (isset($data[FilterAttributeOptions::IMPORTANT])) {
            if ('true' === strtolower($data[FilterAttributeOptions::IMPORTANT]) || true === $data[FilterAttributeOptions::IMPORTANT]) {
                $equalFilter['task.important'] = 1;
                $filterForUrl['important'] = '&important=TRUE';
            }
        }
        if (isset($data[FilterAttributeOptions::ADDED_PARAMETERS])) {
            $addedParameters = $data[FilterAttributeOptions::ADDED_PARAMETERS];

            if(!\is_array($addedParameters)) {
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

                            if (VariableHelper::CHECKBOX === $typeOfTaskAttribute) {
                                if ('true' === strtolower($strpos[1])) {
                                    $equalFilterAddedParams[$attributeId] = 1;
                                } elseif ('false' === strtolower($strpos[1])) {
                                    $equalFilterAddedParams[$attributeId] = 0;
                                }
                            } elseif (VariableHelper::DATE === $typeOfTaskAttribute) {
                                $dateData = $this->separateFromToDateData($strpos[1], ':');
                                $dateFilterAddedParams[$attributeId] = $dateData;
                            } else {
                                $attributeValues = explode(',', $strpos[1]);
                                $inFilterAddedParams[$attributeId] = $attributeValues;
                            }
                        }
                    }
                }
            }else{
                foreach ($addedParameters as $key => $value){
                    // Check if TaskAttribute exists, select filter type based on it's TYPE
                    $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($key);
                    if ($taskAttribute instanceof TaskAttribute) {
                        $typeOfTaskAttribute = $taskAttribute->getType();
                        if (VariableHelper::CHECKBOX === $typeOfTaskAttribute) {
                            if ('true' === $value || true === $value) {
                                $equalFilterAddedParams[$key] = 1;
                            } elseif ('false' === $value || false === $value) {
                                $equalFilterAddedParams[$key] = 0;
                            }
                        } elseif (VariableHelper::DATE === $typeOfTaskAttribute) {
                            $dateData = $this->separateFromToDateData($value, ':');
                            $dateFilterAddedParams[$key] = $dateData;
                        } else {
                            $inFilterAddedParams[$key] = $value;
                        }
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
     * @param string $separator
     * @return array
     */
    private function separateFromToDateData(string $created, $separator = '='): array
    {
        $fromPosition = strpos($created, 'FROM' . $separator);
        $toPosition = strpos($created, 'TO' . $separator);

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
     * @param string|null $orderString
     * @return array|Response
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
                    $message = 'Requested filter parameter ' . $orderArrayKeyValue[0] . ' is not allowed!';
                    return [
                        'correct' => false,
                        'message' => $message
                    ];
                }
                $orderArrayKeyValueLowwer = strtolower($orderArrayKeyValue[1]);
                if (!($orderArrayKeyValueLowwer === 'asc' || $orderArrayKeyValueLowwer === 'desc')) {
                    $message = $orderArrayKeyValue[1] . ' Is not allowed! You can order data only ASC or DESC!';
                    return [
                        'correct' => false,
                        'message' => $message
                    ];
                }
                $order[$orderArrayKeyValue[0]] = $orderArrayKeyValue[1];
            }
        }
        if (\count($order) === 0) {
            $order[FilterAttributeOptions::ID] = 'DESC';
        }
        return [
            'correct' => true,
            'message' => $order
        ];
    }

    /**
     * @param array $requestData
     * @param array $changedParams
     * @return array
     */
    private function getChangedParams(array &$requestData, array &$changedParams): array
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
