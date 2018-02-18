<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\File;
use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\CoreBundle\Security\VoteOptions;
use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Controller\ApiBaseController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use API\CoreBundle\Entity\Company;
use Symfony\Component\Validator\Constraints\DateTime;

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
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('users_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

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

            $usersArray = $this->get('api_user.service')->getUsersResponse($page, $isActive, $order, $filtersForUrl, $limit);
            $response = $response->setContent(json_encode($usersArray));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::PROBLEM_WITH_FILTER_DATA_CODING]));
        }

        return $response;
    }


    /**
     * ### Response ###
     *     {
     *       "data":
     *       [
     *           {
     *              "id": 3,
     *              "username": "agent",
     *              "email": "agent@agent.sk",
     *              "name": null,
     *              "surname": null,
     *              "is_active": true
     *          },
     *          {
     *              "id": 4,
     *              "username": "agent2",
     *              "email": "agent2@agent.sk",
     *              "name": null,
     *              "is_active": true
     *          },
     *          {
     *              "id": 5,
     *              "username": "agent3",
     *              "email": "agent3@agent.sk",
     *              "name": null,
     *              "is_active": true
     *          }
     *       ]
     *       "date": 1518907522
     *     }
     *
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
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *  },
     * )
     *
     * @param string|bool $date
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function listOfAllUsersAction($date = false): Response
    {
        // JSON API Response - Content type and Location settings
        if (false !== $date && 'false' !== $date) {
            $intDate = (int)$date;
            if (is_int($intDate) && null !== $intDate) {
                $locationURL = $this->generateUrl('users_list_of_all_active_from_date', ['date' => $date]);
                $dateTimeObject = new \DateTime("@$date");
            } else {
                $locationURL = $this->generateUrl('users_list_of_all_active');
                $dateTimeObject = false;
            }
        } else {
            $locationURL = $this->generateUrl('users_list_of_all_active');
            $dateTimeObject = false;
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        if ($date && !($dateTimeObject instanceof \Datetime)) {
            $response = $response->setContent(['message' => 'Date parameter is not in a valid format! Expected format: Timestamp']);
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            return $response;
        }

        $allUsers = $this->get('api_user.service')->getListOfAllUsers($dateTimeObject);
        $currentDate = new \DateTime('UTC');
        $currentDateTimezone = $currentDate->getTimestamp();

        $dataArray = [
            'data' => $allUsers,
            'date' => $currentDateTimezone
        ];

        $response = $response->setContent(json_encode($dataArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
    }

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
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function searchAction(Request $request): Response
    {
        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('user_search');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        // Check the logged users ACL rights
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        // Filter params processing
        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];
            $isActive = $processedFilterParams['isActive'];
            $term = $processedFilterParams['term'];

            $filtersForUrl = [
                'isActive' => '&isActive=' . $isActive,
                'order' => '&order=' . $order,
                'term' => '&term=' . $term
            ];

            $usersArray = $this->get('api_user.service')->getUsersSearchResponse($term, $page, $isActive, $filtersForUrl, $order, $limit);
            $response = $response->setContent(json_encode($usersArray));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::PROBLEM_WITH_FILTER_DATA_CODING]));
        }

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
     *           "inactivate": "/api/v1/core-bundle/users/85",
     *           "restore": "/api/v1/core-bundle/users/85/restore",
     *           "put: company": "/api/v1/core-bundle/users/85/company/41",
     *           "put: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41"
     *         },
     *         "allowedUserRoles":
     *         [
     *            {
     *              "id": 22,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl":
     *              [
     *                  "login_to_system",
     *                  "create_tasks",
     *                  "create_projects",
     *                  "company_settings",
     *                  "report_filters",
     *                  "sent_emails_from_comments",
     *                  "update_all_tasks"
     *              ],
     *              "order": 2,
     *              "is_active": true
     *           },
     *           {
     *              "id": 23,
     *              "title": "AGENT",
     *              "description": null,
     *              "homepage": "/",
     *              "acl":
     *              [
     *                  "login_to_system",
     *                  "create_tasks",
     *                  "create_projects",
     *                  "company_settings",
     *                  "sent_emails_from_comments"
     *              ],
     *              "order": 3,
     *              "is_active": true
     *           }
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
        $locationURL = $this->generateUrl('user', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User with requested Id does not exist!']));
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
            'userId' => $id,
            'userRoleId' => $user->getUserRole()->getId(),
            'userCompanyId' => $userCompanyId
        ];

        $userArray = $this->get('api_user.service')->getUserResponse($ids);
        $userRoles = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->getAllowedUserRoles($user->getUserRole()->getOrder());
        $allowedRolesArray['allowedUserRoles'] = $userRoles;

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
     *           "inactivate": "/api/v1/core-bundle/users/85",
     *           "restore": "/api/v1/core-bundle/users/85/restore",
     *           "put: company": "/api/v1/core-bundle/users/85/company/41",
     *           "put: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41"
     *         },
     *         "allowedUserRoles":
     *         [
     *            {
     *              "id": 22,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl":
     *              [
     *                  "login_to_system",
     *                  "create_tasks",
     *                  "create_projects",
     *                  "company_settings",
     *                  "report_filters",
     *                  "sent_emails_from_comments",
     *                  "update_all_tasks"
     *              ],
     *              "order": 2,
     *              "is_active": true
     *           },
     *           {
     *              "id": 23,
     *              "title": "AGENT",
     *              "description": null,
     *              "homepage": "/",
     *              "acl":
     *              [
     *                  "login_to_system",
     *                  "create_tasks",
     *                  "create_projects",
     *                  "company_settings",
     *                  "sent_emails_from_comments"
     *              ],
     *              "order": 3,
     *              "is_active": true
     *           }
     *        ]
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new User. User has to have a USER ROLE. User can, but does not have to have a COMPANY",
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
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
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
        if ($companyId) {
            $locationURL = $this->generateUrl('user_create_with_company', ['userRoleId' => $userRoleId, 'companyId' => $companyId]);
        } else {
            $locationURL = $this->generateUrl('user_create', ['userRoleId' => $userRoleId]);
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

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
     *           "inactivate": "/api/v1/core-bundle/users/85",
     *           "restore": "/api/v1/core-bundle/users/85/restore",
     *           "put: company": "/api/v1/core-bundle/users/85/company/41",
     *           "put: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41"
     *         },
     *         "allowedUserRoles":
     *         [
     *            {
     *              "id": 22,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl":
     *              [
     *                  "login_to_system",
     *                  "create_tasks",
     *                  "create_projects",
     *                  "company_settings",
     *                  "report_filters",
     *                  "sent_emails_from_comments",
     *                  "update_all_tasks"
     *              ],
     *              "order": 2,
     *              "is_active": true
     *           },
     *           {
     *              "id": 23,
     *              "title": "AGENT",
     *              "description": null,
     *              "homepage": "/",
     *              "acl":
     *              [
     *                  "login_to_system",
     *                  "create_tasks",
     *                  "create_projects",
     *                  "company_settings",
     *                  "sent_emails_from_comments"
     *              ],
     *              "order": 3,
     *              "is_active": true
     *           }
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request, $userRoleId = false, $companyId = false): Response
    {
        // JSON API Response - Content type and Location settings
        if ($companyId && $userRoleId) {
            $locationURL = $this->generateUrl('user_update_with_company_and_user_role', ['id' => $id, 'userRoleId' => $userRoleId, 'companyId' => $companyId]);
        } elseif ($companyId) {
            $locationURL = $this->generateUrl('user_update_with_company', ['id' => $id, 'companyId' => $companyId]);
        } elseif ($userRoleId) {
            $locationURL = $this->generateUrl('user_update_with_user_role', ['id' => $id, 'userRoleId' => $userRoleId]);
        } else {
            $locationURL = $this->generateUrl('user_update', ['id' => $id]);
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        // Check if user has permission to CRUD User entity
        // User can update his own data
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

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User with requested Id does not exist!']));
            return $response;
        }

        if ($userRoleId && $this->getUser()->getId() !== $id) {
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
        } elseif ($userRoleId && $this->getUser()->getId() === $id) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => 'You can not change your own User Role!']));
            return $response;
        }

        if ($companyId && $this->getUser()->getId() !== $id) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($companyId);

            if (!$company instanceof Company) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Company with requested Id does not exist!']));
                return $response;
            }
            $user->setCompany($company);
        } elseif ($companyId && $this->getUser()->getId() === $id) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => 'You can not change your own Company!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateUser($user, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Inactivate User Entity",
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
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(int $id): Response
    {
        $locationURL = $this->generateUrl('user_delete', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

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

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User with requested Id does not exist!']));
            return $response;
        }

        $user->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => 'User was successfully inactivated!']));
        return $response;
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
     *           "inactivate": "/api/v1/core-bundle/users/85",
     *           "restore": "/api/v1/core-bundle/users/85/restore",
     *           "put: company": "/api/v1/core-bundle/users/85/company/41",
     *           "put: user-role & company": "/api/v1/core-bundle/users/85/user-role/32/company/41"
     *         },
     *         "allowedUserRoles":
     *         [
     *            {
     *              "id": 22,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl":
     *              [
     *                  "login_to_system",
     *                  "create_tasks",
     *                  "create_projects",
     *                  "company_settings",
     *                  "report_filters",
     *                  "sent_emails_from_comments",
     *                  "update_all_tasks"
     *              ],
     *              "order": 2,
     *              "is_active": true
     *           },
     *           {
     *              "id": 23,
     *              "title": "AGENT",
     *              "description": null,
     *              "homepage": "/",
     *              "acl":
     *              [
     *                  "login_to_system",
     *                  "create_tasks",
     *                  "create_projects",
     *                  "company_settings",
     *                  "sent_emails_from_comments"
     *              ],
     *              "order": 3,
     *              "is_active": true
     *           }
     *        ]
     *      }
     *
     * @ApiDoc(
     *  description="Restore (activate) User Entity",
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(int $id): Response
    {
        $locationURL = $this->generateUrl('user_restore', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

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

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User with requested Id does not exist!']));
            return $response;
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

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(array_merge($userArray, $allowedRolesArray)));
        return $response;
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
     *          {"name"="old_password", "dataType"="string", "required"=false, "format"="POST", "description"="Old password"},
     *          {"name"="new_password", "dataType"="string", "required"=true, "format"="POST", "description"="New password"},
     *          {"name"="new_password_repeat", "dataType"="string", "required"=true, "format"="POST", "description"="New password - repeat"}
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
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function resetPasswordAction(Request $request, int $id)
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('user_reset_password', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User with requested Id does not exist!']));
            return $response;
        }

        // Only admin can reset password - for all users
        if (!$this->get('acl_helper')->isAdmin() && $this->getUser()->getId() !== $id) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => 'Only admin can reset password of every user. Logged User can change his own password!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        if (false !== $requestBody) {
            if (isset($requestBody['new_password']) && isset($requestBody['new_password_repeat'])) {
                $password = $requestBody['new_password'];
                $passwordRepeated = $requestBody['new_password_repeat'];

                if (\strlen($password) < 8) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Password has to have at least 8 characters!']));
                    return $response;
                }

                if ($password !== $passwordRepeated) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Password and repeated password are not the same!']));
                    return $response;
                }
            } else {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'Problem with a new password and its repetition! Both parameters are required!']));
                return $response;
            }

            // Admin can restart everybody's password
            if ($this->get('acl_helper')->isAdmin()) {
                $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));
                $this->getDoctrine()->getManager()->persist($user);
                $this->getDoctrine()->getManager()->flush();

                $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
                $response = $response->setContent(json_encode(['message' => 'Password was successfully changed!']));
                return $response;
            } else {
                // Check the old password
                if (isset($requestBody['old_password'])) {
                    if ($this->get('security.password_encoder')->isPasswordValid($user, $requestBody['old_password'])) {
                        $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));
                        $this->getDoctrine()->getManager()->persist($user);
                        $this->getDoctrine()->getManager()->flush();

                        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
                        $response = $response->setContent(json_encode(['message' => 'Password was successfully changed!']));
                        return $response;
                    } else {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'OLD Password is not correct! Please contact ADMIN to reset your password!']));
                        return $response;
                    }
                } else {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'OLD Password is required!']));
                    return $response;
                }
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;
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
    private function updateUser($user, array $requestData, $create = false, $locationUrl): Response
    {
        $allowedUserEntityParams = [
            'username',
            'password',
            'email',
            'language',
            'image'
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
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);

        if (false !== $requestData) {
            $requestDetailData = [];
            if (isset($requestData['detail_data'])) {
                if (!\is_array($requestData['detail_data'])) {
                    $requestDetailData = json_decode($requestData['detail_data'], true);
                } else {
                    $requestDetailData = $requestData['detail_data'];
                }
                unset($requestData['detail_data']);
            } elseif (isset($requestData['detailData'])) {
                if (!\is_array($requestData['detailData'])) {
                    $requestDetailData = json_decode($requestData['detailData'], true);
                } else {
                    $requestDetailData = $requestData['detailData'];
                }
                unset($requestData['detailData']);
            }

            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
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

            //Check if IMAGE was already uploaded
            if (isset($requestData['image']) && 'null' !== strtolower($requestData['image'])) {
                $image = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
                    'slug' => $requestData['image']
                ]);
                if (!$image instanceof File) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Image with requested SLUG does not exist in DB! Image has to be UPLOADED by /api/v1/core-bundle/cdn/upload first!']));
                    return $response;
                }
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);
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

                    if (!$userData instanceof UserData) {
                        $userData = new UserData();
                        $userData->setUser($user);
                        $user->setDetailData($userData);
                    }
                    $errorsUserData = $this->get('entity_processor')->processEntity($userData, $requestDetailData);
                    if (false === $errorsUserData) {
                        $user->setUpdatedAt(new \DateTime('UTC'));
                        $this->getDoctrine()->getManager()->persist($userData);
                        $this->getDoctrine()->getManager()->persist($user);
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
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_SUPPORT]));
        }
        return $response;
    }
}
