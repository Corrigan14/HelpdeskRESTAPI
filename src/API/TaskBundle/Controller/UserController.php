<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\TaskBundle\Security\VoteOptions;
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
 * Class UserController
 *
 * @package API\TaskBundle\Controller
 */
class UserController extends ApiBaseController
{
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
     *  description="Create new User, UserData Entity. User role is required, company is optional.",
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
        $userRole = $this->getDoctrine()->getRepository('APITaskBundle:UserRole')->find($userRoleId);

        if (!$userRole instanceof UserRole) {
            return $this->createApiResponse([
                'message' => 'User role with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user has permission to CRUD User entity
        $aclOptions = [
            'acl' => UserRoleAclOptions::USER_SETTINGS,
            'user' => $this->getUser()
        ];
        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        // Check if user can create User entity with requested User Role
        $voteOptions = [
            'userRole' => $userRole
        ];
        if (!$this->get('user_custom_voter')->isGranted(VoteOptions::CREATE_USER_WITH_USER_ROLE, $voteOptions)) {
            return $this->createApiResponse([
                'message' => 'You can not create user with selected User Role!',
            ], StatusCodesHelper::ACCESS_DENIED_CODE);
        }

        $requestData = $request->request->all();

        $user = new User();
        $user->setUserRole($userRole);
        $user->setIsActive(true);
        if ($userRole->getTitle() === 'ADMIN') {
            $user->setRoles(['ROLE_ADMIN']);
        } else {
            $user->setRoles(['ROLE_USER']);
        }

        // Upload and save avatar
        $file = $request->files->get('image');
        if (null !== $file) {
            $imageSlug = $this->get('upload_helper')->uploadFile($file, true);
            $user->setImage($imageSlug);
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
     * @throws \InvalidArgumentException
     * @throws \LogicException
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
}
