<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Model\BaseModel;
use API\CoreBundle\Services\ErrorHelper;
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
     * @ApiDoc(
     *  description="Returns a list of Users with selected detail Info (user Entity, UserData Entity), you can pass in a fields option to get custom data",
     *  statusCodes={
     *      200="The request has succeeded",
     *  },
     *  headers={
     *     {
     *       "name"="X-AUTHORIZE-KEY",
     *       "required"=true,
     *       "description"="JWT Token"
     *     }
     *  },
     *  filters={
     *     {
     *       "name"="fields",
     *       "description"="Custom fields to get only selected data"
     *     },
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
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

        return $this->json($userModel->getUsersResponse($fields , $page), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @ApiDoc(
     *  description="Returns User with selected detail Info(user Entity, UserData Entity)",
     *  statusCodes={
     *      200="The request has succeeded",
     *  },
     *  headers={
     *     {
     *       "name"="X-AUTHORIZE-KEY",
     *       "required"=true,
     *       "description"="JWT Token"
     *     }
     *  },
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  filters={
     *     {
     *       "name"="fields",
     *       "description"="Custom fields to get only selected data"
     *     },
     *  }
     *  )
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

        return $this->json($userModel->getCustomUser($id , $fields), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @ApiDoc(
     *  description="Create new User, UserData Entity",
     *  statusCodes={
     *      201="The entity was successfully created",
     *      409="Invalid parameters",
     *  },
     *  headers={
     *     {
     *       "name"="X-AUTHORIZE-KEY",
     *       "required"=true,
     *       "description"="JWT Token"
     *     }
     *  },
     *  output="API\CoreBundle\Entity\User",
     *  parameters={
     *     {"name"="username", "dataType"="string", "required"=true, "format" = "POST","description"="User name"},
     *     {"name"="email", "dataType"="string", "required"=true, "format" = "POST","description"="Email"},
     *     {"name"="password", "dataType"="string", "required"=true, "format" = "POST", "description"="At least 8 characters long password"},
     *     {"name"="name", "dataType"="string", "required"=false, "format" = "POST", "description"="Name"},
     *     {"name"="surname", "dataType"="string", "required"=false, "format" = "POST", "description"="Surname"},
     *     {"name"="title_before", "dataType"="string", "required"=false, "format" = "POST", "description"="Title Before name"},
     *     {"name"="title_after", "dataType"="string", "required"=false, "format" = "POST", "description"="Title After name"},
     *     {"name"="function", "dataType"="string", "required"=false, "format" = "POST", "description"="Function - possition of user"},
     *     {"name"="mobile", "dataType"="string", "required"=false, "format" = "POST", "description"="Mobile number"},
     *     {"name"="tel", "dataType"="string", "required"=false, "format" = "POST", "description"="Telephone number"},
     *     {"name"="fax", "dataType"="string", "required"=false, "format" = "POST", "description"="Fax number"},
     *     {"name"="signature", "dataType"="text", "required"=false, "format" = "POST", "description"="Signature format"},
     *     {"name"="street", "dataType"="string", "required"=false, "format" = "POST", "description"="Street (part of address)"},
     *     {"name"="city", "dataType"="string", "required"=false, "format" = "POST", "description"="City (part of address)"},
     *     {"name"="zip", "dataType"="string", "required"=false, "format" = "POST", "description"="Zip Code (part of address)"},
     *     {"name"="country", "dataType"="string", "required"=false, "format" = "POST", "description"="Country (part of address)"},
     *  },
     *  )
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

            return $this->json($this->get('api_user.model')->getCustomUser($user->getId()) , StatusCodesHelper::CREATED_CODE);
        }

        return $this->json(['message'=>StatusCodesHelper::INVALID_PARAMETERS_MESSAGE,'errors'=> $errors], StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @ApiDoc(
     *  description="Update All User Entity data",
     *  statusCodes={
     *      200="The request has succeeded",
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
     *      200="The request has succeeded",
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
     *      204="The entity was successfully deleted",
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
