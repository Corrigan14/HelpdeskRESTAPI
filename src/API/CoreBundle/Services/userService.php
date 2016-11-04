<?php

namespace API\CoreBundle\Services;

use API\CoreBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class userService
 * @package API\CoreBundle\Services
 */
class userService
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;


    /**
     * UserService constructor.
     *
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * Return Users Response  which includes Data and Links and Pagination
     *
     * @param array $fields
     * @param int   $page
     *
     * @return array
     */
    public function getUsersResponse(array $fields , int $page)
    {
        if (0 === count($fields)) {
            $fields = UserRepository::DEFAULT_FIELDS;
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository('APICoreBundle:User');
        $users = $userRepository->getCustomUsers($fields , $page);

        $response = [
            'data' => $users ,
        ];
        $pagination = HateoasHelper::getPagination(
            $this->router->generate('users_list') ,
            $page ,
            $userRepository->countUsers(),
            UserRepository::LIMIT ,
            $fields
        );

        return array_merge($response , $pagination);
    }
}