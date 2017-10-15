<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Security\VoteOptions;
use API\TaskBundle\Services\FilterAttributeOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FilterController
 *
 * @package API\TaskBundle\Controller
 */
class FilterController extends ApiBaseController implements ControllerInterface
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
     *  description="Returns a list of Logged user's Filters",
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
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request)
    {
        $pageNum = $request->get('page');
        $pageNum = (int)$pageNum;
        $page = ($pageNum === 0) ? 1 : $pageNum;

        $limitNum = $request->get('limit');
        $limit = (int)$limitNum ? (int)$limitNum : 10;

        $orderString = $request->get('order');
        $orderString = strtolower($orderString);
        $order = ($orderString === 'asc' || $orderString === 'desc') ? $orderString : 'ASC';

        $isActive = $request->get('isActive');
        $public = $request->get('public');
        $report = $request->get('report');
        $project = $request->get('project');

        $filtersForUrl = [];
        if (null !== $public) {
            $filtersForUrl['public'] = '&public=' . $public;
        }
        if (null !== $isActive) {
            $filtersForUrl['isActive'] = '&isActive=' . $isActive;
        }
        if (null !== $report) {
            $filtersForUrl['isActive'] = '&report=' . $report;
        }
        if (null !== $project) {
            $filtersForUrl['project'] = '&project=' . $project;
        }

        $options = [
            'loggedUserId' => $this->getUser()->getId(),
            'isActive' => strtolower($isActive),
            'public' => strtolower($public),
            'report' => strtolower($report),
            'project' => strtolower($project),
            'order' => $order,
            'filtersForUrl' => array_merge($filtersForUrl, ['order' => '&order=' . $order]),
            'limit' => $limit
        ];

        return $this->json($this->get('filter_service')->getFiltersResponse($page, $options), StatusCodesHelper::SUCCESSFUL_CODE);
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
     *             ]
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
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);

        if (!$filter instanceof Filter) {
            return $this->notFoundResponse();
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::SHOW_FILTER, $filter)) {
            return $this->accessDeniedResponse();
        }

        $filterArray = $this->get('filter_service')->getFilterResponse($id);
        return $this->json($filterArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *             ]
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
     *  description="Create a new Filter Entity.
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
    public function createAction(Request $request)
    {
        $filter = new Filter();
        $requestData = $request->request->all();
        // Check if user can create PUBLIC filter (it's role has SHARE_FILTER ACL)
        if (isset($requestData['public'])) {
            if (true === $requestData['public'] || 1 === (int)$requestData['public']) {
                $aclOptions = [
                    'acl' => UserRoleAclOptions::SHARE_FILTERS,
                    'user' => $this->getUser()
                ];

                if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                    $filter->setPublic(false);
                    return $this->createApiResponse([
                        'message' => 'You have not permission to create a PUBLIC filter!',
                    ], StatusCodesHelper::ACCESS_DENIED_CODE);
                } else {
                    $filter->setPublic(true);
                }
                unset($requestData['public']);
            } else {
                $filter->setPublic(false);
            }
        }

        $filter->setIsActive(true);
        $filter->setCreatedBy($this->getUser());

        return $this->updateEntity($filter, $requestData, true);
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
     *             ]
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
     *             ]
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
     *  description="Update the Filter Entity",
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
     * @param Request $request
     * @return Response
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function updateAction(int $id, Request $request)
    {
        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        $requestData = $request->request->all();

        if (!$filter instanceof Filter) {
            return $this->createApiResponse([
                'message' => 'Filter with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::UPDATE_FILTER, $filter)) {
            return $this->accessDeniedResponse();
        }

        // Check if user can create PUBLIC filter (it's role has SHARE_FILTER ACL)
        if (isset($requestData['public']) && true === $requestData['public']) {
            $aclOptions = [
                'acl' => UserRoleAclOptions::SHARE_FILTERS,
                'user' => $this->getUser()
            ];

            if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                $filter->setPublic(false);
                return $this->createApiResponse([
                    'message' => 'You have not permission to create PUBLIC filter!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            } else {
                $filter->setPublic(true);
            }
            unset($requestData['public']);
        } else {
            $filter->setPublic(false);
        }

        return $this->updateEntity($filter, $requestData);
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
     *             ]
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
     *             ]
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
     *  description="Partially update the Filter Entity",
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
     * @param Request $request
     * @return Response
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        $requestData = $request->request->all();

        if (!$filter instanceof Filter) {
            return $this->notFoundResponse();
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::UPDATE_FILTER, $filter)) {
            return $this->accessDeniedResponse();
        }

        // Check if user can create PUBLIC filter (it's role has SHARE_FILTER ACL)
        if (isset($requestData['public']) && true === $requestData['public']) {
            $aclOptions = [
                'acl' => UserRoleAclOptions::SHARE_FILTERS,
                'user' => $this->getUser()
            ];

            if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                $filter->setPublic(false);
                return $this->createApiResponse([
                    'message' => 'You have not permission to create PUBLIC filter!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            } else {
                $filter->setPublic(true);
            }
            unset($requestData['public']);
        } else {
            $filter->setPublic(false);
        }

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
     *               "name": "Customer2",
     *               "surname": "Customerovic2"
     *            },
     *            {
     *               "id": 1015,
     *               "username": "manager",
     *               "name": "Customer2",
     *               "surname": "Customerovic2"
     *            },
     *         ],
     *         "created":
     *         [
     *            {
     *               "id": 1014,
     *               "username": "admin",
     *               "name": "Customer2",
     *               "surname": "Customerovic2"
     *            },
     *            {
     *               "id": 1015,
     *               "username": "manager",
     *               "name": "Customer2",
     *               "surname": "Customerovic2"
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
     *          "assigned":
     *          [
     *            {
     *               "id": 1014,
     *               "username": "admin",
     *               "name": "Customer2",
     *               "surname": "Customerovic2"
     *            }
     *          ]
     *      }
     * @ApiDoc(
     *  description="Get all options for filter: statuses, available projects, available creators, available requesters, available companies, available assigners, available tags",
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
     *  })
     *
     *
     * @return JsonResponse
     * @throws \LogicException
     */
    public function getFilterOptionsAction()
    {
        // Return arrays of options
        $statusesArray = $this->get('status_service')->getListOfExistedStatuses();

        // Available projects are where logged user have any ACL
        // Admin can use All existed projects
        $isAdmin = $this->get('task_voter')->isAdmin();
        $projectsArray = $this->get('project_service')->getListOfAvailableProjectsWhereUsersACLExists($this->getUser(), $isAdmin);

        $currentUser [] = [
            'id' => 'current-user',
            'username' => 'Current User'
        ];

        // Every user can be creator
        // The current-user option is added to the returned array
        $creatorArray = $this->get('api_user.service')->getListOfAllUsers();
        $creatorModifiedArray = array_merge($currentUser, $creatorArray);

        // Every user can be requester
        $requesterArray = $creatorModifiedArray;

        // Every company is available
        $companyArray = $this->get('api_company.service')->getListOfAllCompanies();

        // Public and logged user's tags are available
        $tagArray = $this->get('tag_service')->getListOfUsersTags($this->getUser()->getId());

        // Every user can have assigned tasks
        $assignArray = $creatorModifiedArray;

        $response = [
            'status' => $statusesArray,
            'project' => $projectsArray,
            'created' => $creatorModifiedArray,
            'requester' => $requesterArray,
            'company' => $companyArray,
            'tag' => $tagArray,
            'assigned' => $assignArray
        ];

        return $this->json($response, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param Filter $filter
     * @param array $data
     * @param bool $create
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     *
     * @return Response
     * @throws \LogicException
     */
    private function updateEntity(Filter $filter, array $data, $create = false)
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

        if (array_key_exists('_format', $data)) {
            unset($data['_format']);
        }

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedUnitEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for Tag Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        // Check if every key sent in filter array is allowed in FilterOptions
        if (isset($data['filter'])) {
            $filters = $data['filter'];
            if (!is_array($filters)) {
                $filtersArray = explode(',', $filters);
                $filtersExplodedArray = [];
                foreach ($filtersArray as $array) {
                    $keyValue = explode('=>', $array);
                    $filtersExplodedArray[$keyValue[0]] = $keyValue[1];
                }
                $filtersArray = $filtersExplodedArray;
            } else {
                $filtersArray = $filters;
            }

            foreach ($filtersArray as $key => $value) {
                if (!in_array($key, FilterAttributeOptions::getConstants(), true)) {
                    return $this->createApiResponse([
                        'message' => 'Requested filter parameter ' . $key . ' is not allowed!',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }

            $filter->setFilter($filtersArray);
            unset($data['filter']);
        }

        // Check if user can create REPORT Filter (it's role has to have ACL REPORT_FILTERS)
        if (isset($data['report']) && $data['report']) {
            $aclOptions = [
                'acl' => UserRoleAclOptions::REPORT_FILTERS,
                'user' => $this->getUser()
            ];

            if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                $filter->setReport(false);
                return $this->createApiResponse([
                    'message' => 'You have not permission to create REPORT filter!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            } else {
                $filter->setReport(true);
            }
            unset($data['report']);
        } else {
            $filter->setReport(false);
        }

        // Check id user want to set this filter like default
        if (!isset($data['default'])) {
            $data['default'] = false;
        }

        // Check if user set some Columns and if these columns are allowed (exists)
        if (isset($data['columns'])) {
            $dataColumnsArray = (!is_array($data['columns'])) ? explode(',', $data['columns']) : $data['columns'];

            foreach ($dataColumnsArray as $col) {
                if (!in_array($col, FilterAttributeOptions::getConstants(), true)) {
                    return $this->createApiResponse([
                        'message' => 'Requested column parameter ' . $col . ' is not allowed!',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }

            $filter->setColumns($dataColumnsArray);
            unset($data['columns']);
        }

        // Check if user set some Columns_task_attributes and if these columns are allowed (exists)
        if (isset($data['columns_task_attributes'])) {
            $dataColumnsArray = (!is_array($data['columns_task_attributes'])) ? explode(',', $data['columns_task_attributes']) : $data['columns_task_attributes'];

            foreach ($dataColumnsArray as $col) {
                $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($col);

                if (!$taskAttribute instanceof TaskAttribute) {
                    return $this->createApiResponse([
                        'message' => 'Requested task attribute with id ' . $col . ' does not exist!',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }

            $filter->setColumnsTaskAttributes($dataColumnsArray);
            unset($data['columns_task_attributes']);
        }


        $errors = $this->get('entity_processor')->processEntity($filter, $data);
        // Check manually if icon_class parameter is not NULL. If yes - return error
        $iconError = [];
        if (!isset($data['icon_class'])) {
            $iconError[] = [
                'field' => 'icon_class',
                'message' => 'Icon class is required!'
            ];
        }

        if (false === $errors && false !== $iconError) {
            $this->getDoctrine()->getManager()->persist($filter);
            $this->getDoctrine()->getManager()->flush();

            $filterArray = $this->get('filter_service')->getFilterResponse($filter->getId());
            return $this->json($filterArray, $statusCode);
        }

        $data = [
            'errors' => array_merge($errors, $iconError),
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
