<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Model\BaseModel;
use API\CoreBundle\Services\ErrorHelper;
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
     * @ApiDoc(
     *  description="Returns a list of Users with selected detail Info (user Entity, UserData Entity), you can pass in
     *  a fields option to get custom data", statusCodes={
     *      200="Returned when successful",
     *  },
     *  parameters={
     *      {"name"="fields", "dataType"="string", "required"=false, "format"="GET",
     *       "description"="custom fields to get only selected data"},
     *      {"name"="page", "dataType"="string", "required"=false, "format"="GET",
     *       "description"="Pagination, limit is set to 10 records"}
     *  },
     *  headers={
     *     {
     *       "name"="X-AUTHORIZE-KEY",
     *       "required"=true,
     *       "description"="JWT Token"
     *     }
     *  }
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function listUsersAction(Request $request)
    {
        $userModel = $this->get('api_user.model');
        $fields = $request->get('fields') ? explode(',' , $request->get('fields')) : [];
        $page = $request->get('page') ?: 1;

        return $this->json($userModel->getUsersResponse($fields , $page));
    }

    /**
     * @ApiDoc(
     *  description="Returns User with selected detail Info(user Entity, UserData Entity)",
     *  statusCodes={
     *      200="Returned when successful",
     *  })
     *
     * @param int     $id
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getUserAction(int $id , Request $request)
    {
        $userModel = $this->get('api_user.model');
        $fields = $request->get('fields') ? explode(',' , $request->get('fields')) : [];

        return $this->json($userModel->getCustomUser($id , $fields));
    }

    /**
     * @ApiDoc(
     *  description="Create new User Entity",
     *  statusCodes={
     *      201="Returned when successful",
     *      409="Returned when inserted data are not valid",
     *  })
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \LogicException
     */
    public function createUserAction(Request $request)
    {
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

            return $this->json($this->get('api_user.model')->getCustomUser($user->getId()) , 201);
        }

        return $this->json(['errors' => $errors] , 409);

//
//        BaseModel::fillEntity(User::class , $user , $request->request->all());
//        $validator = $this->get('validator');
//        $errors = $validator->validate($user);
//
//        if (count($errors) > 0) {
//            return $this->json(['errors' => ErrorHelper::getErrorsFromValidation($errors)] , 409);
//        }
//        $this->getDoctrine()->getManager()->persist($user);
//        $this->getDoctrine()->getManager()->flush();
//
//        return $this->json($this->get('api_user.model')->getCustomUser($user->getId()) , 201);
    }

    /**
     * @ApiDoc(
     *  description="Update All User Entity data",
     *  statusCodes={
     *      200="Returned when successful",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function updateUserAction($id)
    {

    }

    /**
     * @ApiDoc(
     *  description="Update Selected User Entity data",
     *  statusCodes={
     *      200="Returned when successful",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function updatePartialUserAction($id)
    {

    }

    /**
     * @ApiDoc(
     *  description="Delete User Entity",
     *  statusCodes={
     *      200="Returned when successful",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteUserAction($id)
    {

    }
}
