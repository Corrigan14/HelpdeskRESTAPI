<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Security\FilterAttributeOptions;
use API\TaskBundle\Security\FilterColumnsOptions;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FilterController
 *
 * @package API\TaskBundle\Controller
 */
class FilterController extends ApiBaseController
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
     *             ],
     *             "remembered": false
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
     *             ],
     *             "remembered": false
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];
            $isActive = $processedFilterParams['isActive'];
            $public = $processedFilterParams['public'];
            $report = $processedFilterParams['report'];
            $project = $processedFilterParams['project'];
            $default = $processedFilterParams['default'];

            $filtersForUrl = [
                'isActive' => '&isActive=' . $isActive,
                'order' => '&order=' . $order,
                'public' => '&public=' . $public,
                'report' => '&report=' . $report,
                'project' => '&project=' . $project,
                'default' => '&default=' . $default
            ];

            $options = [
                'loggedUserId' => $this->getUser()->getId(),
                'isActive' => strtolower($isActive),
                'public' => strtolower($public),
                'report' => strtolower($report),
                'project' => strtolower($project),
                'default' => strtolower($default),
                'order' => $order,
                'filtersForUrl' => $filtersForUrl,
                'limit' => $limit
            ];

            $filtersArray = $this->get('filter_service')->getFiltersResponse($page, $options);
            $response = $response->setContent(json_encode($filtersArray));
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
     *         "data":
     *         {
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
     *             ],
     *             "remembered": false
     *         },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Filter entity",
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
     *  output="API\TaskBundle\Entity\Filter",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);

        if (!$filter instanceof Filter) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::SHOW_FILTER, $filter)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $filterArray = $this->get('filter_service')->getFilterResponse($id);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($filterArray));
        return $response;
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *         {
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
     *             ],
     *             "remembered": false
     *         },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Filter Entity.
     *  Filter field is expected to be an array with key = filter option, value = requested data id/val/... (look at task list filters)
     *  Allowed filter options are saved in FilterAttributeOptions file.",
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_create');

        $filter = new Filter();
        $filter->setIsActive(true);
        $filter->setCreatedBy($this->getUser());
        $filter->setUsersRemembered(false);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateEntity($filter, $requestBody, true, $locationURL);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *         {
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
     *             ],
     *             "remembered": false
     *         },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Update Filter Entity.
     *  Filter field is expected to be an array with key = filter option, value = requested data id/val/... (look at task list filters)
     *  Allowed filter options are saved in FilterAttributeOptions file.",
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updateAction(Request $request, int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_update');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        if (!$filter instanceof Filter) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::UPDATE_FILTER, $filter)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateEntity($filter, $requestBody, false, $locationURL);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *         {
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
     *             ],
     *             "remembered": true
     *         },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create/Update Filter For user which should be remembered after the Log off. The Entity is rewritten after the next save.
     *  Filter field is expected an array with key = filter option, value = requested data id/val/... (look at task list filters)
     *  Allowed filter options are saved in FilterAttributeOptions file.",
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function setUsersRememberedFilterAction(Request $request)
    {
        $loggedUser = $this->getUser();
        $requestData = $request->request->all();

        // Check if logged user already has its remembered filter. If no, create the new entity.
        $existedRememberedFilter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->findOneBy([
            'createdBy' => $loggedUser,
            'users_remembered' => true
        ]);

        if ($existedRememberedFilter instanceof Filter) {
            $filter = $existedRememberedFilter;
            $create = false;
        } else {
            $filter = new Filter();
            $filter->setIsActive(true);
            $filter->setCreatedBy($loggedUser);
            $filter->setUsersRemembered(true);
            $filter->setPublic(false);
            $create = true;
        }

        return $this->updateEntity($filter, $requestData, $create, true);
    }

    /**
     *  ### Response ###
     *      {
     *         "data":
     *         {
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
     *             ],
     *             "remembered": true
     *         },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns Logged User's Remembered Filter. If he does not have one, EMPTY value is returned.",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output="API\TaskBundle\Entity\Filter",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request"
     *  }
     * )
     *
     * @return bool|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function getUsersRememberedFilterAction()
    {
        $savedFilter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->findOneBy([
            'createdBy' => $this->getUser(),
            'users_remembered' => true
        ]);

        if ($savedFilter instanceof Filter) {
            $filterArray = $this->get('filter_service')->getFilterResponse($savedFilter->getId());
            return $this->json($filterArray, StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            return $this->json(null, StatusCodesHelper::SUCCESSFUL_CODE);
        }
    }

    /**
     * @ApiDoc(
     *  description="Delete Users remembered filter",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The Entity does not exist - no action was done",
     *      204 ="The Entity was successfully deleted",
     *      401 ="Unauthorized request"
     *  })
     *
     * @return Response
     * @throws \LogicException
     */
    public function resetUsersRememberedFilterAction()
    {
        $savedFilter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->findOneBy([
            'createdBy' => $this->getUser(),
            'users_remembered' => true
        ]);

        if ($savedFilter instanceof Filter) {
            $this->getDoctrine()->getManager()->remove($savedFilter);
            $this->getDoctrine()->getManager()->flush();
            return $this->json(null, StatusCodesHelper::DELETED_CODE);
        } else {
            return $this->json(null, StatusCodesHelper::SUCCESSFUL_CODE);
        }
    }


    /**
     * ### Response ###
     *      {
     *        "data":
     *         {
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
     *            "columns":
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
     *             ],
     *             "remembered": false
     *         },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Filter Entity for Project",
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $projectId
     * @return Response
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function createProjectsFilterAction(Request $request, int $projectId)
    {
        $filter = new Filter();
        $requestData = $request->request->all();

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
        if (!$project instanceof Project) {
            return $this->createApiResponse([
                'message' => 'Project with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can create PUBLIC filter (it's role has PROJECT_SHARED_FILTER ACL)
        if (isset($requestData['public']) && true === $requestData['public']) {
            $aclOptions = [
                'acl' => UserRoleAclOptions::PROJECT_SHARED_FILTERS,
                'user' => $this->getUser()
            ];

            if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                $filter->setPublic(false);
                return $this->createApiResponse([
                    'message' => 'You have not permission to create PUBLIC filters in Projects!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            } else {
                $filter->setPublic(true);
            }
            unset($requestData['public']);
        } else {
            $filter->setPublic(false);
        }

        $filter->setIsActive(true);
        $filter->setCreatedBy($this->getUser());
        $filter->setProject($project);

        return $this->updateEntity($filter, $requestData, true);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
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
     *            "columns":
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
     *             ],
     *             "remembered": false
     *         },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the projects Filter Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
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
     * @param int $projectId
     * @param Request $request
     * @return Response
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function updateProjectFilterAction(int $id, int $projectId, Request $request)
    {
        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        $requestData = $request->request->all();

        if (!$filter instanceof Filter) {
            return $this->createApiResponse([
                'message' => 'Filter with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
        if (!$project instanceof Project) {
            return $this->createApiResponse([
                'message' => 'Project with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'filter' => $filter,
            'project' => $project
        ];

        if (!$this->get('filter_voter')->isGranted(VoteOptions::UPDATE_PROJECT_FILTER, $options)) {
            return $this->accessDeniedResponse();
        }

        // Check if user can create PUBLIC filter (it's role has PROJECT_SHARED_FILTER ACL)
        if (isset($requestData['public']) && true === $requestData['public']) {
            $aclOptions = [
                'acl' => UserRoleAclOptions::PROJECT_SHARED_FILTERS,
                'user' => $this->getUser()
            ];

            if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                $filter->setPublic(false);
                return $this->createApiResponse([
                    'message' => 'You have not permission to create PUBLIC filters in Projects!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            } else {
                $filter->setPublic(true);
            }
            unset($requestData['public']);
        } else {
            $filter->setPublic(false);
        }

        $filter->setProject($project);

        return $this->updateEntity($filter, $requestData);
    }

    /**
     * @ApiDoc(
     *  description="Delete Filter Entity",
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
     * @return Response
     * @throws \LogicException
     */
    public function deleteAction(int $id)
    {
        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);

        if (!$filter instanceof Filter) {
            return $this->createApiResponse([
                'message' => 'Filter with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::DELETE_FILTER, $filter)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($filter);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param Filter $filter
     * @param array $data
     * @param bool $create
     * @param $locationUrl
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    private function updateEntity(Filter $filter, array $data, $create = false, $locationUrl)
    {
        $allowedUnitEntityParams = [
            'title',
            'public',
            'filter',
            'report',
            'is_active',
            'default',
            'icon_class',
            'order',
            'columns',
            'columns_task_attributes'
        ];

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);


        if (false !== $data) {
            if (array_key_exists('_format', $data)) {
                unset($data['_format']);
            }

            foreach ($data as $key => $value) {
                if (!in_array($key, $allowedUnitEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for a Filter Entity!']));
                    return $response;
                }
            }

            // Check if user can create PUBLIC filter (it's role has SHARE_FILTER ACL)
            if (isset($data['public']) && (true === $data['public'] || 'true' == $data['public'] || 1 === (int)$data['public'])) {
                $aclOptions = [
                    'acl' => UserRoleAclOptions::SHARE_FILTERS,
                    'user' => $this->getUser()
                ];

                if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                    $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                    $response = $response->setContent(json_encode(['message' => 'You have not permission to create a PUBLIC filter!']));
                    return $response;
                } else {
                    $filter->setPublic(true);
                }
                unset($data['public']);
            } elseif ($create) {
                $filter->setPublic(false);
            }

            // Check if user can create REPORT Filter (it's role has to have ACL REPORT_FILTERS)
            if (isset($data['report']) && (true === $data['report'] || 'true' == $data['report'] || 1 === (int)$data['report'])) {
                $aclOptions = [
                    'acl' => UserRoleAclOptions::REPORT_FILTERS,
                    'user' => $this->getUser()
                ];

                if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                    $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                    $response = $response->setContent(json_encode(['message' => 'You have not permission to create a REPORT filter!']));
                    return $response;
                } else {
                    $filter->setReport(true);
                }
                unset($data['report']);
            } elseif ($create) {
                $filter->setReport(false);
            }


            // Check if user want to set this filter like default
            if (isset($data['default']) && (true === $data['default'] || 'true' == $data['default'] || 1 === (int)$data['default'])) {
                $filter->setDefault(true);
            } elseif ($create) {
                $filter->setDefault(false);
            }

            // Check if every key sent in a filter array is allowed in FilterOptions and decode data correctly
            // Possilbe ways how to send Filter data:
            // 1. array [assigned => "210,211", taskCompany => "202"]
            // 2. json: e.g {"assigned":"210,211","taskCompany":"202"}
            // 3. string in a specific format: assigned=>210,taskCompany=>202
            if (isset($data['filter'])) {
                $filters = $data['filter'];
                if (!is_array($filters)) {
                    //Try Json decode or Array Exploding
                    $filtersArray = json_decode($filters, true);
                    // Not very nice to use the third post data option
                    if (!is_array($filtersArray)) {
                        $filtersArray = explode(',', $filters);
                        $filtersExplodedArray = [];
                        foreach ($filtersArray as $array) {
                            $keyValue = explode('=>', $array);
                            $filtersExplodedArray[$keyValue[0]] = $keyValue[1];
                        }
                        $filtersArray = $filtersExplodedArray;
                    }
                } else {
                    $filtersArray = $filters;
                }

                foreach ($filtersArray as $key => $value) {
                    if (!in_array($key, FilterAttributeOptions::getConstants(), true)) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'Requested filter parameter ' . $key . ' is not allowed!']));
                        return $response;
                    }
                }
                $filter->setFilter($filtersArray);
                unset($data['filter']);
            }

            // Check if user set some Columns and if these columns are allowed (exists)
            // The data format should be
            // 1. JSON ARRAY: ["title","status"]
            // 2. Arrray separated by ,: title, status
            if (isset($data['columns'])) {
                $dataColumnsArray = $data['columns'];
                if (!is_array($dataColumnsArray)) {
                    $dataColumnsArray = json_decode($data['columns'], true);
                    if (!is_array($dataColumnsArray)) {
                        $dataColumnsArray = explode(',', $data['columns']);
                    }
                }

                foreach ($dataColumnsArray as $col) {
                    if (!in_array($col, FilterColumnsOptions::getConstants(), true)) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'Requested column parameter ' . $col . ' is not allowed!']));
                        return $response;
                    }
                }

                $filter->setColumns($dataColumnsArray);
                unset($data['columns']);
            }

            // Check if user set some Columns and if these columns are allowed (exists)
            // The data format shoul be
            // 1. JSON ARRAY: ["title","status"]
            // 2. Arrray separated by ,: title, status
            // Check if user set some Columns_task_attributes and if these columns are allowed (exists)
            if (isset($data['columns_task_attributes'])) {
                $dataColumnsArray = $data['columns_task_attributes'];
                if (!is_array($dataColumnsArray)) {
                    $dataColumnsArray = json_decode($data['columns_task_attributes'], true);
                    if (!is_array($dataColumnsArray)) {
                        $dataColumnsArray = explode(',', $data['columns_task_attributes']);
                    }
                }

                foreach ($dataColumnsArray as $col) {
                    $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($col);

                    if (!$taskAttribute instanceof TaskAttribute) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' =>  'Requested task attribute with id ' . $col . ' does not exist!']));
                        return $response;
                    }
                }

                $filter->setColumnsTaskAttributes($dataColumnsArray);
                unset($data['columns_task_attributes']);
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);
            $errors = $this->get('entity_processor')->processEntity($filter, $data);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($filter);
                $this->getDoctrine()->getManager()->flush();

                $filterArray = $this->get('filter_service')->getFilterResponse($filter->getId());
                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($filterArray));
                return $response;
            } else {
                $data = [
                    'errors' => $errors,
                    'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
                ];
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode($data));
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;
    }
}
