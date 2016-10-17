<?php

namespace API\CoreBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoginController
 *
 * @package API\CoreBundle\Controller
 */
class LoginController extends Controller
{
    /**
     * @ApiDoc(
     *  description="Returns a JWT Token for authentication",
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user provided invalid credentials",
     *      404={"Returned when the user is not found"},
     *      405={"Incorrect method used"}
     *
     *  },
     *  parameters={
     *      {"name"="username", "dataType"="string", "required"=true, "format"="json", "description"="username for
     *      login purposes"},
     *      {"name"="password", "dataType"="string", "required"=true, "format"="json", "description"="password for
     *      login purposes"}
     *  }
     * )
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \LogicException
     */
    public function tokenAuthenticationAction(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')
                     ->findOneBy(['username' => $username]);

        if (!$user) {
            return $this->json(['error' => 'User not found'] , 404);
        }

        // password check
        if (!$this->get('security.password_encoder')->isPasswordValid($user , $password)) {
            return $this->json(['error' => 'Incorrect credentials'] , 403);
        }

        // Use LexikJWTAuthenticationBundle to create JWT token that hold only information about user name
        $token = $this->get('lexik_jwt_authentication.encoder.default')
                      ->encode(['username' => $user->getUsername()]);

        // Return genereted tocken
        return $this->json(['token' => $token]);
    }
}
