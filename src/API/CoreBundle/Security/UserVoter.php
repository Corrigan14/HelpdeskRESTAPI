<?php
/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 10/25/16
 * Time: 1:06 PM
 */

namespace API\CoreBundle\Security;


use API\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class UserVoter
 *
 * @package API\CoreBundle\Security
 */
class UserVoter
{
    /** @var AccessDecisionManagerInterface */
    private $decisionManager;
    /** @var TokenInterface */
    private $token;
    /** @var  User */
    private $user;

    /**
     * UserVoter constructor.
     *
     * @param AccessDecisionManagerInterface $decisionManager
     * @param TokenStorage                   $tokenStorage
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager , TokenStorage $tokenStorage)
    {
        $this->decisionManager = $decisionManager;
        $this->token = $tokenStorage->getToken();
    }


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
            throw new \InvalidArgumentException('User must be an Instance of User Entity');
        }

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
            case VoteOptions::LIST_USERS:
                return $this->canList();
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

        return $this->hasAclRights(VoteOptions::CREATE_USER);
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

        return $this->hasAclRights(VoteOptions::SHOW_USER);
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

        return $this->hasAclRights(VoteOptions::UPDATE_USER);
    }

    /**
     * @param int $user
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canDelete(int $user): bool
    {
        if ($this->decisionManager->decide($this->token , ['ROLE_ADMIN'])) {
            return true;
        }
        if ($user === $this->user->getId()) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::DELETE_USER);
    }

    /**
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canList(): bool
    {
        if ($this->decisionManager->decide($this->token , ['ROLE_ADMIN'])) {
            return true;
        }


        return $this->hasAclRights(VoteOptions::LIST_USERS);
    }

    /**
     * Every User has a custom array of access rights
     *
     * @param $action
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function hasAclRights($action)
    {
        if (!in_array($action , VoteOptions::getConstants() , true)) {
            throw new \InvalidArgumentException('Action ins not valid, please list your action in the options list');
        }

        $acl = $this->user->getAcl();

        if (in_array($action , $acl , true)) {
            return true;
        }

        return false;
    }
}