<?php

namespace API\TaskBundle\Security;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\ApiBaseVoter;
use API\CoreBundle\Security\VoterInterface;
use API\TaskBundle\Entity\Filter;

/**
 * Class FilterVoter
 *
 * @package API\TaskBundle\Security
 */
class FilterVoter extends ApiBaseVoter implements VoterInterface
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
    public function isGranted($action, $options)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::SHOW_FILTER:
                return $this->canViewFilter($options);
            case VoteOptions::CREATE_FILTER:
                return $this->canCreateFilter($options);
            case VoteOptions::UPDATE_FILTER:
                return $this->canUpdateFilter($options);
            case VoteOptions::DELETE_FILTER:
                return $this->canDeleteFilter($options);
            default:
                return false;
        }
    }

    /**
     * @param Filter $filter
     * @return bool
     */
    private function canViewFilter(Filter $filter): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can view filter if this is PUBLIC
        if ($filter->getPublic()) {
            return true;
        }

        //User can view filter if he created it
        if ($filter->getCreatedBy()->getId() === $this->user->getId()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $options
     * @return bool
     */
    private function canCreateFilter(array $options): bool
    {

    }


    /**
     * @param Filter $filter
     * @return bool
     */
    private function canUpdateFilter(Filter $filter): bool
    {

    }


    /**
     * @param Filter $filter
     * @return bool
     */
    private function canDeleteFilter(Filter $filter): bool
    {

    }
}