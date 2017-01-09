<?php

namespace API\TaskBundle\Security;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\VoterInterface;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class FilterVoter
 *
 * @package API\TaskBundle\Security
 */
class FilterVoter implements VoterInterface
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
            case VoteOptions::CREATE_PUBLIC_FILTER:
                return $this->canCreatePublicFilter();
            case VoteOptions::CREATE_FILTER:
                return $this->canCreateFilter();
            case VoteOptions::CREATE_PROJECT_FILTER:
                return $this->canCreateProjectFilter($options);
            case VoteOptions::CREATE_PUBLIC_PROJECT_FILTER:
                return $this->canCreatePublicProjectFilter($options);
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
     * @return bool
     */
    private function canCreateFilter(): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // Everybody can create it's own filter
        return true;
    }

    /**
     * @return bool
     */
    private function canCreatePublicFilter(): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can create public filter if he has ACL CAN_CREATE_PUBLIC_FILTER
        return false;
    }

    /**
     * @param Project $project
     * @return bool
     */
    private function canCreateProjectFilter(Project $project): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can create filter in project if he created this project
        if ($project->getCreatedBy()->getId() === $this->user->getId()) {
            return true;
        }

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

    /**
     * @param Project $project
     * @return bool
     */
    private function canCreatePublicProjectFilter(Project $project): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can create PUBLIC filter in project if he created this project
        if ($project->getCreatedBy()->getId() === $this->user->getId()) {
            return true;
        }

        // User can create PUBLIC filter in project if he has CREATE_PUBLIC_PROJECT_FILTER permission to this project
        $userHasProject = $this->em->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->user,
            'project' => $project
        ]);
        if ($userHasProject instanceof UserHasProject) {
            $acl = $userHasProject->getAcl();
            return in_array(VoteOptions::CREATE_PUBLIC_PROJECT_FILTER, $acl);
        }

        return false;
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