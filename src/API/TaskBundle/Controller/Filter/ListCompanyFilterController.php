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
     *       "data":
     *       [
     *         {
     *            "company":
     *            {
     *               "id": 1802,
     *               "title": "Web-Solutions"
     *            },
     *            "work_hours": 20
     *          },
     *          {
     *            "company":
     *            {
     *               "id": 1803,
     *               "title": "LAN-Systems"
     *            },
     *            "work_hours": 35
     *          }
     *       ],
     *       "total": 22
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of COMPANIES with WORK HOURS. These companies include Tasks which fulfill conditions from the requested FILTER",
     *  requirements={
     *     {
     *       "name"="filterId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of filter"
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