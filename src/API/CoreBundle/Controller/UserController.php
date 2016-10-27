<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\CoreBundle\Security\VoteOptions;
use API\CoreBundle\Services\StatusCodesHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UsersController
 *
 * @package API\CoreBundle\Controller
 */
class UserController extends Controller
{
    /**
     * ### Response ###
     *     {
     *       "data": [
     *                   {
     *                         "id": "1",
     *                         "email": "admin@admin.sk",
     *                         "username": "admin",
     *                         "_links": {
     *                             "self": "/users/1"
     *                         }
     *                 }
     *          ],
     *       "_links": {
     *             "self": "/users?page=1&fields=id,email,username",
     *             "first": "/users?page=1&fields=id,email,username",
     *             "prev": false,
     *             "next": "/users?page=2&fields=id,email,username",
     *             "last": "/users?page=3&fields=id,email,username"
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
     *      200="The request has succeeded",
     *  },
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function listUsersAction(Request $request)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::LIST_USERS)) {
            return $this->unauthorizedResponse();
        }

        $userModel = $this->get('api_user.model');
        $fields = $request->get('fields') ? explode(',' , $request->get('fields')) : [];
        $page = $request->get('page') ?: 1;

        return $this->json($userModel->getUsersResponse($fields , $page) , StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *          "data":     {
     *                          "id": "2",
     *                          "email": "admin@admin.sk",
     *                          "username": "admin"
     *                      },
     *          "_links":   {
     *                          "put": "/api/v1/users/2",
     *                          "patch": "/api/v1/users/2",
     *                          "delete": "/api/v1/users/2"
     *                      }
     *      }
     *
     * @ApiDoc(
     *  description="Returns User with selected detail Info(user Entity, UserData Entity)",
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
     *  output="API\CoreBundle\Entity\User",
     *  statusCodes={
     *      200="The request has succeeded",
     *  },
     *  )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getUserAction(int $id)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::SHOW_USER , $id)) {
            return $this->unauthorizedResponse();
        }
        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);

        return $this->json($this->get('api_user.model')->getCustomUserData($user) , StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      ▿{
     *          "data": ▿   {
     *                          "id": "12",
     *                          "username": "anitram547",
     *                          "roles": "a:1:{i:0;s:9:\"ROLE_USER\";}",
     *                          "email": "anitram@anitram.com",
     *                          "name": "Martina",
     *                          "surname": "Koronci Babinska",
     *                          "title_before": null,
     *                          "title_after": null,
     *                          "function": "developer",
     *                          "mobile": "00421 0987 544",
     *                          "tel": null,
     *                          "fax": null,
     *                          "signature": "Martina Koronci Babinska, WEB-SOLUTIONS",
     *                          "street": "Nova 487",
     *                          "city": "Bratislava",
     *                          "zip": "025874",
     *                          "country": "SR"
     *                      },
     *          "_links": ▿{
     *                      "put": "/api/v1/users/13",
     *                      "patch": "/api/v1/users/13",
     *                      "delete": "/api/v1/users/13"
     *                      }
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
     *      201="The entity was successfully created",
     *      409="Invalid parameters",
     *  }
     *  )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \LogicException
     */
    public function createUserAction(Request $request)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::CREATE_USER)) {
            return $this->unauthorizedResponse();
        }

        $requestData = $request->request->all();

        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(true);

        /**
         * For security reasons
         */
        unset($requestData['roles'] , $requestData['isActive']);

        $errors = $this->get('entity_processor')->processEntity($user , $requestData);
        if (false === $errors) {
            if ($requestData['password']) {
                $user->setPassword($this->get('security.password_encoder')->encodePassword($user , $requestData['password']));
            }

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            /**
             * Fill UserData Entity if some its parameters were sent
             */
            if (isset($requestData['detail_data']) && count($requestData['detail_data']) > 0) {
                $userData = new UserData();
                $user->setDetailData($userData);
                $errorsUserData = $this->get('entity_processor')->processEntity($userData , $requestData['detail_data']);

                if (false === $errorsUserData) {
                    $this->getDoctrine()->getManager()->persist($userData);
                    $this->getDoctrine()->getManager()->flush();

                    return $this->json($this->get('api_user.model')->getCustomUserData($user) , StatusCodesHelper::CREATED_CODE);
                }
            } else {
                return $this->json($this->get('api_user.model')->getCustomUserData($user) , StatusCodesHelper::CREATED_CODE);
            }
        }

        return $this->json(['message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE , 'errors' => $errors] , StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @ApiDoc(
     *  description="Update the Entire User",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200="The request has succeeded",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws \InvalidArgumentException
     */
    public function updateUserAction($id)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::UPDATE_USER , $id)) {
            return $this->unauthorizedResponse();
        }
    }

    /**
     * @ApiDoc(
     *  description="Partially update the User",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200="The request has succeeded",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws \InvalidArgumentException
     */
    public function updatePartialUserAction($id)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::UPDATE_USER , $id)) {
            return $this->unauthorizedResponse();
        }
    }

    /**
     * @ApiDoc(
     *  description="Delete User Entity",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      204="The User was successfully deleted",
     *      404="The User was not found",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function deleteUserAction($id)
    {
        if (!$this->get('user_voter')->isGranted(VoteOptions::DELETE_USER , $id)) {
            return $this->unauthorizedResponse();
        }
        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($id);
        if (null === $user) {
            return $this->json([
                'message' => StatusCodesHelper::USER_NOT_FOUND_CODE ,
            ] , StatusCodesHelper::USER_NOT_FOUND_MESSAGE);
        }

        $this->getDoctrine()->getManager()->remove($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->json([
            'message' => StatusCodesHelper::DELETED_MESSAGE ,
        ] , StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @return JsonResponse
     */
    private function unauthorizedResponse()
    {
        return $this->json([
            'message' => StatusCodesHelper::UNAUTHORIZED_MESSAGE ,
        ] , StatusCodesHelper::UNAUTHORIZED_CODE);
    }
}
