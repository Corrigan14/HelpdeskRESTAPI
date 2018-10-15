<?php

namespace API\TaskBundle\Security\Filter;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Security\VoteOptions;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class Voter
 *
 * @package API\TaskBundle\Security\Filetr
 */
class Voter
{
    /** @var  User */
    private $user;

    /** @var AccessDecisionManagerInterface */
    protected $decisionManager;

    /** @var TokenInterface */
    protected $token;

    /** @var  EntityManager */
    protected $em;

    /**
     * ApiBaseVoter constructor.
     *
     * @param AccessDecisionManagerInterface $decisionManager
     * @param TokenStorage $tokenStorage
     * @param EntityManager $em
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager, TokenStorage $tokenStorage, EntityManager $em)
    {
        $this->decisionManager = $decisionManager;
        $this->token = $tokenStorage->getToken();
        $this->em = $em;
    }

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
            case VoteOptions::SHOW_FILTER:
                return $this->canViewFilter($options);
            case VoteOptions::UPDATE_FILTER:
                return $this->canUpdateFilter($options);
            case VoteOptions::UPDATE_PROJECT_FILTER:
                return $this->canUpdateProjectFilter($options);
            case VoteOptions::CREATE_PROJECT_FILTER:
                return $this->canCreateProjectFilter($options);
            case VoteOptions::DELETE_FILTER:
                return $this->canDeleteFilter($options);
            case VoteOptions::SET_REMEMBERED_FILTER:
                return $this->canSetRememberedFilter($options);
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

        // User can view a filter if it is report and he/she has a his rol has a right REPORT_FILTERS
        /** @var UserRole $loggedUsersRole */
        $loggedUsersRole = $this->user->getUserRole();
        if ($filter->getReport() && \in_array(UserRoleAclOptions::REPORT_FILTERS, $loggedUsersRole->getAcl(), true)) {
            return true;
        }

        return false;
    }

    /**
     * @param Filter $filter
     * @return bool
     */
    private function canUpdateFilter(Filter $filter): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can update filter if he created it
        if ($this->user->getId() === $filter->getCreatedBy()->getId()) {
            return true;
        }

        return false;
    }


    /**
     * @param array $options
     * @return bool
     */
    private function canUpdateProjectFilter(array $options): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        $filter = $options['filter'];
        $project = $options['project'];

        // User can update filter if he created it
        if ($this->user->getId() === $filter->getCreatedBy()->getId()) {
            // User can create filter in project if he has ANY permission to this project
            $userHasProject = $this->em->getRepository('APITaskBundle:UserHasProject')->findOneBy([
                'user' => $this->user,
                'project' => $project
            ]);
            if ($userHasProject instanceof UserHasProject) {
                return true;
            }

            return false;
        }

        return false;
    }


    /**
     * @param array $options
     * @return bool
     */
    private function canCreateProjectFilter(array $options): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        $project = $options['project'];

        // User can create filter in a project if he has ANY permission to this project
        $userHasProject = $this->em->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->user,
            'project' => $project
        ]);
        if ($userHasProject instanceof UserHasProject) {
            return true;
        }

        return false;
    }


    /**
     * @param Filter $filter
     * @return bool
     */
    private function canDeleteFilter(Filter $filter): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can delete filter if he created it
        if ($filter->getCreatedBy()->getId() === $this->user->getId()) {
            return true;
        }

        return false;
    }

    /**
     * @param Filter $filter
     * @return bool
     */
    private function canSetRememberedFilter(Filter $filter): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can set remembered filter if this filter if PUBLIC or he created it
        if ($filter->getCreatedBy()->getId() === $this->user->getId()) {
            return true;
        }

        if ($filter->getPublic()) {
            return true;
        }

        return false;
    }
}