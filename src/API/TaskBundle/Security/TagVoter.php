<?php

namespace API\TaskBundle\Security;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\ApiBaseVoter;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\UserRole;

/**
 * Class TagVoter
 *
 * @package API\TaskBundle\Security
 */
class TagVoter extends ApiBaseVoter
{
    /** @var  User */
    private $user;

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $action
     *
     * @param bool|Tag $tag
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isGranted($action, $tag = false)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::SHOW_TAG:
                return $this->canReadTag($tag);
            case VoteOptions::UPDATE_TAG:
                return $this->canUpdateTag($tag);
            default:
                return false;
        }
    }

    /**
     * @param Tag $tag
     * @return bool
     * @internal param int $tagId
     *
     */
    private function canReadTag(Tag $tag): bool
    {
        return ($tag->getPublic() || $this->user->getId() === $tag->getCreatedBy()->getId());
    }

    /**
     * @param Tag $tag
     * @return bool
     * @internal param int $tagId
     *
     */
    private function canUpdateTag(Tag $tag): bool
    {
        $createdTag = false;
        if ($this->user->getId() === $tag->getCreatedBy()->getId()) {
            $createdTag = true;
        }

        /** @var UserRole $loggedUserRole */
        $loggedUserRole = $this->user->getUserRole();
        $loggedUserRoleACL = $loggedUserRole->getAcl();

        $shareTagACL = false;
        if (true === $tag->getPublic() && \in_array(UserRoleAclOptions::SHARE_TAGS, $loggedUserRoleACL, true)) {
            $shareTagACL = true;
        }

        return ($createdTag || $shareTagACL);
    }
}