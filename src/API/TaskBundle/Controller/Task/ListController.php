<?php

namespace API\TaskBundle\Controller\Task;

use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListController
 *
 * @package API\TaskBundle\Controller\Filter
 */
class ListController extends ApiBaseController
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
     *            "work_type": "servis IT",
     *            "createdAt": 1506434914,
     *            "updatedAt": 1506434914,
     *            "statusChange": 1531254165,
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
     *          not - just tasks without projects are returned,
     *          current-user - just tasks from actually logged user's projects are returned
     *          current-user,coma separated list od others IDs f.i. current-user,1,2,3,not"
     *     },
     *     {
     *       "name"="creator",
     *       "description"="A list of coma separated ID's of Creator f.i. 1,2,3
     *        Another option:
     *          current-user - just tasks created by actually logged user are returned.
     *          current-user,coma separated list od others IDs f.i. current-user,1,2,3"
     *     },
     *     {
     *       "name"="requester",
     *       "description"="A list of coma separated ID's of Creator f.i. 1,2,3
     *        Another option:
     *          current-user - just tasks created by actually logged user are returned.
     *          current-user,coma separated list od others IDs f.i. current-user,1,2,3"
     *     },
     *     {
     *       "name"="company",
     *       "description"="A list of coma separated ID's of Companies f.i. 1,2,3
     *        Another options:
     *          current-user - just tasks created by users with the same company like logged user are returned.
     *          current-user,coma separated list od others IDs f.i. current-user,1,2,3"
     *     },
     *     {
     *       "name"="assigned",
     *       "description"="A list of coma separated ID's of Users f.i. 1,2,3
     *        Another option:
     *          not - just tasks which aren't assigned to nobody are returned,
     *          current-user - just tasks assigned to actually logged user are returned
     *          current-user,coma separated list od others IDs f.i. current-user,1,2,3,not"
     *     },
     *     {
     *       "name"="tag",
     *       "description"="A list of coma separated ID's of Tags f.i. 1,2,3"
     *     },
     *     {
     *       "name"="follower",
     *       "description"="A list of coma separated ID's of Task Followers f.i. 1,2,3
     *        Another option:
     *          current-user - just tasks followed by actually logged user are returned
     *          current-user,coma separated list od others IDs f.i. current-user,1,2,3"
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
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function listAction(Request $request): Response
    {
        $locationURL = $this->generateUrl('tasks_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        if (false === $requestBody) {
            $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));

            return $response;
        }

        $processedOrderParam = $this->get('task_process_order_param_service')->processOrderParam($requestBody);
        if (false === $processedOrderParam['correct']) {
            $response->setContent(json_encode(['message' => $processedOrderParam['message']]))
                ->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);

            return $response;
        }

        $processedBasicFilterParams = $this->get('api_base.service')->processFilterParams($requestBody, true);
        $processedAdditionalFilterParams = $this->get('task_process_filter_param_service')->processFilterData($requestBody, $this->getUser());

        $options = $this->get('task_helper_service')->createOptionsForTasksArray($this->getUser(), $this->get('task_voter')->isAdmin(), $processedBasicFilterParams, $processedOrderParam, $processedAdditionalFilterParams);
        $tasksArray = $this->get('task_list_service')->getTasksResponse($options);
        $tasksModified = $this->addCanEditParamToEveryTask($tasksArray);

        $response->setContent(json_encode($tasksModified))
            ->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);

        return $response;
    }

}