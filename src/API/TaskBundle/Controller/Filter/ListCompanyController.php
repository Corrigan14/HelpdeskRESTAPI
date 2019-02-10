<?php

namespace API\TaskBundle\Controller\Filter;

use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListCompanyController
 *
 * @package API\TaskBundle\Controller\Filter
 */
class ListCompanyController extends ApiBaseController
{

    /**
     *  ### Response ###
     *     {
     *     "2":
     *         {
     *          "company_title": "Unassigned",
     *          "work_hours": 34,
     *          "number_of_tasks": 3048
     *         },
     *     "3":
     *         {
     *          "company_title": "LAN-SYSTEMS",
     *          "work_hours": 9,
     *          "number_of_tasks": 5
     *         },
     *     "4":
     *         {
     *          "company_title": "WEB-SOLUTIONS",
     *          "work_hours": 14,
     *          "number_of_tasks": 10
     *         }
     *      }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of COMPANIES with WORK HOURS and NUMBER OF TASKS related to the company. These companies include Tasks which fulfill conditions from the requested FILTER",
     *  filters={
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order chart. The list is ordered by TITLE of a company."
     *     },
     *     {
     *       "name"="report",
     *       "description"="If TRUE, the list is treated like REPORT filter"
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
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        $locationURL = $this->generateUrl('filter_company_list_without_saved_filter');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        if (false === $requestBody) {
            $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));

            return $response;
        }

        $processedOrderParam = $this->get('task_process_order_param_service')->processOrderParamForCompanyList($requestBody);
        $processedAdditionalFilterParams = $this->get('task_process_filter_param_service')->processFilterData($requestBody, $this->getUser());

        // Check if logged user has permission to see the requested filter.
        // If the filter is REPORT  user's role needs a permission REPORT_FILTERS
        if (!$this->get('filter_voter')->isGranted(VoteOptions::SHOW_COMPANY_REPORT_FILTER, $processedAdditionalFilterParams['isReport'])) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

        $options = $this->get('task_helper_service')->createOptionsForCompanyArray($processedAdditionalFilterParams, $processedOrderParam);
        $companyArray = $this->get('task_list_service')->getListOfCompaniesForCompanyReport($options);
        $response->setContent(json_encode($companyArray))
            ->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);

        return $response;
    }

}