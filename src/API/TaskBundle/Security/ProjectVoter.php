<?php

namespace API\TaskBundle\Security;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\ApiBaseVoter;
use API\CoreBundle\Security\VoterInterface;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;

/**
 * Class ProjectVoter
 *
 * @package API\TaskBundle\Security
 */
class ProjectVoter extends ApiBaseVoter implements VoterInterface
{
    /** @var  User */
    private $user;

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $action
     *
     * @param Project|bool $project
     *
     * @return bool
     */
    public function isGranted($action, $project = false)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::LIST_PROJECTS:
                return $this->canList();
            case VoteOptions::SHOW_PROJECT:
                return $this->canRead($project);
            case VoteOptions::CREATE_PROJECT:
                return $this->canCreate();
            case VoteOptions::UPDATE_PROJECT;
                return $this->canUpdate();
            case VoteOptions::DELETE_PROJECT;
                return $this->canDelete();
            default:
                return false;
        }
    }

    /**
     * Check if logged user has Admin ROLE
     *
     * @return bool
     */
    public function isAdmin():bool
    {
        return $this->decisionManager->decide($this->token, ['ROLE_ADMIN']);
    }

    /**
     * User can see a list of projects if: he is an admin, or he has it's own projects or he has any permission to another projects
     *
     * @return bool
     */
    private function canList():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        $usersProjects = $this->user->getProjects();
        if (count($usersProjects) > 0) {
            return true;
        }

        $userHasProjects = $this->user->getUserHasProjects();
        if (count($userHasProjects) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param Project $project
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canRead(Project $project):bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($project->getCreatedBy() === $this->user) {
            return true;
        }

        return $this->hasAclProjectRights(VoteOptions::SHOW_PROJECT, $project);
    }

    /**
     * Admin can create project or it depends on Users ACL
     *
     * @return bool
     */
    private function canCreate():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::CREATE_PROJECT, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canUpdate():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::UPDATE_STATUS, $this->user, VoteOptions::getConstants());
    }

    /**
     * @return bool
     */
    private function canDelete():bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $this->hasAclRights(VoteOptions::DELETE_STATUS, $this->user, VoteOptions::getConstants());
    }

    /**
     * Every User has a custom array of access rights to every project
     *
     * @param string $action
     *
     * @param Project $project
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function hasAclProjectRights($action, Project $project)
    {
        if (!in_array($action, VoteOptions::getConstants(), true)) {
            throw new \InvalidArgumentException('Action ins not valid, please list your action in the options list');
        }

        $userHasProjects = $this->user->getUserHasProjects();

        if (count($userHasProjects) > 0) {
            /** @var UserHasProject $uhp */
            foreach ($userHasProjects as $uhp) {
                if ($uhp->getProject() === $project) {
                    $acl = $uhp->getAcl();
                    if (in_array($action, $acl, true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}