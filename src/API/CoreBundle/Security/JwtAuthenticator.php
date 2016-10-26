<?php

namespace API\CoreBundle\Security;

use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class JwtAuthenticator
 *
 * @package AppBundle\Security
 */
class JwtAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $jwtEncoder;

    public function __construct(EntityManager $em , DefaultEncoder $jwtEncoder)
    {
        $this->em = $em;
        $this->jwtEncoder = $jwtEncoder;
    }

    public function start(Request $request , AuthenticationException $authException = null)
    {
        return new JsonResponse('Auth header required' , 401);
    }

    public function getCredentials(Request $request)
    {

        if (!$request->headers->has('Authorization')) {
            return;
        }

        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer' ,
            'Authorization'
        );

        $token = $extractor->extract($request);

        if (!$token) {
            return;
        }

        return $token;
    }

    public function getUser($credentials , UserProviderInterface $userProvider)
    {
        /** @var array $data */
        $data = $this->jwtEncoder->decode($credentials);

        if (!$data) {
            return;
        }

        $username = $data['username'];

        $user = $this->em->getRepository('APICoreBundle:User')
                         ->findOneBy(['username' => $username]);

        if (!$user) {
            return;
        }

        return $user;
    }


    public function checkCredentials($credentials , UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request , AuthenticationException $exception)
    {
        return new JsonResponse([
            'message' => $exception->getMessage() ,
        ] , 401);
    }

    public function onAuthenticationSuccess(Request $request , TokenInterface $token , $providerKey)
    {
        return;
    }

    public function supportsRememberMe()
    {
        return false;
    }

}