<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
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
     *       "_links":
     *        {
     *          "update filter": "/api/v1/task-bundle/filters/15",
     *          "inactivate": "/api/v1/task-bundle/filters/15/inactivate",
     *          "restore": "/api/v1/task-bundle/filters/15/restore",
     *          "set logged users remembered filter": "/api/v1/task-bundle/filters/15/user-remembered",
     *          "get logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered",
     *          "remove logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered/delete"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create Filter Entity for a requested Project.
     *  Filter field is expected to be an array with a key = filter option, value = requested data id/val/... (look at task list filters).",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="Processed objects ID"
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
     *      201 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $projectId
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createProjectsFilterAction(Request $request, int $projectId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_project_create', ['projectId' => $projectId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        // User can create filter in a project if he has ANY permission to this project
        $options = [
            'project' => $project
        ];
        if (!$this->get('filter_voter')->isGranted(VoteOptions::CREATE_PROJECT_FILTER, $options)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }


        $filter = new Filter();
        $filter->setIsActive(true);
        $filter->setCreatedBy($this->getUser());
        $filter->setProject($project);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateEntity($filter, $requestBody, true, $locationURL, true);
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
     *       "_links":
     *        {
     *          "update filter": "/api/v1/task-bundle/filters/15",
     *          "inactivate": "/api/v1/task-bundle/filters/15/inactivate",
     *          "restore": "/api/v1/task-bundle/filters/15/restore",
     *          "set logged users remembered filter": "/api/v1/task-bundle/filters/15/user-remembered",
     *          "get logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered",
     *          "remove logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered/delete"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Update Filter Entity in a requested Project.",
     * requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="Projects ID"
     *     },
     *     {
     *       "name"="filterId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="Filters ID"
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
     * @param Request $request
     * @param int $projectId
     * @param int $filterId
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updateProjectFilterAction(int $filterId, int $projectId, Request $request)
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_project_update', ['projectId' => $projectId, 'filterId' => $filterId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($filterId);
        if (!$filter instanceof Filter) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));
            return $response;
        }

        $options = [
            'filter' => $filter,
            'project' => $project
        ];

        if (!$this->get('filter_voter')->isGranted(VoteOptions::UPDATE_PROJECT_FILTER, $options)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }


        $filter->setProject($project);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateEntity($filter, $requestBody, false, $locationURL, true);
    }

    /**
     * @ApiDoc(
     *  description="Inactivate Filter Entity",
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
     *      204 ="The Entity was successfully inactivated",
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
    public function inactivateAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_inactivate', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        if (!$filter instanceof Filter) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::DELETE_FILTER, $filter)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $filter->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($filter);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => 'is_active param of Entity was successfully changed to inactive: 0']));
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
     *             ]
     *         },
     *       "_links":
     *        {
     *          "update filter": "/api/v1/task-bundle/filters/15",
     *          "inactivate": "/api/v1/task-bundle/filters/15/inactivate",
     *          "restore": "/api/v1/task-bundle/filters/15/restore",
     *          "set logged users remembered filter": "/api/v1/task-bundle/filters/15/user-remembered",
     *          "get logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered",
     *          "remove logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered/delete"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Activate Filter Entity",
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
     *      404 ="Not found Entity"
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
    public function activateAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_restore', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        if (!$filter instanceof Filter) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::DELETE_FILTER, $filter)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $filter->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($filter);
        $this->getDoctrine()->getManager()->flush();

        $filterArray = $this->get('filter_service')->getFilterResponse($id);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($filterArray));
        return $response;
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
    public function deleteAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_inactivate', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        if (!$filter instanceof Filter) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::DELETE_FILTER, $filter)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $this->getDoctrine()->getManager()->remove($filter);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::DELETED_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::DELETED_MESSAGE]));
        return $response;
    }

    /**
     * @ApiDoc(
     *  description="Set Filter Entity as REMEMBERED for a logged user.",
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
     * @param $id
     * @return Response
     * @throws \LogicException
     */
    public function setUsersRememberedFilterAction($id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_set_user_remembered', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        if (!$filter instanceof Filter) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::SET_REMEMBERED_FILTER, $filter)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $loggedUser->setRememberedFilter($filter);
        $filter->addRememberUser($loggedUser);
        $this->getDoctrine()->getManager()->persist($loggedUser);
        $this->getDoctrine()->getManager()->persist($filter);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => 'Filter was successfully set as remembered']));
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
     *             ]
     *         },
     *        "_links":
     *        {
     *          "update filter": "/api/v1/task-bundle/filters/15",
     *          "inactivate": "/api/v1/task-bundle/filters/15/inactivate",
     *          "restore": "/api/v1/task-bundle/filters/15/restore",
     *          "set logged users remembered filter": "/api/v1/task-bundle/filters/15/user-remembered",
     *          "get logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered",
     *          "remove logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered/delete"
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
    public function getUsersRememberedFilterAction(): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_get_user_remembered');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $savedFilter = $loggedUser->getRememberedFilter();

        if ($savedFilter instanceof Filter) {
            $filterArray = $this->get('filter_service')->getFilterResponse($savedFilter->getId());

            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode($filterArray));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode(null));
        }

        return $response;
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
     *      200 ="Entity does not exist - no action was done|Entity was successfully deleted",
     *      401 ="Unauthorized request"
     *  })
     *
     * @return Response
     * @throws \LogicException
     */
    public function resetUsersRememberedFilterAction(): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_reset_user_remembered');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $savedFilter = $loggedUser->getRememberedFilter();

        if ($savedFilter instanceof Filter) {
            if (!$this->get('filter_voter')->isGranted(VoteOptions::SET_REMEMBERED_FILTER, $savedFilter)) {
                $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
                return $response;
            }

            $savedFilter->removeRememberUser($loggedUser);
            $loggedUser->setRememberedFilter(null);
            $this->getDoctrine()->getManager()->persist($savedFilter);
            $this->getDoctrine()->getManager()->persist($loggedUser);
            $this->getDoctrine()->getManager()->flush();

            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter was successfully removed from the logged user!']));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode(['message' => 'User does not have saved any filter!']));
        }

        return $response;
    }

    /**
     * @param Filter $filter
     * @param array $data
     * @param bool $create
     * @param $locationUrl
     * @param bool $project
     * @return Response
     * @throws \LogicException
     */
    private function updateEntity(Filter $filter, array $data, $create = false, $locationUrl, $project = false)
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
                if (!\in_array($key, $allowedUnitEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for a Filter Entity!']));
                    return $response;
                }
            }

            // Check if user can create PUBLIC filter (it's role has SHARE_FILTER/PROJECT_SHARED_FILTERS ACL)
            if (isset($data['public']) && (true === $data['public'] || 'true' === $data['public'] || 1 === (int)$data['public'])) {
                if ($project) {
                    $aclOptions = [
                        'acl' => UserRoleAclOptions::PROJECT_SHARED_FILTERS,
                        'user' => $this->getUser()
                    ];
                } else {
                    $aclOptions = [
                        'acl' => UserRoleAclOptions::SHARE_FILTERS,
                        'user' => $this->getUser()
                    ];
                }

                if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                    $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                    $response = $response->setContent(json_encode(['message' => 'You have not permission to create a PUBLIC filter!']));
                    return $response;
                }

                $filter->setPublic(true);
                unset($data['public']);
            } elseif ($create) {
                $filter->setPublic(false);
            }

            // Check if user can create REPORT Filter (it's role has to have ACL REPORT_FILTERS)
            if (isset($data['report']) && (true === $data['report'] || 'true' === $data['report'] || 1 === (int)$data['report'])) {
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
            if (isset($data['default']) && (true === $data['default'] || 'true' === $data['default'] || 1 === (int)$data['default'])) {
                $filter->setDefault(true);
            } elseif ($create) {
                $filter->setDefault(false);
            }

            // Check if every key sent in a filter array is allowed in FilterOptions and decode data correctly
            // Possilbe ways how to send Filter data:
            // 2. json: e.g {"assigned":"210,211","taskCompany":"202"}
            // 3. string in a specific format: assigned=>210,taskCompany=>202
            if (isset($data['filter'])) {
                //Try Json decode
                $filtersArray = json_decode($data['filter'], true);


                if (!\is_array($filtersArray)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Invalid filter parameter format!']));
                    return $response;
                }

                foreach ($filtersArray as $key => $value) {
                    if (!\in_array($key, FilterAttributeOptions::getConstants(), true)) {
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
                if (!\is_array($dataColumnsArray)) {
                    $dataColumnsArray = json_decode($data['columns'], true);
                    if (!\is_array($dataColumnsArray)) {
                        $dataColumnsArray = explode(',', $data['columns']);
                    }
                }

                foreach ($dataColumnsArray as $col) {
                    if (!\in_array($col, FilterColumnsOptions::getConstants(), true)) {
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
                if (!\is_array($dataColumnsArray)) {
                    $dataColumnsArray = json_decode($data['columns_task_attributes'], true);
                    if (!\is_array($dataColumnsArray)) {
                        $dataColumnsArray = explode(',', $data['columns_task_attributes']);
                    }
                }

                foreach ($dataColumnsArray as $col) {
                    $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($col);

                    if (!$taskAttribute instanceof TaskAttribute) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'Requested task attribute with id ' . $col . ' does not exist!']));
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
