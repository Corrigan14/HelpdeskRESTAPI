<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Repository\UserRoleRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class UserRoleService
 *
 * @package API\TaskBundle\Services
 */
class UserRoleService
{
    /** @var EntityManager */
    protected $em;

    /** @var  Router */
    protected $router;

    /**
     * ApiBaseService constructor.
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * @param int $page
     * @param array $options
     * @return array
     */
    public function getUserRolesResponse(int $page, array $options):array
    {
        $data = $this->em->getRepository('APITaskBundle:UserRole')->getAllEntities($page, $options);
        $count = $this->em->getRepository('APITaskBundle:UserRole')->countEntities($options);

        $response = [
            'data' => $data,
        ];

        $url = $this->router->generate('user_role_list');
        $limit = UserRoleRepository::LIMIT;
        $filters = $options['filtersForUrl'];

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param UserRole $userRole
     * @return array
     */
    public function getUserRoleResponse(UserRole $userRole): array
    {
        return [
            'data' => $userRole,
            '_links' => $this->getFilterLinks($userRole->getId()),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getFilterLinks(int $id)
    {
        return [
            'put' => $this->router->generate('user_role_update', ['id' => $id]),
            'patch' => $this->router->generate('user_role_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('user_role_delete', ['id' => $id]),
        ];
    }
}