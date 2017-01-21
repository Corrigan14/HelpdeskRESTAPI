<?php

namespace API\CoreBundle\Security;

use API\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class ApiBaseVoter
 *
 * @package API\CoreBundle\Security
 */
class ApiBaseVoter
{
    /** @var AccessDecisionManagerInterface */
    protected $decisionManager;

    /** @var TokenInterface */
    protected $token;

    /**
     * ApiBaseVoter constructor.
     *
     * @param AccessDecisionManagerInterface $decisionManager
     * @param TokenStorage $tokenStorage
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager, TokenStorage $tokenStorage)
    {
        $this->decisionManager = $decisionManager;
        $this->token = $tokenStorage->getToken();
    }

    /**
     * Every User has a custom array of access rights
     *
     * @param string $action
     * @param User $user
     *
     * @param array|bool $voteConsts
     * @return bool
     */
    public function hasAclRights($action, User $user, $voteConsts = false)
    {
        if (!$voteConsts) {
            $voteConsts = VoteOptions::getConstants();
        }

        if (!in_array($action, $voteConsts, true)) {
            throw new \InvalidArgumentException('Action ins not valid, please list your action in the options list');
        }

//        $acl = $user->getAcl();

//        if (in_array($action, $acl, true)) {
//            return true;
//        }

        return false;
    }
}