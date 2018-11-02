<?php

namespace API\TaskBundle\Controller\Task;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListFromFilterController
 *
 * @package API\TaskBundle\Controller\Filter
 */
class ListFromFilterController extends ApiBaseController
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
     *            "work_time": null,,
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
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public function listAction(Request $request, int $filterId): Response
    {
        $locationURL = $this->generateUrl('tasks_list_saved_filter', ['filterId' => $filterId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($filterId);
        if (!$filter instanceof Filter) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));

            return $response;
        }

        // Check if logged user has permission to see the requested filter.
        // If the filter is REPORT  user's role needs a permission REPORT_FILTERS
        if (!$this->get('filter_voter')->isGranted(VoteOptions::SHOW_FILTER, $filter)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

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
        $processedAdditionalFilterParams = $this->get('task_process_filter_param_service')->processFilterData($requestBody, $this->getUser(), $filter->getFilter());
        
        $options = $this->get('task_helper_service')->createOptionsForTasksArray($this->getUser(), $this->get('task_voter')->isAdmin(), $processedBasicFilterParams, $processedOrderParam, $processedAdditionalFilterParams, $filter);
        $tasksArray = $this->get('task_list_service')->getList($options, $filter);
        $tasksModified = $this->addCanEditParamToEveryTask($tasksArray);

        $response->setContent(json_encode($tasksModified))
            ->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);

        return $response;
    }
}