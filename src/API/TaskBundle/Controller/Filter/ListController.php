<?php

namespace API\TaskBundle\Controller\Filter;

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
     *          {
     *             "id": 145,
     *             "title": 145,
     *             "public": true,
     *             "filter":
     *             {
     *                "status": "238,239",
     *                "assigned": "not,current-user"
     *             },
     *             "report": false,
     *             "is_active": true,
     *             "default": true,
     *             "icon_class": "&#xE88A;"
     *             "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             },
     *             "project":
     *             {
     *                "id": 2575,
     *                "title": "INBOX",
     *             },
     *             "columns":
     *             [
     *                "title",
     *                "creator",
     *                "company",
     *                "assigned",
     *                "createdTime",
     *                "deadlineTime",
     *                "status"
     *             ],
     *             "columns_task_attributes":
     *             [
     *                205,
     *                206
     *             ]
     *          },
     *          {
     *             "id": 146,
     *             "title": 146,
     *             "public": true,
     *             "filter":
     *             {
     *                "important": "TRUE",
     *                "assigned": "current-user"
     *             },
     *             "report": false,
     *             "is_active": true,
     *             "default": false,
     *             "icon_class": "&#xE88A;"
     *             "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             },
     *             "project": null,
     *             "columns":
     *             [
     *                "title",
     *                "creator",
     *                "company",
     *                "assigned",
     *                "createdTime",
     *                "deadlineTime",
     *                "status"
     *             ],
     *             "columns_task_attributes":
     *             [
     *                205,
     *                206
     *             ]
     *           },
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/filters?page=1",
     *           "first": "/api/v1/task-bundle/filters?page=1",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/filters?page=2",
     *            "last": "/api/v1/task-bundle/filters?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Logged user's Filters.",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by ORDER number of filter"
     *     },
     *     {
     *       "name"="public",
     *       "description"="Return's only PUBLIC filters if this param is TRUE, only logged user's filter's without
     *       PUBLIC filters, if param is FALSE. Else returns both: PUBLIC filters and filters created by logged user."
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE filters if this param is TRUE, only INACTIVE filters if param is FALSE"
     *     },
     *     {
     *       "name"="report",
     *       "description"="Return's only REPORT filters if this param is TRUE, only FILTER filters if param is FALSE"
     *     },
     *     {
     *       "name"="project",
     *       "description"="A list of coma separated ID's of Project f.i. 1,2,3.
     *        Another options:
     *          NOT - just filters without projects are returned,
     *          CURRENT-USER - just filters for actually logged user's projects are returned."
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Limit for Pagination: 999 - returns all entities, null - returns 10 entities"
     *     },
     *     {
     *       "name"="default",
     *       "description"="Returns only DEFAULT filters if this param is TRUE"
     *     },
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
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        $locationURL = $this->generateUrl('filter_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false === $requestBody) {
            $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));

            return $response;
        }

        $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);

        $filtersArray = $this->get('filter_get_service')->getFiltersResponse($this->getUser()->getId(), $processedFilterParams);
        $response->setContent(json_encode($filtersArray))
            ->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);

        return $response;
    }
}