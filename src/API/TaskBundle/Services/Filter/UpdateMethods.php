<?php

namespace API\TaskBundle\Services\Filter;


use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class UpdateMethods
 *
 * @package API\TaskBundle\Services
 */
class UpdateMethods
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;


    /**
     * ProjectService constructor.
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
     * Check if user can create PUBLIC filter (it's role has SHARE_FILTER ACL)
     *
     * @param array $data
     * @param bool $userRoleAclPermission
     * @return array
     */
    public function checkPublicFilterPermission(array $data, bool $userRoleAclPermission): array
    {
        if (!isset($data['public'])) {
            return [
                'public' => false
            ];
        }

        if (true === $data['public'] || 'true' === $data['public'] || 1 === (int)$data['public']) {
            if (!$userRoleAclPermission) {
                return [
                    'public' => false,
                    'error' => 'You have not permission to create a PUBLIC filter!'
                ];
            }

            return [
                'public' => true
            ];
        }

        return [
            'public' => false
        ];
    }
}