<?php

namespace API\TaskBundle\Controller\Filter;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListCompanyFilterController
 *
 * @package API\TaskBundle\Controller\Filter
 */
class ListCompanyFilterController extends ApiBaseController
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
     * @ApiDoc(
     *  description="Returns a list of COMPANIES with WORK HOURS and NUMBER OF TASKS related to the company. These companies include Tasks which fulfill conditions from the requested FILTER",
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
     *       "name"="order",
     *       "description"="ASC or DESC order chart. The list is ordered by TITLE of a comapny."
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


        $processedOrderParam = $this->get('task_process_order_param_service')->processOrderParamForCompanyList($requestBody);
        $processedAdditionalFilterParams = $this->get('task_process_filter_param_service')->processFilterData($requestBody, $this->getUser(), $filter->getFilter());
        $options = $this->get('task_helper_service')->createOptionsForCompanyArray($processedAdditionalFilterParams, $processedOrderParam, $filter);

        $companyArray = $this->get('task_list_service')->getListOfCompaniesForCompanyReport($options);

        $response->setContent(json_encode($companyArray))
            ->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);

        return $response;
    }
}