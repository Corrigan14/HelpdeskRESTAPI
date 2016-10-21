<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;

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

        return $this->json([
            'data' => $userModel->getCustomUser($id , $fields) ,
        ]);
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
     */
    public function createUserAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserType::class , $user);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            /** @var PasswordEncoder $encoder */
            $encoder = $this->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user , $plainPassword);

            $user->setPassword($encoded);
            $user->setIsActive(true);
            $user->setRoles(['ROLE_USER']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return new JsonResponse($user->getId() , 201);
        } else {
            $errors = $this->getErrorsFromForm($form);

            return new JsonResponse($errors , 409);
        }
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

    /**
     * @param Form $form
     *
     * @return array
     */
    private function getErrorsFromForm(Form $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof Form) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

//    public function postUserAction(Request $request)
//    {
//        $body = $request->getContent();
//        $data = json_decode($body, true);
//
//        $user = new User();
//        $form = $this->createForm(UserType::class,$user);
//        $form->submit($data);
//
//        if($form->isValid()){
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($user);
//            $em->flush();
//        }
//
//        return new Response('It worked. Believe me - I\'m an API', 201);
//    }

}