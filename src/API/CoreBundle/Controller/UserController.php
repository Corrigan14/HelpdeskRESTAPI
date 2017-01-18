<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\CoreBundle\Security\VoteOptions;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
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
class UserController extends ApiBaseController implements ControllerInterface
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
     *            "acl": "[]"
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/core-bundle/users?page=1&fields=id,email,username",
     *           "first": "/api/v1/core-bundle/users?page=1&fields=id,email,username",
     *           "prev": false,
     *           "next": "/api/v1/core-bundle/users?page=2&fields=id,email,username",
     *            "last": "/api/v1/core-bundle/users?page=3&fields=id,email,username"
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function listAction(Request $request)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::LIST_USERS)) {
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
     *           "id": 46,
     *           "username": "admin",
     *           "email": "admin@admin.sk",
     *           "roles": "[\"ROLE_ADMIN\"]",
     *           "is_active": true,
     *           "acl": "[]"
     *           "detail_data":
     *           {
     *              "name": "Martina",
     *              "surname": "Kollar",
     *              "title_before": Mgr,
     *              "title_after": PhD,
     *              "function": "developer",
     *              "mobile": "00421 0987 544",
     *              "tel": 00421 0987 544,
     *              "fax": 00421 0987 544,
     *              "signature": "Martina Kollar, Web-Solutions",
     *              "street": "Nova 487",
     *              "city": "Bratislava",
     *              "zip": "025874",
     *              "country": "SR"
     *           },
     *          "company":
     *           {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/users/46",
     *           "patch": "/api/v1/core-bundle/users/46",
     *           "delete": "/api/v1/core-bundle/users/46"
     *        }
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
        if (!$this->get('user_voter')->isGranted(VoteOptions::SHOW_USER, $id)) {
            return $this->accessDeniedResponse();
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (null === $user) {

            return $this->notFoundResponse();

        }

        return $this->createApiResponse($this->get('api_user.service')->getUserResponse($user), StatusCodesHelper::SUCCESSFUL_CODE);

    }

    /**
     *  ### Response ###
     *      ▿{
     *         "data": ▿
     *         {
     *           "id": 12,
     *           "username": "admin",
     *           "email": "admin@admin.sk",
     *           "roles": "[\"ROLE_ADMIN\"]",
     *           "is_active": true,
     *           "acl": "[]"
     *           "detail_data":
     *           {
     *              "name": "Martina",
     *              "surname": "Kollar",
     *              "title_before": null,
     *              "title_after": null,
     *              "function": "developer",
     *              "mobile": "00421 0987 544",
     *              "tel": null,
     *              "fax": null,
     *              "signature": "Martina Kollar, Web-Solutions",
     *              "street": "Nova 487",
     *              "city": "Bratislava",
     *              "zip": "025874",
     *              "country": "SR"
     *           },
     *          "company":
     *           {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           }
     *         },
     *         "_links": ▿
     *         {
     *            "put": "/api/v1/core-bundle/users/12",
     *            "patch": "/api/v1/core-bundle/users/12",
     *            "delete": "/api/v1/core-bundle/users/12"
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
     * @param bool|int $companyId
     *
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request, $companyId = false)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::CREATE_USER)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $user = new User();
        //avatar
        $file = $request->files->get('image');
        if (null != $file) {
            $imageSlug = $this->get('upload_helper')->uploadFile($file, true);
            $user->setImage($imageSlug);
        }

        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(true);

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
     *           "id": 12,
     *           "username": "admin",
     *           "email": "admin@admin.sk",
     *           "roles": "[\"ROLE_ADMIN\"]",
     *           "is_active": true,
     *           "acl": "[]"
     *           "detail_data":
     *           {
     *              "name": "Martina",
     *              "surname": "Kollar",
     *              "title_before": null,
     *              "title_after": null,
     *              "function": "developer",
     *              "mobile": "00421 0987 544",
     *              "tel": null,
     *              "fax": null,
     *              "signature": "Martina Kollar, Web-Solutions",
     *              "street": "Nova 487",
     *              "city": "Bratislava",
     *              "zip": "025874",
     *              "country": "SR"
     *           },
     *          "company":
     *           {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           }
     *         },
     *         "_links": ▿
     *         {
     *            "put": "/api/v1/core-bundle/users/12",
     *            "patch": "/api/v1/core-bundle/users/12",
     *            "delete": "/api/v1/core-bundle/users/12"
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
     * @param int|bool $companyId
     * @param Request $request
     *
     * @return Response|JsonResponse
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */

    public function updateAction(int $id, Request $request, $companyId = false)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::UPDATE_USER, $id)) {
            return $this->accessDeniedResponse();
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        $requestData = $request->request->all();


        if ($user instanceof User && $companyId) {
            try {
                $this->setCompanyToUser($user, $companyId);
            } catch (\InvalidArgumentException $e) {
                return $this->notFoundResponse();
            }
        }

        return $this->updateUser($user, $requestData);

    }

    /**
     *  ### Response ###
     *      ▿{
     *         "data": ▿
     *         {
     *           "id": 12,
     *           "username": "admin",
     *           "email": "admin@admin.sk",
     *           "roles": "[\"ROLE_ADMIN\"]",
     *           "is_active": true,
     *           "acl": "[]"
     *           "detail_data":
     *           {
     *              "name": "Martina",
     *              "surname": "Kollar",
     *              "title_before": null,
     *              "title_after": null,
     *              "function": "developer",
     *              "mobile": "00421 0987 544",
     *              "tel": null,
     *              "fax": null,
     *              "signature": "Martina Kollar, Web-Solutions",
     *              "street": "Nova 487",
     *              "city": "Bratislava",
     *              "zip": "025874",
     *              "country": "SR"
     *           },
     *          "company":
     *           {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           }
     *         },
     *         "_links": ▿
     *         {
     *            "put": "/api/v1/core-bundle/users/12",
     *            "patch": "/api/v1/core-bundle/users/12",
     *            "delete": "/api/v1/core-bundle/users/12"
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
     * @param int|bool $companyId
     * @param Request $request
     *
     * @return Response|JsonResponse
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */

    public function updatePartialAction(int $id, Request $request, $companyId = false)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::UPDATE_USER, $id)) {
            return $this->accessDeniedResponse();

        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);

        $requestData = $request->request->all();


        if ($user instanceof User && $companyId) {
            try {
                $this->setCompanyToUser($user, $companyId);
            } catch (\InvalidArgumentException $e) {
                return $this->notFoundResponse();
            }
        }

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
        if (!$this->get('user_voter')->isGranted(VoteOptions::DELETE_USER, $id)) {
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
     *           "id": 12,
     *           "username": "admin",
     *           "email": "admin@admin.sk",
     *           "roles": "[\"ROLE_ADMIN\"]",
     *           "is_active": true,
     *           "acl": "[]"
     *           "detail_data":
     *           {
     *              "name": "Martina",
     *              "surname": "Kollar",
     *              "title_before": null,
     *              "title_after": null,
     *              "function": "developer",
     *              "mobile": "00421 0987 544",
     *              "tel": null,
     *              "fax": null,
     *              "signature": "Martina Kollar, Web-Solutions",
     *              "street": "Nova 487",
     *              "city": "Bratislava",
     *              "zip": "025874",
     *              "country": "SR"
     *           },
     *          "company":
     *           {
     *              "id": 1,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           }
     *         },
     *         "_links": ▿
     *         {
     *            "put": "/api/v1/core-bundle/users/12",
     *            "patch": "/api/v1/core-bundle/users/12",
     *            "delete": "/api/v1/core-bundle/users/12"
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
        if (!$this->get('user_voter')->isGranted(VoteOptions::DELETE_USER, $id)) {
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


            //var_dump($requestData);
            //upload avatar
            //$image = $this->get('upload_helper')->uploadFile($editForm->get('image')->getData());
            //$user->setImage($image);


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

                    return $this->createApiResponse($this->get('api_user.service')->getUserResponse($user), $statusCode);
                }
            } else {
                return $this->createApiResponse($this->get('api_user.service')->getUserResponse($user), $statusCode);
            }
        }

        return $this->invalidParametersResponse();

    }

    /**
     * @param User $user
     * @param int $companyId
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function setCompanyToUser(User $user, int $companyId)
    {

        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($companyId);

        if (!$company instanceof Company) {
            throw new \InvalidArgumentException('Company is not type of Company');
        }
        $user->setCompany($company);

    }
}
