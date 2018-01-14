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
        $responseData = $this->em->getRepository('APITaskBundle:UserRole')->getAllEntities($page, $options);

        $response['data'] = $responseData['array'];

        $url = $this->router->generate('user_role_list');
        $limit = $options['limit'];
        $filters = $options['filtersForUrl'];

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getUserRoleResponse(int $id): array
    {
        $userRole = $this->em->getRepository('APITaskBundle:UserRole')->getEntity($id);

        return [
            'data' => $userRole,
            '_links' => $this->getFilterLinks($id),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getFilterLinks(int $id):array
    {
        return [
            'put' => $this->router->generate('user_role_update', ['id' => $id]),
            'inactivate' => $this->router->generate('user_role_inactivate', ['id' => $id]),
            'restore' => $this->router->generate('user_role_restore', ['id' => $id]),
        ];
    }
}