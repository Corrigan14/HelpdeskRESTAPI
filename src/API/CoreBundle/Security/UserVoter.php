<?php
/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 10/25/16
 * Time: 1:06 PM
 */

namespace API\CoreBundle\Security;


use API\CoreBundle\Entity\User;
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

    private $user;

    public function __construct(AccessDecisionManagerInterface $decisionManager , TokenInterface $token)
    {
        $this->decisionManager = $decisionManager;
        $this->token = $token;
    }


    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $action
     *
     * @return bool
     */
    protected function isGranted($action)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::CREATE_USER:
                return $this->canCreate();
            case VoteOptions::SHOW_USER:
                return $this->canRead();
            case VoteOptions::UPDATE_USER:
                return $this->canUpdate();
            case VoteOptions::DELETE_USER:
                return $this->canDelete();
            case VoteOptions::LIST_USERS:
                return $this->canList();
            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    private function canCreate(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    private function canRead(): bool
    {

        return false;
    }

    /**
     * @return bool
     */
    private function canUpdate(): bool
    {

        return false;
    }

    /**
     * @return bool
     */
    private function canDelete(): bool
    {

        return false;
    }

    /**
     *
     * @return bool
     */
    private function canList(): bool
    {
        if ($this->decisionManager->decide($this->token , ['ROLE_ADMIN'])) {
            return true;
        }

        return false;
    }
}