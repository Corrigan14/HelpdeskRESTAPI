<?php

namespace API\CoreBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class ApiBaseVoter
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
     * @param TokenStorage                   $tokenStorage
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager , TokenStorage $tokenStorage)
    {
        $this->decisionManager = $decisionManager;
        $this->token = $tokenStorage->getToken();
    }
}