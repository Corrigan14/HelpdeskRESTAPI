<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
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
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE user roles if this param is TRUE, only INACTIVE user roles if param is FALSE"
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
     * @throws \LogicException
     */
    public function listAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;
        $isActive = $request->get('isActive');

        $filtersForUrl['isActive'] = '&isActive=' . $isActive;
        $options = [
            'isActive' => $isActive,
            'filtersForUrl' => $filtersForUrl
        ];

        $userRolesArray = $this->get('user_role_service')->getUserRolesResponse($page, $options);
        return $this->json($userRolesArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *           "patch": "/api/v1/task-bundle/user-roles/5",
     *           "delete": "/api/v1/task-bundle/user-roles/5"
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($id);

        if (!$userRole instanceof UserRole) {
            return $this->createApiResponse([
                'message' => 'User role with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $userRoleArray = $this->get('user_role_service')->getUserRoleResponse($id);
        return $this->json($userRoleArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *           "put": "/api/v1/task-bundle/user-roles/5",
     *           "patch": "/api/v1/task-bundle/user-roles/5",
     *           "delete": "/api/v1/task-bundle/user-roles/5"
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
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $userRole = new UserRole();
        $userRole->setIsActive(true);

        $requestData = $request->request->all();
        return $this->updateEntity($userRole, $requestData, true);
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
     *           "put": "/api/v1/task-bundle/user-roles/5",
     *           "patch": "/api/v1/task-bundle/user-roles/5",
     *           "delete": "/api/v1/task-bundle/user-roles/5"
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($id);

        if (!$userRole instanceof UserRole) {
            return $this->notFoundResponse();
        }

        // No user can update admin role!
        if ($userRole->getTitle() === 'ADMIN') {
            return $this->createApiResponse([
                'message' => 'You can not update ADMIN role!',
            ], StatusCodesHelper::ACCESS_DENIED_CODE);
        }

        $requestData = $request->request->all();
        return $this->updateEntity($userRole, $requestData);
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
     *           "put": "/api/v1/task-bundle/user-roles/5",
     *           "patch": "/api/v1/task-bundle/user-roles/5",
     *           "delete": "/api/v1/task-bundle/user-roles/5"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the User role Entity",
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
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($id);

        if (!$userRole instanceof UserRole) {
            return $this->notFoundResponse();
        }

        // No user can update admin role!
        if ($userRole->getTitle() === 'ADMIN') {
            return $this->createApiResponse([
                'message' => 'You can not update ADMIN role!',
            ], StatusCodesHelper::ACCESS_DENIED_CODE);
        }

        $requestData = $request->request->all();
        return $this->updateEntity($userRole, $requestData);
    }

    /**
     * @ApiDoc(
     *  description="Delete User role Entity",
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($id);

        if (!$userRole instanceof UserRole) {
            return $this->notFoundResponse();
        }

        // No user can delete admin role!
        if ($userRole->getTitle() === 'ADMIN') {
            return $this->createApiResponse([
                'message' => 'You can not delete ADMIN role!',
            ], StatusCodesHelper::ACCESS_DENIED_CODE);
        }

        $userRole->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($userRole);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => 'is_active param of Entity was successfully changed to inactive: 0',
        ], StatusCodesHelper::SUCCESSFUL_CODE);
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
     *           "patch": "/api/v1/task-bundle/user-roles/5",
     *           "delete": "/api/v1/task-bundle/user-roles/5"
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_ROLE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($id);

        if (!$userRole instanceof UserRole) {
            return $this->notFoundResponse();
        }

        $userRole->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($userRole);
        $this->getDoctrine()->getManager()->flush();

        $userRoleArray = $this->get('user_role_service')->getUserRoleResponse($id);
        return $this->json($userRoleArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param UserRole $userRole
     * @param $requestData
     * @param bool $create
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateEntity(UserRole $userRole, $requestData, $create = false)
    {
        $allowedUnitEntityParams = [
            'title',
            'description',
            'homepage',
            'acl',
            'order',
            'is_active'
        ];

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!in_array($key, $allowedUnitEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for Tag Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        /** @var UserRole $loggedUserRole */
        $loggedUserRole = $loggedUser->getUserRole();
        $loggedUserAcl = $loggedUserRole->getAcl();

        // Check the order of role: this has to be higher compare to logged users role
        if (isset($requestData['order'])) {
            $orderNum = (int)$requestData['order'];
            if ($orderNum <= $loggedUserRole->getOrder()) {
                return $this->createApiResponse([
                    'message' => 'Order of created role has to be higher like yours role order: ' . $loggedUserRole->getOrder(),
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
            $requestData['order'] = $orderNum;
        }

        // Check the ACL of role: this can not contain different ACL rules like logged user has
        if (isset($requestData['acl'])) {
            $aclData = $requestData['acl'];
            if (!is_array($aclData)) {
                $aclData = explode(',', $aclData);
            }
            foreach ($aclData as $acl) {
                if (!in_array($acl, $loggedUserAcl)) {
                    return $this->createApiResponse([
                        'message' => 'The ACL can contains just yours ACL params: ' . implode(',', $loggedUserAcl),
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
            $requestData['acl'] = $aclData;
        }

        $errors = $this->get('entity_processor')->processEntity($userRole, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($userRole);
            $this->getDoctrine()->getManager()->flush();

            $userRoleArray = $this->get('user_role_service')->getUserRoleResponse($userRole->getId());
            return $this->json($userRoleArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
