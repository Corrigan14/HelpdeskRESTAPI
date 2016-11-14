<?php

namespace API\CoreBundle\Security;

use API\CoreBundle\Entity\User;

/**
 * Class CompanyVoter
 * @package API\CoreBundle\Security
 */
class CompanyVoter extends ApiBaseVoter implements VoterInterface
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
    public function isGranted($action, $options = [])
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::CREATE_COMPANY:
                return $this->canCreate();
            case VoteOptions::SHOW_COMPANY:
                return $this->canRead($options);
            case VoteOptions::UPDATE_COMPANY:
                return $this->canUpdate($options);
            case VoteOptions::DELETE_COMPANY:
                return $this->canDelete($options);
            case VoteOptions::LIST_COMPANIES:
                return $this->canList();
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

        return $this->hasAclRights(VoteOptions::LIST_COMPANIES, $this->user);
    }

    /**
     * @param int $companyId
     * @return bool
     */
    private function canRead(int $companyId):bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        if($this->user->getCompany()->getId() === $companyId){
            return true;
        }

        return $this->hasAclRights(VoteOptions::SHOW_COMPANY, $this->user);
    }
}