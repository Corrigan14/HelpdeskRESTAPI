<?php

namespace API\TaskBundle\Security;


use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\ApiBaseVoter;
use API\CoreBundle\Security\VoterInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class TagVoter
 *
 * @package API\TaskBundle\Security
 */
class TagVoter extends ApiBaseVoter implements VoterInterface
{
    /** @var  User */
    private $user;

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string   $action
     *
     * @param bool|int $targetTagId
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isGranted($action , $targetTagId = false)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::CREATE_PUBLIC_TAG:
                return $this->canCreatePublicTag();
            case VoteOptions::SHOW_TAG:
                return $this->canReadTag($targetTagId);
            default:
                return false;
        }
    }

    /**
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canCreatePublicTag(): bool
    {
        if ($this->decisionManager->decide($this->token , ['ROLE_ADMIN'])) {
            return true;
        }

        return false;
    }

    /**
     * @param int $tagId
     *
     * @return bool
     * @throws \InvalidArgumentException*
     */
    private function canReadTag(int $tagId): bool
    {
        $tag = $this->em->getRepository('APITaskBundle:Tag')->find($tagId);

        if ($tag->getPublic() || $this->user->getId() === $tag->getCreatedBy()->getId()) {
            return true;
        }

        return false;
    }

}