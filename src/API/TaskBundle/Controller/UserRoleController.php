<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\DataFixtures\ORM\UserRoleFixture;
use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserRoleController
 *
 * @package API\TaskBundle\Controller
 */
class UserRoleController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response - option if we have pagination ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 1,
     *             "title": "ADMIN",
     *             "description": "Admin is a main system role. All ACL are available.",
     *             "homepage": "/",
     *             "acl":
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
     *             ]
     *             "is_active": true,
     *             "order": 1
     *          },
     *          {
     *              "id": 2,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl":
     *              [
     *                "login_to_system",
     *                "create_tasks",
     *                "create_projects",
     *                "company_settings",
     *                "report_filters",
     *                "sent_emails_from_comments",
     *                "update_all_tasks"
     *              ]
     *              "is_active": true
     *              "order": 2
     *           },
     *           {
     *               "id": 3,
     *               "title": "AGENT",
     *               "description": null,
     *               "homepage": "/",
     *               "acl":
     *               [
     *                  "login_to_system",
     *                  "create_tasks",
     *                  "create_projects",
     *                  "company_settings",
     *                  "sent_emails_from_comments"
     *               ]
     *               "is_active": true
     *               "order": 3
     *            },
     *            {
     *                "id": 4,
     *                "title": "CUSTOMER",
     *                "description": null,
     *                "homepage": "/",
     *                "acl":
     *                [
     *                   "login_to_system",
     *                   "create_tasks"
     *                ],
     *                "is_active": true
     *                "order": 4
     *             }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/user-roles?page=1&isActive=true",
     *           "first": "/api/v1/task-bundle/user-roles?page=1&isActive=true",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/user-roles?page=2&isActive=true",
     *            "last": "/api/v1/task-bundle/user-roles?page=3&isActive=true"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of User Roles",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Order"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE user roles if this param is TRUE, only INACTIVE user roles if param is FALSE"
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
     *      403 ="Access denied"
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('user_role_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);


        // To list User Roles User has to have USER_ROLE_SETTINGS ACL or USER_SETTINGS
        $aclForUserRoleSettingsOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];
        $hasUserRoleSettingsACL = $this->get('acl_helper')->roleHasACL($aclForUserRoleSettingsOptions);

        $aclForUserSettingsOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];
        $hasUserSettingsACL = $this->get('acl_helper')->roleHasACL($aclForUserSettingsOptions);
        
        if (false === $hasUserRoleSettingsACL && false === $hasUserSettingsACL) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];
            $isActive = $processedFilterParams['isActive'];

            $filtersForUrl = [
                'isActive' => '&isActive=' . $isActive,
                'order' => '&order=' . $order,
            ];

            $options = [
                'isActive' => $isActive,
                'order' => $order,
                'filtersForUrl' => $filtersForUrl,
                'limit' => $limit
            ];

            $userRolesArray = $this->get('user_role_service')->getUserRolesResponse($page, $options);
            $response = $response->setContent(json_encode($userRolesArray));
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
     *           "id": 155,
     *           "title": "MANAGER",
     *           "description": null,
     *           "homepage": "/",
     *           "acl":
     *           [
     *              "login_to_system",
     *              "create_tasks",
     *              "create_projects",
     *              "company_settings",
     *              "report_filters",
     *              "sent_emails_from_comments",
     *              "update_all_tasks"
     *           ],
     *           "order": 2,
     *           "is_active": true,
     *           "users":
     *           [
     *              {
     *                 "id": 2576,
     *                 "username": "manager",
     *                 "email": "manager@manager.sk"
     *               }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/user-roles/5",
     *           "inactivate": "/api/v1/task-bundle/user-roles/5/inactivate"
     *           "restore": "/api/v1/task-bundle/user-roles/5/restore"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a User role entity",
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
     *  output="API\TaskBundle\Entity\UserRole",
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
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('user_role', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($id);

        if (!$userRole instanceof UserRole) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User role with requested Id does not exist!']));
            return $response;
        }

        $userRoleArray = $this->get('user_role_service')->getUserRoleResponse($id);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($userRoleArray));
        return $response;
    }

    /**
     * ### Response ###
     *      {
     *       "data":
     *        {
     *           "id": 155,
     *           "title": "MANAGER",
     *           "description": null,
     *           "homepage": "/",
     *           "acl":
     *           [
     *              "login_to_system",
     *              "create_tasks",
     *              "create_projects",
     *              "company_settings",
     *              "report_filters",
     *              "sent_emails_from_comments",
     *              "update_all_tasks"
     *           ],
     *           "order": 2,
     *           "is_active": true,
     *           "users":
     *           [
     *              {
     *                 "id": 2576,
     *                 "username": "manager",
     *                 "email": "manager@manager.sk"
     *               }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/user-roles/5"
     *           "inactivate": "/api/v1/task-bundle/user-roles/5/inactivate"
     *           "restore": "/api/v1/task-bundle/user-roles/5/restore"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new User role Entity",
     *  input={"class"="API\TaskBundle\Entity\UserRole"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\UserRole"},
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
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('user_role_create');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $userRole = new UserRole();
        $userRole->setIsActive(true);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateEntity($userRole, $requestBody, true, $locationURL);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 155,
     *           "title": "MANAGER",
     *           "description": null,
     *           "homepage": "/",
     *           "acl":
     *           [
     *              "login_to_system",
     *              "create_tasks",
     *              "create_projects",
     *              "company_settings",
     *              "report_filters",
     *              "sent_emails_from_comments",
     *              "update_all_tasks"
     *           ],
     *           "order": 2,
     *           "is_active": true,
     *           "users":
     *           [
     *              {
     *                 "id": 2576,
     *                 "username": "manager",
     *                 "email": "manager@manager.sk"
     *               }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/user-roles/5"
     *           "inactivate": "/api/v1/task-bundle/user-roles/5/inactivate"
     *           "restore": "/api/v1/task-bundle/user-roles/5/restore"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the User role Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\UserRole"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\UserRole"},
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
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('user_role_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($id);
        if (!$userRole instanceof UserRole) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User role with requested Id does not exist!']));
            return $response;
        }

        // No user can update admin role!
        if ($userRole->getTitle() === 'ADMIN') {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => 'You can not update ADMIN role!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateEntity($userRole, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Inactivate User role Entity",
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
     *      200 ="is_active param of Entity was successfully changed to inactive: 0",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('user_role_inactivate', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($id);

        if (!$userRole instanceof UserRole) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User role with requested Id does not exist!']));
            return $response;
        }

        // No user can delete admin role!
        if ($userRole->getTitle() === 'ADMIN') {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => 'You can not delete ADMIN role!']));
            return $response;
        }

        $userRole->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($userRole);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => 'is_active param of Entity was successfully changed to inactive: 0']));
        return $response;
    }


    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 155,
     *           "title": "MANAGER",
     *           "description": null,
     *           "homepage": "/",
     *           "acl":
     *           [
     *              "login_to_system",
     *              "create_tasks",
     *              "create_projects",
     *              "company_settings",
     *              "report_filters",
     *              "sent_emails_from_comments",
     *              "update_all_tasks"
     *           ],
     *           "order": 2,
     *           "is_active": true,
     *           "users":
     *           [
     *              {
     *                 "id": 2576,
     *                 "username": "manager",
     *                 "email": "manager@manager.sk"
     *               }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/user-roles/5",
     *           "inactivate": "/api/v1/task-bundle/user-roles/5/inactivate"
     *           "restore": "/api/v1/task-bundle/user-roles/5/restore"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Restore User role Entity",
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
     *      200 ="is_active param of Entity was successfully changed to active: 1",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('user_role_restore', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($id);

        if (!$userRole instanceof UserRole) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User role with requested Id does not exist!']));
            return $response;
        }

        $userRole->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($userRole);
        $this->getDoctrine()->getManager()->flush();

        $userRoleArray = $this->get('user_role_service')->getUserRoleResponse($id);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($userRoleArray));
        return $response;
    }

    /**
     * @param UserRole $userRole
     * @param $requestData
     * @param bool $create
     * @param string $locationUrl
     * @return Response
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    private function updateEntity(UserRole $userRole, $requestData, $create = false, string $locationUrl): Response
    {
        $allowedUnitEntityParams = [
            'title',
            'description',
            'homepage',
            'acl',
            'order'
        ];

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);

        if (false !== $requestData) {
            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
            }

            foreach ($requestData as $key => $value) {
                if (!\in_array($key, $allowedUnitEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for User-Role Entity!']));
                    return $response;
                }
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            /** @var User $loggedUser */
            $loggedUser = $this->getUser();
            /** @var UserRole $loggedUserRole */
            $loggedUserRole = $loggedUser->getUserRole();
            $loggedUserAcl = $loggedUserRole->getAcl();

            // Check the order of role: this has to be higher compare to the logged users role
            if (isset($requestData['order'])) {
                $orderNum = (int)$requestData['order'];
                if ($orderNum <= $loggedUserRole->getOrder()) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Order of created role has to be higher like yours role order: ' . $loggedUserRole->getOrder()]));
                    return $response;
                }
                $requestData['order'] = $orderNum;
            }

            // Check the role's ACL: this can not contain different ACL rules as logged user has
            if (isset($requestData['acl']) && 'null' !== strtolower($requestData['acl'])) {
                $aclData = json_decode($requestData['acl'], true);
                if (!\is_array($aclData)) {
                    $aclData = explode(',', $requestData['acl']);
                }
                if (!empty($aclData)) {
                    foreach ($aclData as $acl) {
                        if (!\in_array($acl, $loggedUserAcl, true)) {
                            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                            $response = $response->setContent(json_encode(['message' => 'The ACL can contain only yours ACL params: ' . implode(',', $loggedUserAcl)]));
                            return $response;
                        }
                    }
                } else {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'The ACL can contain only yours ACL params: ' . implode(',', $loggedUserAcl)]));
                    return $response;
                }
                $requestData['acl'] = $aclData;
            }

            // Check if Homepage is set, if not: "/" will be set as Default
            if ($create) {
                if (!isset($requestData['homepage'])) {
                    $userRole->setHomepage(UserRoleFixture::BASE_URL);
                }
            }

            $errors = $this->get('entity_processor')->processEntity($userRole, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($userRole);
                $this->getDoctrine()->getManager()->flush();

                $userRoleArray = $this->get('user_role_service')->getUserRoleResponse($userRole->getId());
                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($userRoleArray));
                return $response;
            }

            $data = [
                'errors' => $errors,
                'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
            ];
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode($data));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;
    }
}
