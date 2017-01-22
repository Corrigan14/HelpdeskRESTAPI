<?php

namespace API\CoreBundle\Security;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\UserRole;

/**
 * Class UserVoter
 *
 * @package API\CoreBundle\Security
 */
class UserVoter extends ApiBaseVoter implements VoterInterface
{
    /** @var  User */
    private $user;

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $action
     *
     * @param $options
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isGranted($action, $options)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::CREATE_USER_WITH_USER_ROLE:
                return $this->canCreateUserWithSelectedUserRole($options);
            default:
                return false;
        }
    }

    /**
     * @param array $options
     * @return bool
     */
    private function canCreateUserWithSelectedUserRole(array $options): bool
    {
        // Admin can create user with any User Role
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can create just user with role, which order is higher like his role order
        /** @var UserRole $userRole */
        $requestedUserRole = $options['userRole'];
        /** @var UserRole $loggedUserUserRole */
        $loggedUserUserRole = $this->user->getUserRole();

        if ($loggedUserUserRole->getOrder() < $requestedUserRole->getOrder()) {
            return true;
        }

        return false;
    }

}