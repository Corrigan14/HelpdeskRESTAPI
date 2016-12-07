<?php

namespace API\TaskBundle\Security;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\ApiBaseVoter;
use API\CoreBundle\Security\VoterInterface;
use API\TaskBundle\Entity\TaskAttribute;

/**
 * Class TaskAttributeVoter
 *
 * @package API\TaskBundle\Security
 */
class TaskAttributeVoter extends ApiBaseVoter implements VoterInterface
{
    /** @var  User */
    private $user;

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $action
     *
     * @param mixed $options
     *
     * @return bool
     */
    public function isGranted($action, $options = false)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::LIST_TASK_ATTRIBUTES:
                return $this->canList();
            case VoteOptions::SHOW_TASK_ATTRIBUTE:
                return $this->canRead();
            case VoteOptions::CREATE_TASK_ATTRIBUTE:
                return $this->canCreate();
            case VoteOptions::UPDATE_TASK_ATTRIBUTE:
                return $this->canUpdate();
            case VoteOptions::DELETE_TASK_ATTRIBUTE:
                return $this->canDelete();
            default:
                return false;
        }

    }

    /**
     * User can see a list of task attributes
     *
     * @return bool
     */
    private function canList():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::LIST_TASK_ATTRIBUTES, $this->user, VoteOptions::getConstants());
    }

    /**
     * User can see the task attribute
     *
     * @return bool
     */
    private function canRead():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::LIST_TASK_ATTRIBUTES, $this->user, VoteOptions::getConstants());
    }

    /**
     * User can create the task attribute
     *
     * @return bool
     */
    private function canCreate():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::CREATE_TASK_ATTRIBUTE, $this->user, VoteOptions::getConstants());
    }

    /**
     * User can update the task attribute
     *
     * @return bool
     */
    private function canUpdate():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::UPDATE_TASK_ATTRIBUTE, $this->user, VoteOptions::getConstants());
    }

    /**
     * User can delete the task attribute
     *
     * @return bool
     */
    private function canDelete():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::DELETE_TASK_ATTRIBUTE, $this->user, VoteOptions::getConstants());
    }
}