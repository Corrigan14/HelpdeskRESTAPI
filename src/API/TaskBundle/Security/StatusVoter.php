<?php

namespace API\TaskBundle\Security;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\ApiBaseVoter;
use API\CoreBundle\Security\VoterInterface;

/**
 * Class StatusVoter
 *
 * @package API\TaskBundle\Security
 */
class StatusVoter extends ApiBaseVoter implements VoterInterface
{
    /** @var  User */
    private $user;

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $action
     *
     * @param mixed $status
     *
     * @return bool
     */
    public function isGranted($action, $status = false)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::LIST_STATUSES:
                return $this->canList();
            case VoteOptions::SHOW_STATUS:
                return $this->canRead();
            case VoteOptions::CREATE_STATUS:
                return $this->canCreate();
            case VoteOptions::UPDATE_STATUS;
                return $this->canUpdate();
            case VoteOptions::DELETE_STATUS;
                return $this->canDelete();
            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    private function canList():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::LIST_STATUSES, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canRead():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::SHOW_STATUS, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canCreate():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::CREATE_STATUS, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canUpdate():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::UPDATE_STATUS, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canDelete():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::DELETE_STATUS, $this->user, VoteOptions::getConstants());
    }
}