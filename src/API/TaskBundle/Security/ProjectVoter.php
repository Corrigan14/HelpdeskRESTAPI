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
     * @param UserHasProject|Project|bool $project
     *
     * @return bool
     * @throws \InvalidArgumentException
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
            case VoteOptions::VIEW_PROJECT:
                return $this->canRead($project);
            case VoteOptions::EDIT_PROJECT:
                return $this->canEdit($project);
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
    private function canRead($project):bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        $projectAcl = [
            ProjectAclOptions::VIEW_ALL_TASKS,
            ProjectAclOptions::VIEW_OWN_TASKS,
            ProjectAclOptions::VIEW_TASKS_FROM_USERS_COMPANY
        ];

        return $this->hasAclProjectRightsConditionOR($projectAcl, $project);
    }

    /**
     * @param UserHasProject $userHasProject
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canEdit($userHasProject):bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($userHasProject instanceof UserHasProject) {
            return $this->hasAclProjectRight(ProjectAclOptions::EDIT_PROJECT, $userHasProject);
        }

        return false;
    }

    /**
     * Every User has a custom array of access rights to every project
     *
     * @param string $action
     *
     * @param UserHasProject $userHasProject
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function hasAclProjectRight($action, UserHasProject $userHasProject)
    {
        $acl = $userHasProject->getAcl();
        if (in_array($action, $acl, true)) {
            return true;
        }

        return false;
    }

    /**
     * Every User has a custom array of access rights to every project
     *
     * @param array $actions
     *
     * @param Project $project
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function hasAclProjectRightsConditionOR(array $actions, Project $project)
    {
        foreach ($actions as $action) {
            if (!in_array($action, ProjectAclOptions::getConstants(), true)) {
                throw new \InvalidArgumentException('Action is not valid, please list your action in the options list');
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
        }

        return false;
    }
}