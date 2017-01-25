<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\UserRole;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class AclHelper
 *
 * @package API\TaskBundle\Services
 */
class AclHelper
{
    /** @var EntityManager */
    protected $em;

    /** @var AccessDecisionManagerInterface */
    protected $decisionManager;

    /** @var TokenInterface */
    protected $token;

    /**
     * ApiBaseService constructor.
     * @param EntityManager $em
     * @param AccessDecisionManagerInterface $decisionManager
     * @param TokenStorage $tokenStorage
     */
    public function __construct(EntityManager $em, AccessDecisionManagerInterface $decisionManager, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->decisionManager = $decisionManager;
        $this->token = $tokenStorage->getToken();
    }

    /**
     * Check if logged user has Admin ROLE
     *
     * @return bool
     */
    public function isAdmin():bool
    {
        return $this->decisionManager->decide($this->token, ['ROLE_ADMIN']);
    }

    /**
     * @param array $options
     * @return bool
     */
    public function roleHasACL(array $options):bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $acl = $options['acl'];
        /** @var User $user */
        $user = $options['user'];
        $userRole = $user->getUserRole();

        if ($userRole instanceof UserRole) {
            $userRoleHasAcl = $userRole->getAcl();
            if (in_array($acl, $userRoleHasAcl)) {
                return true;
            }
            return false;
        }

        return false;
    }
}