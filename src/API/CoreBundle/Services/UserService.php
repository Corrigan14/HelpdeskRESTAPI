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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
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
     * @param array $ids
     * @return array
     */
    public function getUserResponse(array $ids)
    {
        $userId = $ids['userId'];
        $user = $this->em->getRepository('APICoreBundle:User')->getUserResponse($userId);

        return [
            'data' => $user[0],
            '_links' => $this->getUserLinks($ids),
        ];
    }

    /**
     * @param array $ids
     * @return array
     */
    private function getUserLinks(array $ids)
    {
        $userId = $ids['userId'];
        $userRoleId = $ids['userRoleId'];
        $userCompanyId = $ids['userCompanyId'];

        if ($userCompanyId) {
            $linksForCompany = [
                'put: company' => $this->router->generate('user_update_with_company', ['id' => $userId, 'companyId' => $userCompanyId]),
                'put: user-role & company' => $this->router->generate('user_update_with_company_and_user_role', ['id' => $userId, 'userRoleId' => $userRoleId, 'companyId' => $userCompanyId]),
                'patch: company' => $this->router->generate('user_partial_update_with_company', ['id' => $userId, 'companyId' => $userCompanyId]),
                'patch: user-role & company' => $this->router->generate('user_partial_update_with_company_and_user_role', ['id' => $userId, 'userRoleId' => $userRoleId, 'companyId' => $userCompanyId]),
            ];
        } else {
            $linksForCompany = [];
        }

        $otherLinks = [
            'put' => $this->router->generate('user_update', ['id' => $userId]),
            'put: user-role' => $this->router->generate('user_update_with_user_role', ['id' => $userId, 'userRoleId' => $userRoleId]),
            'patch' => $this->router->generate('user_partial_update', ['id' => $userId]),
            'patch: user-role' => $this->router->generate('user_partial_update_with_user_role', ['id' => $userId, 'userRoleId' => $userRoleId]),
            'delete' => $this->router->generate('user_delete', ['id' => $userId]),
            'restore' => $this->router->generate('user_restore', ['id' => $userId]),
        ];

        return array_merge($otherLinks, $linksForCompany);
    }
}