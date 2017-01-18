<?php

namespace API\CoreBundle\Services;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class userService
 * @package API\CoreBundle\Services
 */
class UserService
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
     * @param int $page
     *
     * @param string $isActive
     * @return array
     */
    public function getUsersResponse(array $fields, int $page, $isActive)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository('APICoreBundle:User');
        $users = $userRepository->getCustomUsers($fields, $page, $isActive);

        $response = [
            'data' => $users,
        ];
        $pagination = HateoasHelper::getPagination(
            $this->router->generate('users_list'),
            $page,
            $userRepository->countUsers($isActive),
            UserRepository::LIMIT,
            $fields,
            $isActive
        );

        return array_merge($response, $pagination);
    }

    /**
     * Return User Response which includes all data about User Entity and Links to update/partialUpdate/delete
     *
     * @param User $user
     *
     * @return array
     */
    public function getUserResponse(User $user)
    {
        return [
            'data' => $user,
            '_links' => $this->getUserLinks($user->getId()),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getUserLinks(int $id)
    {
        return [
            'put' => $this->router->generate('user_update', ['id' => $id]),
            'patch' => $this->router->generate('user_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('user_delete', ['id' => $id]),
        ];
    }
}