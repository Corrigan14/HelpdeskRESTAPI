<?php

namespace API\CoreBundle\Controller;

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
     *            "id": "1",
     *            "email": "admin@admin.sk",
     *            "username": "admin",
     *            "roles": "[\"ROLE_ADMIN\"]",
     *            "is_active": true,
     *            "detailData":
     *            {
     *               "id": 4,
     *               "name": "Martinka",
     *               "surname": "Babinska",
     *               "title_before": null,
     *               "title_after": null,
     *               "function": null,
     *               "mobile": null,
     *               "tel": null,
     *               "fax": null,
     *               "signature": null,
     *               "street": null,
     *               "city": null,
     *               "zip": null,
     *               "country": null
     *            },
     *            "user_role":
     *            {
     *               "id": 2,
     *               "title": "MANAGER",
     *               "description": null,
     *               "homepage": "/",
     *               "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *               "is_active": true
     *               "order": 2
     *            }
     *          },
     *          {
     *            "id": 69,
     *            "username": "manager",
     *            "password": "$2y$13$Ki4oUBYQ0/4eJSluQ.hGyucdHtmWqPI10tl6tqbUF/2iMxWi3CLZy",
     *            "email": "manager@manager.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "image": null,
     *            "detailData": null,
     *            "user_role":
     *            {
     *              "id": 26,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *              "is_active": true,
     *              "order": 2
     *            },
     *            "company":
     *            {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true,
     *              "companyData": {
     *              {
     *                 "id": 44,
     *                 "value": "data val",
     *                 "companyAttribute":
     *                 {
     *                    "id": 1,
     *                    "title": "input company additional attribute",
     *                    "type": "input",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                "id": 45,
     *                "value": "data valluesgyda gfg",
     *                "companyAttribute":
     *                {
     *                  "id": 2,
     *                  "title": "select company additional attribute",
     *                  "type": "simple_select",
     *                  "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                  "is_active": true
     *                }
     *              }
     *            }
     *         }
     *       ]
     *       "_links":
     *       {
     *           "self": "/api/v1/core-bundle/users?page=1&fields=id,email,username",
     *           "first": "/api/v1/core-bundle/users?page=1&fields=id,email,username",
     *           "prev": false,
     *           "next": "/api/v1/core-bundle/users?page=2&fields=id,email,username",
     *           "last": "/api/v1/core-bundle/users?page=3&fields=id,email,username"
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
     *       "name"="fields",
     *       "description"="Custom fields to get only selected data, see options in list of parameters"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE users if this param is TRUE, only INACTIVE users if param is FALSE"
     *     },
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function listAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $fields = $request->get('fields') ? explode(',', $request->get('fields')) : [];
        $page = $request->get('page') ?: 1;
        $isActive = $request->get('isActive') ?: 'all';

        return $this->json($this->get('api_user.service')->getUsersResponse($fields, $page, $isActive), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": 69,
     *            "username": "manager",
     *            "password": "$2y$13$Ki4oUBYQ0/4eJSluQ.hGyucdHtmWqPI10tl6tqbUF/2iMxWi3CLZy",
     *            "email": "manager@manager.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "image": null,
     *            "detailData":
     *            {
     *               "id": 4,
     *               "name": "Martinka",
     *               "surname": "Babinska",
     *               "title_before": null,
     *               "title_after": null,
     *               "function": null,
     *               "mobile": null,
     *               "tel": null,
     *               "fax": null,
     *               "signature": null,
     *               "street": null,
     *               "city": null,
     *               "zip": null,
     *               "country": null
     *            },
     *            "user_role":
     *            {
     *              "id": 26,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *              "is_active": true,
     *              "order": 2
     *            },
     *            "company":
     *            {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true,
     *              "companyData": {
     *              {
     *                 "id": 44,
     *                 "value": "data val",
     *                 "companyAttribute":
     *                 {
     *                    "id": 1,
     *                    "title": "input company additional attribute",
     *                    "type": "input",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                "id": 45,
     *                "value": "data valluesgyda gfg",
     *                "companyAttribute":
     *                {
     *                  "id": 2,
     *                  "title": "select company additional attribute",
     *                  "type": "simple_select",
     *                  "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                  "is_active": true
     *                }
     *              }
     *            }
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
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns User (user Entity)",
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
     * @return Response|JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            return $this->notFoundResponse();
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
        return $this->json($userArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      ▿{
     *         "data": ▿
     *         {
     *          "id": 69,
     *            "username": "manager",
     *            "password": "$2y$13$Ki4oUBYQ0/4eJSluQ.hGyucdHtmWqPI10tl6tqbUF/2iMxWi3CLZy",
     *            "email": "manager@manager.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "image": null,
     *            "detailData":
     *            {
     *               "id": 4,
     *               "name": "Martinka",
     *               "surname": "Babinska",
     *               "title_before": null,
     *               "title_after": null,
     *               "function": null,
     *               "mobile": null,
     *               "tel": null,
     *               "fax": null,
     *               "signature": null,
     *               "street": null,
     *               "city": null,
     *               "zip": null,
     *               "country": null
     *            },
     *            "user_role":
     *            {
     *              "id": 26,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *              "is_active": true,
     *              "order": 2
     *            },
     *            "company":
     *            {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true,
     *              "companyData": {
     *              {
     *                 "id": 44,
     *                 "value": "data val",
     *                 "companyAttribute":
     *                 {
     *                    "id": 1,
     *                    "title": "input company additional attribute",
     *                    "type": "input",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                "id": 45,
     *                "value": "data valluesgyda gfg",
     *                "companyAttribute":
     *                {
     *                  "id": 2,
     *                  "title": "select company additional attribute",
     *                  "type": "simple_select",
     *                  "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                  "is_active": true
     *                }
     *              }
     *            }
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
     *         }
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAction(Request $request, int $userRoleId, $companyId = false)
    {
        // Check if user has permission to CRUD User entity
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];
        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $user = new User();
        $user->setIsActive(true);

        // Upload and save avatar
        $file = $request->files->get('image');
        if (null !== $file) {
            $imageSlug = $this->get('upload_helper')->uploadFile($file, true);
            $user->setImage($imageSlug);
        }

        if ($userRoleId) {
            try {
                $this->setUserRoleToUser($user, $userRoleId);
            } catch (\InvalidArgumentException $e) {
                return $this->notFoundResponse();
            }
        }

        if ($companyId) {
            try {
                $this->setCompanyToUser($user, $companyId);
            } catch (\InvalidArgumentException $e) {
                return $this->notFoundResponse();
            }
        }

        return $this->updateUser($user, $requestData, true);
    }

    /**
     *  ### Response ###
     *      ▿{
     *         "data": ▿
     *         {
     *           "id": 69,
     *            "username": "manager",
     *            "password": "$2y$13$Ki4oUBYQ0/4eJSluQ.hGyucdHtmWqPI10tl6tqbUF/2iMxWi3CLZy",
     *            "email": "manager@manager.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "image": null,
     *            "detailData":
     *            {
     *               "id": 4,
     *               "name": "Martinka",
     *               "surname": "Babinska",
     *               "title_before": null,
     *               "title_after": null,
     *               "function": null,
     *               "mobile": null,
     *               "tel": null,
     *               "fax": null,
     *               "signature": null,
     *               "street": null,
     *               "city": null,
     *               "zip": null,
     *               "country": null
     *            },
     *            "user_role":
     *            {
     *              "id": 26,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *              "is_active": true,
     *              "order": 2
     *            },
     *            "company":
     *            {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true,
     *              "companyData": {
     *              {
     *                 "id": 44,
     *                 "value": "data val",
     *                 "companyAttribute":
     *                 {
     *                    "id": 1,
     *                    "title": "input company additional attribute",
     *                    "type": "input",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                "id": 45,
     *                "value": "data valluesgyda gfg",
     *                "companyAttribute":
     *                {
     *                  "id": 2,
     *                  "title": "select company additional attribute",
     *                  "type": "simple_select",
     *                  "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                  "is_active": true
     *                }
     *              }
     *            }
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
     *         }
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
     *      404 ="Not found user",
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
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];
        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if ($userRoleId) {
            try {
                $this->setUserRoleToUser($user, $userRoleId);
            } catch (\InvalidArgumentException $e) {
                return $this->notFoundResponse();
            }
        }

        if ($companyId) {
            try {
                $this->setCompanyToUser($user, $companyId);
            } catch (\InvalidArgumentException $e) {
                return $this->notFoundResponse();
            }
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
     *  ### Response ###
     *      ▿{
     *         "data": ▿
     *         {
     *           "id": 69,
     *            "username": "manager",
     *            "password": "$2y$13$Ki4oUBYQ0/4eJSluQ.hGyucdHtmWqPI10tl6tqbUF/2iMxWi3CLZy",
     *            "email": "manager@manager.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "image": null,
     *            "detailData":
     *            {
     *               "id": 4,
     *               "name": "Martinka",
     *               "surname": "Babinska",
     *               "title_before": null,
     *               "title_after": null,
     *               "function": null,
     *               "mobile": null,
     *               "tel": null,
     *               "fax": null,
     *               "signature": null,
     *               "street": null,
     *               "city": null,
     *               "zip": null,
     *               "country": null
     *            },
     *            "user_role":
     *            {
     *              "id": 26,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *              "is_active": true,
     *              "order": 2
     *            },
     *            "company":
     *            {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true,
     *              "companyData": {
     *              {
     *                 "id": 44,
     *                 "value": "data val",
     *                 "companyAttribute":
     *                 {
     *                    "id": 1,
     *                    "title": "input company additional attribute",
     *                    "type": "input",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                "id": 45,
     *                "value": "data valluesgyda gfg",
     *                "companyAttribute":
     *                {
     *                  "id": 2,
     *                  "title": "select company additional attribute",
     *                  "type": "simple_select",
     *                  "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                  "is_active": true
     *                }
     *              }
     *            }
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
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the User",
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
     *      404 ="Not found user",
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
    public function updatePartialAction(int $id, Request $request, $userRoleId = false, $companyId = false)
    {
        // Check if user has permission to CRUD User entity
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];
        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if ($userRoleId) {
            try {
                $this->setUserRoleToUser($user, $userRoleId);
            } catch (\InvalidArgumentException $e) {
                return $this->notFoundResponse();
            }
        }

        if ($companyId) {
            try {
                $this->setCompanyToUser($user, $companyId);
            } catch (\InvalidArgumentException $e) {
                return $this->notFoundResponse();
            }
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
     *           "id": 69,
     *            "username": "manager",
     *            "password": "$2y$13$Ki4oUBYQ0/4eJSluQ.hGyucdHtmWqPI10tl6tqbUF/2iMxWi3CLZy",
     *            "email": "manager@manager.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "image": null,
     *            "detailData":
     *            {
     *               "id": 4,
     *               "name": "Martinka",
     *               "surname": "Babinska",
     *               "title_before": null,
     *               "title_after": null,
     *               "function": null,
     *               "mobile": null,
     *               "tel": null,
     *               "fax": null,
     *               "signature": null,
     *               "street": null,
     *               "city": null,
     *               "zip": null,
     *               "country": null
     *            },
     *            "user_role":
     *            {
     *              "id": 26,
     *              "title": "MANAGER",
     *              "description": null,
     *              "homepage": "/",
     *              "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *              "is_active": true,
     *              "order": 2
     *            },
     *            "company":
     *            {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true,
     *              "companyData": {
     *              {
     *                 "id": 44,
     *                 "value": "data val",
     *                 "companyAttribute":
     *                 {
     *                    "id": 1,
     *                    "title": "input company additional attribute",
     *                    "type": "input",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                "id": 45,
     *                "value": "data valluesgyda gfg",
     *                "companyAttribute":
     *                {
     *                  "id": 2,
     *                  "title": "select company additional attribute",
     *                  "type": "simple_select",
     *                  "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                  "is_active": true
     *                }
     *              }
     *            }
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
     *         }
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

        return $this->createApiResponse($this->get('api_user.service')->getUserResponse($user), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param User|null $user
     *
     * @param array $requestData
     *
     * @param bool $create
     *
     * @return Response|JsonResponse
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @internal param $id
     */
    private function updateUser($user, array $requestData, $create = false)
    {
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
            $ids = [
                'userId' => $user->getId(),
                'userRoleId' => $user->getUserRole()->getId(),
                'userCompanyId' => $userCompanyId
            ];
            //upload avatar
//            $image = $this->get('upload_helper')->uploadFile($editForm->get('image')->getData());
//            $user->setImage($image);

            /**
             * Fill UserData Entity if some its parameters were sent
             */
            if (isset($requestData['detail_data']) && count($requestData['detail_data']) > 0) {

                $userData = $user->getDetailData();
                if (null === $userData) {
                    $userData = new UserData();
                    $userData->setUser($user);
                    $user->setDetailData($userData);
                }

                $errorsUserData = $this->get('entity_processor')->processEntity($userData, $requestData['detail_data']);

                if (false === $errorsUserData) {
                    $this->getDoctrine()->getManager()->persist($userData);
                    $this->getDoctrine()->getManager()->flush();

                    $userArray = $this->get('api_user.service')->getUserResponse($ids);
                    return $this->json($userArray, $statusCode);
                }
            } else {
                $userArray = $this->get('api_user.service')->getUserResponse($ids);
                return $this->json($userArray, $statusCode);
            }
        }

        return $this->createApiResponse($errors, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param User $user
     * @param int $companyId
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @return Response
     */
    private function setCompanyToUser(User $user, int $companyId)
    {
        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($companyId);

        if (!$company instanceof Company) {
            return $this->createApiResponse([
                'message' => 'Company with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }
        $user->setCompany($company);
    }

    /**
     * @param User $user
     * @param int $userRoleId
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @return Response
     */
    private function setUserRoleToUser(User $user, int $userRoleId)
    {
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
    }
}
