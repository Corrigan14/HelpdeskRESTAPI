<?php

namespace API\TaskBundle\Security;


use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\ApiBaseVoter;
use API\CoreBundle\Security\VoterInterface;

/**
 * Class CompanyAttributeVoter
 *
 * @package API\TaskBundle\Security
 */
class CompanyAttributeVoter extends ApiBaseVoter implements VoterInterface
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
            return false;
        }

        switch ($action) {
            case VoteOptions::LIST_COMPANY_ATTRIBUTES:
                return $this->canList();
            case VoteOptions::SHOW_COMPANY_ATTRIBUTE:
                return $this->canShow();
            case VoteOptions::CREATE_COMPANY_ATTRIBUTE:
                return $this->canCreate();
            case VoteOptions::UPDATE_COMPANY_ATTRIBUTE:
                return $this->canUpdate();
            case VoteOptions::DELETE_COMPANY_ATTRIBUTE:
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

        return $this->hasAclRights(VoteOptions::LIST_COMPANY_ATTRIBUTES, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canShow():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::SHOW_COMPANY_ATTRIBUTE, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canCreate():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::CREATE_COMPANY_ATTRIBUTE, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canUpdate():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::UPDATE_COMPANY_ATTRIBUTE, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canDelete():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::DELETE_COMPANY_ATTRIBUTE, $this->user, VoteOptions::getConstants());
    }
}