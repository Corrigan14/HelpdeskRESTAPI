<?php

namespace API\CoreBundle\Security;

use API\CoreBundle\Entity\User;

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
     * @param string   $action
     *
     * @param bool|int $targetUserId
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isGranted($action , $targetUserId = false)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::CREATE_USER:
                return $this->canCreate();
            case VoteOptions::SHOW_USER:
                return $this->canRead($targetUserId);
            case VoteOptions::UPDATE_USER:
                return $this->canUpdate($targetUserId);
            case VoteOptions::DELETE_USER:
                return $this->canDelete($targetUserId);
            default:
                return false;
        }
    }

    /**
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canCreate(): bool
    {
        if ($this->decisionManager->decide($this->token , ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::CREATE_USER, $this->user);
    }

    /**
     * @param int $user
     *
     * @return bool
     * @throws \InvalidArgumentException*
     */
    private function canRead(int $user): bool
    {
        if ($this->decisionManager->decide($this->token , ['ROLE_ADMIN'])) {
            return true;
        }
        if ($user === $this->user->getId()) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::SHOW_USER, $this->user);
    }

    /**
     * @param int $user
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canUpdate(int $user): bool
    {
        if ($this->decisionManager->decide($this->token , ['ROLE_ADMIN'])) {
            return true;
        }
        if ($user === $this->user->getId()) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::UPDATE_USER, $this->user);
    }

    /**
     * @param int $user
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canDelete(int $user): bool
    {
        // Only ADMIN can delete user, he can't delete himselves
        if ($this->decisionManager->decide($this->token , ['ROLE_ADMIN']) && $user !== $this->user->getId()) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::DELETE_USER, $this->user);
    }

}