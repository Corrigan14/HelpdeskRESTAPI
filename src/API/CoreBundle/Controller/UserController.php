<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\CoreBundle\Security\VoteOptions;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Controller\ApiBaseController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use API\CoreBundle\Entity\Company;

/**
 * Class UsersController
 *
 * @package API\CoreBundle\Controller
 */
class UserController extends ApiBaseController
{
    /**
     * ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 2581,
     *             "username": "customer2",
     *             "email": "customer@customer2.sk",
     *             "language": "AJ",
     *             "is_active": true,
     *             "image": null,
     *             "detailData":
     *             {
     *                "id": 2306,
     *                "name": "Customer2",
     *                "surname": "Customerovic2",
     *                "title_before": null,
     *                "title_after": null,
     *                "function": null,
     *                "mobile": null,
     *                "tel": null,
     *                "fax": null,
     *                "signature": null,
     *                "street": null,
     *                "city": null,
     *                "zip": null,
     *                "country": null,
     *                "facebook": null,
     *                "twitter": null,
     *                "linkdin": null,
     *                "google": null
     *              },
     *              "user_role":
     *              {
     *                 "id": 157,
     *                 "title": "CUSTOMER",
     *                 "description": null,
     *                 "homepage": "/",
     *                 "acl":
     *                 [
     *                    "login_to_system",
     *                    "create_tasks"
     *                 ],
     *                 "order": 4
     *              },
     *              "company":
     *              {
     *                 "id": 1802,
     *                 "title": "Web-Solutions"
     *              }
     *           },
     *       ]
     *       "_links":
     *       {
     *           "self": "/api/v1/core-bundle/users?page=1",
     *           "first": "/api/v1/core-bundle/users?page=1",
     *           "prev": false,
     *           "next": "/api/v1/core-bundle/users?page=2",
     *           "last": "/api/v1/core-bundle/users?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     * @ApiDoc(
     *  description="Returns a list of Users with selected detail Info (user Entity, UserData Entity), you can pass in
     *  a fields option to get custom data",
     *  filters={
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE users if this param is TRUE, only INACTIVE users if param is FALSE"
     *     },
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Username"
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
     *  },
     * )
     *
     * @param Request $request
     *
     * @return Response|JsonResponse
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function listAction(Request $request): Response
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        // JSON API Response - Content type and Location settings
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $locationURL = $this->generateUrl('users_list');
        $response->headers->set('Location', $locationURL);

        if (false !== $requestBody) {
            // Filter params processing
            if (isset($requestBody['page'])) {
                $pageNum = $requestBody['page'];
                $page = (int)$pageNum;
            } else {
                $page = 1;
            }

            if (isset($requestBody['limit'])) {
                $limitNum = $requestBody['limit'];
                $limit = (int)$limitNum;
            } else {
                $limit = 10;
            }

            if (999 === $limit) {
                $page = 1;
            }

            if (isset($requestBody['order'])) {
                $orderString = $requestBody['order'];
                $orderString = strtolower($orderString);
                $order = $orderString === 'asc' || $orderString === 'desc';
            } else {
                $order = 'ASC';
            }

            if (isset($requestBody['isActive'])) {
                $isActive = $requestBody['isActive'];
            } else {
                $isActive = 'all';
            }

            $filtersForUrl = [
                'isActive' => '&isActive=' . $isActive,
                'order' => '&order=' . $order,
            ];

            $usersArray = $this->get('api_user.service')->getUsersResponse($page, $isActive, $order, $filtersForUrl, $limit);
            $response = $response->setContent(json_encode($usersArray));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }

        return $response;
    }

    /**
     * ### Response ###
     *       [
     *          {
     *             "id": 2581,
     *             "username": "customer2",
     *          },
     *          {
     *             "id": 2582,
     *             "username": "customer3",
     *          },
     *          {
     *             "id": 2583,
     *             "username": "customer4",
     *          },
     *          {
     *             "id": 2584,
     *             "username": "customer5",
     *          },
     *          {
     *             "id": 2585,
     *             "username": "customer6",
     *          },
     *          {
     *             "id": 2586,
     *             "username": "customer7",
     *          },
     *          {
     *             "id": 2587,
     *             "username": "customer8",
     *          },
     *       ]
     *
     * @ApiDoc(
     *  description="Returns a list of All active Users",
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
     *  },
     * )
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function listOfAllUsersAction(): Response
    {
        // JSON API Response - Content type and Location settings
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $locationURL = $this->generateUrl('users_list_of_all_active');
        $response->headers->set('Location', $locationURL);

        $allUsers = $this->get('api_user.service')->getListOfAllUsers();

        $response = $response->setContent(json_encode($allUsers));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);

        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *         {
     *             "id": 2581,
     *             "username": "customer2",
     *             "email": "customer@customer2.sk",
     *             "language": "AJ",
     *             "is_active": true,
     *             "image": null,
     *             "detailData":
     *             {
     *                "id": 2306,
     *                "name": "Customer2",
     *                "surname": "Customerovic2",
     *                "title_before": null,
     *                "title_after": null,
     *                "function": null,
     *                "mobile": null,
     *                "tel": null,
     *                "fax": null,
     *                "signature": null,
     *                "street": null,
     *                "city": null,
     *                "zip": null,
     *                "country": null,
     *                "facebook": null,
     *                "twitter": null,
     *                "linkdin": null,
     *                "google": null
     *              },
     *              "user_role":
     *              {
     *                 "id": 157,
     *                 "title": "CUSTOMER",
     *                 "description": null,
     *                 "homepage": "/",
     *                 "acl":
     *                 [
     *                    "login_to_system",
     *                    "create_tasks"
     *                 ],
     *                 "order": 4
     *              },
     *              "company":
     *              {
     *                 "id": 1802,
     *                 "title": "Web-Solutions"
     *              }
     *           }
     *        },
     *        "_links": ▿
     *         {
     *           "put": "/api/v1/core-bundle/users/85",
     *           "put: user-role": "/api/v1/core-bundle/users/85/user-role/32",
     *           "patch": "/api/v1/core-bundle/users/85",
     *           "patch: user-role": "/api/v1/core-bundle/users/85/user-role/32",
     *           "delete": "/api/v1/core-bundle/users/85",
     *           "restore": "/api/v1/core-bundle/users/85/restore",
     *           "put: company": "/api/v1/core-bundle/users/85/company/41",
     *           "put: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41",
     *           "patch: company": "/api/v1/core-bundle/users/85/company/41",
     *           "patch: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41"
     *         },
     *         "allowedUserRoles":
     *         [
     *            [
     *               {
     *                  "id": 31,
     *                  "title": "MANAGER",
     *                  "description": null,
     *                  "homepage": "/",
     *                  "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                  "is_active": true,
     *                  "order": 2
     *               },
     *              {
     *                  "id": 32,
     *                  "title": "AGENT",
     *                  "description": null,
     *                  "homepage": "/",
     *                  "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"sent_emails_from_comments\"]",
     *                  "is_active": true,
     *                  "order": 3
     *              }
     *           ]
     *        ]
     *      }
     *
     * @ApiDoc(
     *  description="Return User (user Entity)",
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
     *  output={"class"="API\CoreBundle\Entity\User"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found user"
     *  },
     *  )
     *
     * @param int $id
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $locationURL = $this->generateUrl('user', ['id' => $id]);
        $response->headers->set('Location', $locationURL);

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::NOT_FOUND_MESSAGE]));

            return $response;
        }

        // User can view his own data
        if ($this->getUser()->getId() !== $id) {
            $aclOptions = [
                'acl' => UserRoleAclOptions::USER_SETTINGS,
                'user' => $this->getUser()
            ];

            if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

                return $response;
            }
        }

        $userCompany = $user->getCompany();
        if ($userCompany instanceof Company) {
            $userCompanyId = $userCompany->getId();
        } else {
            $userCompanyId = false;
        }
        $ids = [
            'userId' => $user->getId(),
            'userRoleId' => $user->getUserRole()->getId(),
            'userCompanyId' => $userCompanyId
        ];

        $userArray = $this->get('api_user.service')->getUserResponse($ids);
        $userRoles = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->getAllowedUserRoles($user->getUserRole()->getOrder());
        $allowedRolesArray['allowedUserRoles'] = [$userRoles];

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(array_merge($userArray, $allowedRolesArray)));

        return $response;
    }

    /**
     *  ### Response ###
     *      ▿{
     *         "data": ▿
     *         {
     *             "id": 2581,
     *             "username": "customer2",
     *             "email": "customer@customer2.sk",
     *             "language": "AJ",
     *             "is_active": true,
     *             "image": null,
     *             "detailData":
     *             {
     *                "id": 2306,
     *                "name": "Customer2",
     *                "surname": "Customerovic2",
     *                "title_before": null,
     *                "title_after": null,
     *                "function": null,
     *                "mobile": null,
     *                "tel": null,
     *                "fax": null,
     *                "signature": null,
     *                "street": null,
     *                "city": null,
     *                "zip": null,
     *                "country": null,
     *                "facebook": null,
     *                "twitter": null,
     *                "linkdin": null,
     *                "google": null
     *              },
     *              "user_role":
     *              {
     *                 "id": 157,
     *                 "title": "CUSTOMER",
     *                 "description": null,
     *                 "homepage": "/",
     *                 "acl":
     *                 [
     *                    "login_to_system",
     *                    "create_tasks"
     *                 ],
     *                 "order": 4
     *              },
     *              "company":
     *              {
     *                 "id": 1802,
     *                 "title": "Web-Solutions"
     *              }
     *           }
     *         },
     *        "_links": ▿
     *         {
     *           "put": "/api/v1/core-bundle/users/85",
     *           "put: user-role": "/api/v1/core-bundle/users/85/user-role/32",
     *           "patch": "/api/v1/core-bundle/users/85",
     *           "patch: user-role": "/api/v1/core-bundle/users/85/user-role/32",
     *           "delete": "/api/v1/core-bundle/users/85",
     *           "restore": "/api/v1/core-bundle/users/85/restore",
     *           "put: company": "/api/v1/core-bundle/users/85/company/41",
     *           "put: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41",
     *           "patch: company": "/api/v1/core-bundle/users/85/company/41",
     *           "patch: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41"
     *         },
     *         "allowedUserRoles":
     *         [
     *            [
     *               {
     *                  "id": 31,
     *                  "title": "MANAGER",
     *                  "description": null,
     *                  "homepage": "/",
     *                  "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                  "is_active": true,
     *                  "order": 2
     *               },
     *              {
     *                  "id": 32,
     *                  "title": "AGENT",
     *                  "description": null,
     *                  "homepage": "/",
     *                  "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"sent_emails_from_comments\"]",
     *                  "is_active": true,
     *                  "order": 3
     *              }
     *           ]
     *        ]
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create new User, UserData Entity",
     *  input={"class"="API\CoreBundle\Entity\User"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\User"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     *  )
     *
     * @param Request $request
     *
     * @param int $userRoleId
     * @param bool|int $companyId
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAction(Request $request, int $userRoleId, $companyId = false)
    {
        // JSON API Response - Content type and Location settings
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($companyId) {
            $locationURL = $this->generateUrl('user_create', ['userRoleId' => $userRoleId, 'companyId' => $companyId]);
        }else{
            $locationURL = $this->generateUrl('user_create', ['userRoleId' => $userRoleId]);
        }
        $response->headers->set('Location', $locationURL);

        // Check if user has permission to CRUD User entity
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];
        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        $user = new User();
        $user->setIsActive(true);

        // Upload and save avatar
        $file = $request->files->get('image');
        if (null !== $file) {
            $imageSlug = $this->get('upload_helper')->uploadFile($file, true);
            $user->setImage($imageSlug);
        }

        if ($userRoleId) {
            $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($userRoleId);
            if (!$userRole instanceof UserRole) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'User role with requested Id does not exist!']));
                return $response;
            }
            // Check if user can create User entity with requested User Role
            $voteOptions = [
                'userRole' => $userRole
            ];
            if (!$this->get('user_voter')->isGranted(VoteOptions::CREATE_USER_WITH_USER_ROLE, $voteOptions)) {
                $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                $response = $response->setContent(json_encode(['message' => 'You can not create user with selected User Role!']));
                return $response;
            }
            $user->setUserRole($userRole);

            if ($userRole->getTitle() === 'ADMIN') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }
        }

        if ($companyId) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($companyId);

            if (!$company instanceof Company) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Company with requested Id does not exist!']));
                return $response;
            }
            $user->setCompany($company);
        }

        return $this->updateUser($user, $requestBody, true, $locationURL);
    }

    /**
     *  ### Response ###
     *      ▿{
     *         "data": ▿
     *         {
     *             "id": 2581,
     *             "username": "customer2",
     *             "email": "customer@customer2.sk",
     *             "language": "AJ",
     *             "is_active": true,
     *             "image": null,
     *             "detailData":
     *             {
     *                "id": 2306,
     *                "name": "Customer2",
     *                "surname": "Customerovic2",
     *                "title_before": null,
     *                "title_after": null,
     *                "function": null,
     *                "mobile": null,
     *                "tel": null,
     *                "fax": null,
     *                "signature": null,
     *                "street": null,
     *                "city": null,
     *                "zip": null,
     *                "country": null,
     *                "facebook": null,
     *                "twitter": null,
     *                "linkdin": null,
     *                "google": null
     *              },
     *              "user_role":
     *              {
     *                 "id": 157,
     *                 "title": "CUSTOMER",
     *                 "description": null,
     *                 "homepage": "/",
     *                 "acl":
     *                 [
     *                    "login_to_system",
     *                    "create_tasks"
     *                 ],
     *                 "order": 4
     *              },
     *              "company":
     *              {
     *                 "id": 1802,
     *                 "title": "Web-Solutions"
     *              }
     *           }
     *         },
     *        "_links": ▿
     *         {
     *           "put": "/api/v1/core-bundle/users/85",
     *           "put: user-role": "/api/v1/core-bundle/users/85/user-role/32",
     *           "patch": "/api/v1/core-bundle/users/85",
     *           "patch: user-role": "/api/v1/core-bundle/users/85/user-role/32",
     *           "delete": "/api/v1/core-bundle/users/85",
     *           "restore": "/api/v1/core-bundle/users/85/restore",
     *           "put: company": "/api/v1/core-bundle/users/85/company/41",
     *           "put: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41",
     *           "patch: company": "/api/v1/core-bundle/users/85/company/41",
     *           "patch: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41"
     *         },
     *         "allowedUserRoles":
     *         [
     *            [
     *               {
     *                  "id": 31,
     *                  "title": "MANAGER",
     *                  "description": null,
     *                  "homepage": "/",
     *                  "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                  "is_active": true,
     *                  "order": 2
     *               },
     *              {
     *                  "id": 32,
     *                  "title": "AGENT",
     *                  "description": null,
     *                  "homepage": "/",
     *                  "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"sent_emails_from_comments\"]",
     *                  "is_active": true,
     *                  "order": 3
     *              }
     *           ]
     *        ]
     *      }
     *
     * @ApiDoc(
     *  description="Update the entire User",
     *  input={"class"="API\CoreBundle\Entity\User"},
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
     *  output={"class"="API\CoreBundle\Entity\User"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *      409 ="Invalid parameters",
     *  })
     *
     * @param int $id
     * @param Request $request
     *
     * @param bool $userRoleId
     * @param int|bool $companyId
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request, $userRoleId = false, $companyId = false)
    {
        // Check if user has permission to CRUD User entity
        // User can update his own data
        if ($this->getUser()->getId() !== $id) {
            $aclOptions = [
                'acl' => UserRoleAclOptions::USER_SETTINGS,
                'user' => $this->getUser()
            ];
            if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                return $this->accessDeniedResponse();
            }
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if ($userRoleId && $this->getUser()->getId() !== $id) {
            $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($userRoleId);
            if (!$userRole instanceof UserRole) {
                return $this->createApiResponse([
                    'message' => 'User role with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            // Check if user can create User entity with requested User Role
            $voteOptions = [
                'userRole' => $userRole
            ];
            if (!$this->get('user_voter')->isGranted(VoteOptions::CREATE_USER_WITH_USER_ROLE, $voteOptions)) {
                return $this->createApiResponse([
                    'message' => 'You can not create user with selected User Role!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            }
            $user->setUserRole($userRole);

            if ($userRole->getTitle() === 'ADMIN') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }
        } elseif ($userRoleId && $this->getUser()->getId() === $id) {
            return $this->createApiResponse([
                'message' => 'You can not change your own User Role!',
            ], StatusCodesHelper::ACCESS_DENIED_CODE);
        }

        if ($companyId && $this->getUser()->getId() !== $id) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($companyId);

            if (!$company instanceof Company) {
                return $this->createApiResponse([
                    'message' => 'Company with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $user->setCompany($company);
        } elseif ($companyId && $this->getUser()->getId() === $id) {
            return $this->createApiResponse([
                'message' => 'You can not change your own Company!',
            ], StatusCodesHelper::ACCESS_DENIED_CODE);
        }

        // Upload and save avatar
        $file = $request->files->get('image');
        if (null !== $file) {
            $imageSlug = $this->get('upload_helper')->uploadFile($file, true);
            $user->setImage($imageSlug);
        }

        $requestData = $request->request->all();

        return $this->updateUser($user, $requestData);
    }

    /**
     * @ApiDoc(
     *  description="Delete User Entity",
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
     *      404 ="Not found user",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(int $id)
    {
        // Check if user has permission to CRUD User entity
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];
        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (null === $user) {
            return $this->notFoundResponse();
        }

        $user->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::UNACITVATE_MESSAGE,
        ], StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      ▿{
     *         "data": ▿
     *         {
     *             "id": 2581,
     *             "username": "customer2",
     *             "email": "customer@customer2.sk",
     *             "language": "AJ",
     *             "is_active": true,
     *             "image": null,
     *             "detailData":
     *             {
     *                "id": 2306,
     *                "name": "Customer2",
     *                "surname": "Customerovic2",
     *                "title_before": null,
     *                "title_after": null,
     *                "function": null,
     *                "mobile": null,
     *                "tel": null,
     *                "fax": null,
     *                "signature": null,
     *                "street": null,
     *                "city": null,
     *                "zip": null,
     *                "country": null,
     *                "facebook": null,
     *                "twitter": null,
     *                "linkdin": null,
     *                "google": null
     *              },
     *              "user_role":
     *              {
     *                 "id": 157,
     *                 "title": "CUSTOMER",
     *                 "description": null,
     *                 "homepage": "/",
     *                 "acl":
     *                 [
     *                    "login_to_system",
     *                    "create_tasks"
     *                 ],
     *                 "order": 4
     *              },
     *              "company":
     *              {
     *                 "id": 1802,
     *                 "title": "Web-Solutions"
     *              }
     *           }
     *         },
     *         "_links": ▿
     *         {
     *           "put": "/api/v1/core-bundle/users/85",
     *           "put: user-role": "/api/v1/core-bundle/users/85/user-role/32",
     *           "patch": "/api/v1/core-bundle/users/85",
     *           "patch: user-role": "/api/v1/core-bundle/users/85/user-role/32",
     *           "delete": "/api/v1/core-bundle/users/85",
     *           "restore": "/api/v1/core-bundle/users/85/restore",
     *           "put: company": "/api/v1/core-bundle/users/85/company/41",
     *           "put: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41",
     *           "patch: company": "/api/v1/core-bundle/users/85/company/41",
     *           "patch: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41"
     *         },
     *         "allowedUserRoles":
     *         [
     *            [
     *               {
     *                  "id": 31,
     *                  "title": "MANAGER",
     *                  "description": null,
     *                  "homepage": "/",
     *                  "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                  "is_active": true,
     *                  "order": 2
     *               },
     *              {
     *                  "id": 32,
     *                  "title": "AGENT",
     *                  "description": null,
     *                  "homepage": "/",
     *                  "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"sent_emails_from_comments\"]",
     *                  "is_active": true,
     *                  "order": 3
     *              }
     *           ]
     *        ]
     *      }
     *
     * @ApiDoc(
     *  description="Restore User Entity",
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
     *  output={"class"="API\CoreBundle\Entity\User"},
     *  statusCodes={
     *      200 ="is_active param of Entity was successfully changed to active: 1",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found user",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(int $id)
    {
        // Check if user has permission to CRUD User entity
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];
        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (null === $user) {
            return $this->notFoundResponse();
        }

        $user->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        $userCompany = $user->getCompany();
        if ($userCompany instanceof Company) {
            $userCompanyId = $userCompany->getId();
        } else {
            $userCompanyId = false;
        }
        $ids = [
            'userId' => $user->getId(),
            'userRoleId' => $user->getUserRole()->getId(),
            'userCompanyId' => $userCompanyId
        ];
        $userArray = $this->get('api_user.service')->getUserResponse($ids);
        $userRoles = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->getAllowedUserRoles($user->getUserRole()->getOrder());
        $allowedRolesArray['allowedUserRoles'] = [$userRoles];

        return $this->json(array_merge($userArray, $userRoles), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *     {
     *       "data":
     *       [
     *             *         {
     *             "id": 2581,
     *             "username": "customer2",
     *             "email": "customer@customer2.sk",
     *             "language": "AJ",
     *             "is_active": true,
     *             "image": null,
     *             "detailData":
     *             {
     *                "id": 2306,
     *                "name": "Customer2",
     *                "surname": "Customerovic2",
     *                "title_before": null,
     *                "title_after": null,
     *                "function": null,
     *                "mobile": null,
     *                "tel": null,
     *                "fax": null,
     *                "signature": null,
     *                "street": null,
     *                "city": null,
     *                "zip": null,
     *                "country": null,
     *                "facebook": null,
     *                "twitter": null,
     *                "linkdin": null,
     *                "google": null
     *              },
     *              "user_role":
     *              {
     *                 "id": 157,
     *                 "title": "CUSTOMER",
     *                 "description": null,
     *                 "homepage": "/",
     *                 "acl":
     *                 [
     *                    "login_to_system",
     *                    "create_tasks"
     *                 ],
     *                 "order": 4
     *              },
     *              "company":
     *              {
     *                 "id": 1802,
     *                 "title": "Web-Solutions"
     *              }
     *           }
     *       ]
     *       "_links":
     *       {
     *           "self": "/api/v1/core-bundle/users?page=1&term=customer70",
     *           "first": "/api/v1/core-bundle/users?page=1&term=customer70",
     *           "prev": false,
     *           "next": false,
     *           "last": "/api/v1/core-bundle/users?page=1&term=customer70"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     * @ApiDoc(
     *  description="Search in User Entity",
     *  filters={
     *     {
     *       "name"="term",
     *       "description"="Search term"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE users if this param is TRUE, only INACTIVE users if param is FALSE"
     *     },
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Username"
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
     *      200 ="Entity was successfully found",
     *      401 ="Unauthorized request",
     *      403 ="Access denied"
     *  })
     *
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function searchAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $filtersForUrl = [];

        $term = $request->get('term');
        if (null !== $term) {
            $term = strtolower($term);
            $filtersForUrl['term'] = '&term=' . $term;
        } else {
            $term = false;
        }

        $pageNum = $request->get('page');
        $pageNum = (int)$pageNum;
        $page = ($pageNum === 0) ? 1 : $pageNum;

        $limitNum = $request->get('limit');
        $limit = (int)$limitNum ? (int)$limitNum : 10;

        $orderString = $request->get('order');
        $orderString = strtolower($orderString);
        $order = ($orderString === 'asc' || $orderString === 'desc') ? $orderString : 'ASC';

        $isActive = $request->get('isActive');
        if (null !== $isActive) {
            $filtersForUrl['isActive'] = '&isActive=' . $isActive;
        } else {
            $isActive = false;
        }

        $filtersForUrl['order'] = '&order=' . $order;
        $filtersForUrl['limit'] = '&limit=' . $limit;

        $usersArray = $this->get('api_user.service')->getUsersSearchResponse($term, $page, $isActive, $filtersForUrl, $order, $limit);
        return $this->json($usersArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @ApiDoc(
     *  description="Reset User's Password - Admin or logged user",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  parameters={
     *          {"name"="password", "dataType"="string", "required"=true, "format"="POST", "description"="Reseted password"},
     *          {"name"="password_repeat", "dataType"="string", "required"=true, "format"="POST", "description"="Reseted password - repeat"}
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="Password was successfully reseted",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found user",
     *      409 ="Invalid parameters",
     *  })
     *
     * @param Request $request
     * @param int $id
     * @return Response|bool
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function resetPasswordAction(Request $request, int $id)
    {
        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Only admin can reset password - for all users
        if (!$this->get('acl_helper')->isAdmin() && $this->getUser()->getId() !== $id) {
            return $this->createApiResponse([
                'message' => 'Only admin can reset password of every user. Logged User can change his own password!',
            ], StatusCodesHelper::ACCESS_DENIED_CODE);
        }

        $password = $request->get('password');
        $passwordRepeated = $request->get('password_repeat');

        if (strlen($password) < 8) {
            return $this->createApiResponse([
                'message' => 'Password has to have at least 8 characters!',
            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
        }

        if ($password !== $passwordRepeated) {
            return $this->createApiResponse([
                'message' => 'Password and repeated password are not the same!',
            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
        }

        $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => 'Password was successfully changed!',
        ], StatusCodesHelper::SUCCESSFUL_CODE);

    }

    /**
     * @param User|null $user
     *
     * @param array $requestData
     *
     * @param bool $create
     *
     * @param $locationUrl
     * @return Response
     * @internal param $id
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    private function updateUser($user, array $requestData, $create = false,  $locationUrl):Response
    {
        $allowedUserEntityParams = [
            'username',
            'password',
            'email',
            'language',
            'image',
            'is_active'
        ];

        $alowedUserDetailDataParams = [
            'name',
            'surname',
            'title_before',
            'title_after',
            'function',
            'mobile',
            'tel',
            'fax',
            'signature',
            'street',
            'city',
            'zip',
            'country',
            'facebook',
            'twitter',
            'linkdin',
            'google'
        ];

        // JSON API Response - Content type and Location settings
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Location', $locationUrl);

        $requestDetailData = [];
        if (isset($requestData['detail_data']) && count($requestData['detail_data']) > 0) {
            $requestDetailData = $requestData['detail_data'];
            unset($requestData['detail_data']);
        }

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        // Set is_active param
        if (array_key_exists('is_active', $requestData)) {
            $isActive = strtolower($requestData['is_active']);
            unset($requestData['is_active']);
            if ('true' === $isActive || true === $isActive || '1' === $isActive || 1 === $isActive) {
                $user->setIsActive(true);
            } elseif ('false' === $isActive || false === $isActive || '0' === $isActive || 0 === $isActive) {
                $user->setIsActive(false);
            }
        }

        foreach ($requestData as $key => $value) {
            if (!\in_array($key, $allowedUserEntityParams, true)) {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for User Entity!']));
                return $response;
            }
        }

        foreach ($requestDetailData as $key => $value) {
            if (!\in_array($key, $alowedUserDetailDataParams, true)) {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for Detail Data in User Entity!']));
                return $response;
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        if (null === $user || !$user instanceof User) {
            return $this->notFoundResponse();
        }

        $errors = $this->get('entity_processor')->processEntity($user, $requestData);

        if (false === $errors) {
            if (isset($requestData['password'])) {
                $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $requestData['password']));
            }

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            $userCompany = $user->getCompany();
            if ($userCompany instanceof Company) {
                $userCompanyId = $userCompany->getId();
            } else {
                $userCompanyId = false;
            }

            if ($create) {
                // Every New USER has ACL VIEW_OWN_TASKS to INBOX project
                $inboxProject = $this->getDoctrine()->getRepository('APITaskBundle:Project')->findOneBy([
                    'title' => 'INBOX'
                ]);

                if (null !== $inboxProject) {
                    $acl = [];
                    $acl[] = ProjectAclOptions::VIEW_OWN_TASKS;
                    $userHasProject = new UserHasProject();
                    $userHasProject->setUser($user);
                    $userHasProject->setProject($inboxProject);
                    $userHasProject->setAcl($acl);
                    $this->getDoctrine()->getManager()->persist($userHasProject);
                    $this->getDoctrine()->getManager()->flush();
                }
            }

            $userId = $user->getId();
            $ids = [
                'userId' => $userId,
                'userRoleId' => $user->getUserRole()->getId(),
                'userCompanyId' => $userCompanyId
            ];

            /**
             * Fill UserData Entity if some of its parameters were sent
             */
            if ($requestDetailData) {
                $userData = $this->getDoctrine()->getRepository('APICoreBundle:UserData')->findOneBy([
                    'user' => $userId
                ]);

                if (null === $userData) {
                    $userData = new UserData();
                    $userData->setUser($user);
                    $user->setDetailData($userData);
                }
                $errorsUserData = $this->get('entity_processor')->processEntity($userData, $requestDetailData);
                if (false === $errorsUserData) {
                    $this->getDoctrine()->getManager()->persist($userData);
                    $this->getDoctrine()->getManager()->flush();

                    $userArray = $this->get('api_user.service')->getUserResponse($ids);
                    $userRoles = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->getAllowedUserRoles($user->getUserRole()->getOrder());
                    $allowedRolesArray['allowedUserRoles'] = [$userRoles];

                    $response = $response->setStatusCode($statusCode);
                    $response = $response->setContent(json_encode(array_merge($userArray, $allowedRolesArray)));
                    return $response;
                } else {
                    $data = [
                        'errors' => $errorsUserData,
                        'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
                    ];
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode($data));
                    return $response;
                }
            } else {
                $userArray = $this->get('api_user.service')->getUserResponse($ids);
                $userRoles = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->getAllowedUserRoles($user->getUserRole()->getOrder());
                $allowedRolesArray['allowedUserRoles'] = [$userRoles];

                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode(array_merge($userArray, $allowedRolesArray)));
                return $response;
            }
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
        $response = $response->setContent(json_encode($data));
        return $response;
    }
}
