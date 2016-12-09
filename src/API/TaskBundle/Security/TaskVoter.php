<?php

namespace API\TaskBundle\Security;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\ApiBaseVoter;
use API\CoreBundle\Security\VoterInterface;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\UserHasProject;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class TaskVoter
 *
 * @package API\TaskBundle\Security
 */
class TaskVoter implements VoterInterface
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
            // the user must be logged in if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::LIST_TASKS:
                return $this->canList($options);
            case VoteOptions::SHOW_TASK:
                return $this->canRead($options);
            case VoteOptions::CREATE_TASK:
                return $this->canCreate($options);
            default:
                return false;
        }

    }

    /**
     * @return bool
     */
    public function isAdmin():bool
    {
        return $this->decisionManager->decide($this->token, ['ROLE_ADMIN']);
    }

    /**
     * User can see a list of tasks
     *
     * @param array $options
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canList(array $options):bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        $actions = [];
        $actions[] = VoteOptions::VIEW_ALL_TASKS_IN_PROJECT;
        $actions[] = VoteOptions::VIEW_COMPANY_TASKS_IN_PROJECT;
        $actions[] = VoteOptions::VIEW_USER_TASKS_IN_PROJECT;
        $actions[] = VoteOptions::CREATE_TASK_IN_PROJECT;

        // If user requests to view tasks in some project
        // Option to see Tasks in some project depends on users project ACL:
        // VIEW_ALL_TASKS_IN_PROJECT, VIEW_COMPANY_TASKS_IN_PROJECT, VIEW_USER_TASKS_IN_PROJECT, CREATE_TASK_IN_PROJECT
        if (false !== $options['project']) {
            return $this->hasAclProjectRights($actions, $options['project']);
        }

        //If user created some projects, return TRUE
        $usersProjects = $this->user->getProjects();
        if (count($usersProjects) > 0) {
            return true;
        }

        // If user can create or list tasks or can view some task in som projects, return TRUE
        // The list of tasks is based on user's ACL - service manage this
        $canCreate = $this->hasAclRights(VoteOptions::CREATE_TASK, $this->user);
        $canList = $this->hasAclRights(VoteOptions::LIST_TASKS, $this->user);
        $canViewProjectTasks = $this->hasAclProjectsRights($actions);

        return $canCreate || $canList || $canViewProjectTasks;
    }

    /**
     * User can see a task
     *
     * @param  $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canRead(Task $task):bool
    {

        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can see a task if he created it or task is requested by him
        if ($task->getCreatedBy()->getId() === $this->user->getId() || $task->getRequestedBy()->getId() === $this->user->getId()) {
            return true;
        }

        $taskProject = $task->getProject();
        if ($taskProject instanceof Project) {
            $userHasProject = $this->em->getRepository('APITaskBundle:UserHasProject')->findOneBy([
                'user' => $this->user,
                'project' => $taskProject
            ]);
            if ($userHasProject instanceof UserHasProject) {
                $acl = $userHasProject->getAcl();
                if (null !== $acl) {
                    // User can see a task if this task is from project where user has access: VIEW_ALL_TASKS_IN_PROJECT
                    if (in_array(VoteOptions::VIEW_ALL_TASKS_IN_PROJECT, $acl)) {
                        return true;
                    } elseif (in_array(VoteOptions::VIEW_COMPANY_TASKS_IN_PROJECT, $acl)) {
                        // User can see a task if this task is from project where user has access: VIEW_COMPANY_TASKS_IN_PROJECT
                        // and user is from same company like creator of task
                        $usersCompany = $this->user->getCompany();
                        if ($usersCompany instanceof Company) {
                            $companyU = $usersCompany;
                        } else {
                            $companyU = false;
                        }
                        $taskCreatorCompany = $task->getCreatedBy()->getCompany();
                        if ($taskCreatorCompany instanceof Company) {
                            $companyT = $taskCreatorCompany;
                        } else {
                            $companyT = false;
                        }
                        if ($companyU && $companyT && $companyU->getId() === $companyT->getId()) {
                            return true;
                        }
                        return false;
                    }
                }
                return false;
            }
            return false;
        }
        return false;
    }

    /**
     * User can create a task
     *
     * @param  Project|null $project
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canCreate($project):bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can create task without project if he has CREATE_TASK access in its ACL
        if (null === $project) {
            return $this->hasAclRights(VoteOptions::CREATE_TASK, $this->user);
        }

        // User can create task in it's own project
        if ($project->getCreatedBy()->getId() === $this->user->getId()) {
            return true;
        }

        // User can create task if he has CREATE_TASK_IN_PROJECT access in projects ACL
        $actions [] = VoteOptions::CREATE_TASK_IN_PROJECT;
        return $this->hasAclProjectRights($actions, $project->getId());
    }

    /**
     * User can have a custom array of access rights to selected project
     *
     * @param array $actions
     * @param int $projectId
     * @return bool
     * @throws \InvalidArgumentException
     * @internal param string $action
     *
     */
    private function hasAclProjectRights(array $actions, int $projectId):bool
    {
        $userHasProject = $this->em->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'project' => $projectId,
            'user' => $this->user,
        ]);

        if (count($userHasProject) > 0) {
            foreach ($actions as $action) {
                if (!in_array($action, VoteOptions::getConstants(), true)) {
                    throw new \InvalidArgumentException('Action is not valid, please list your action in the options list');
                }

                $acl = $userHasProject->getAcl();
                if (in_array($action, $acl, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Every User have a custom array of access rights to every project
     *
     * @param array $actions
     * @return bool
     * @throws \InvalidArgumentException
     * @internal param string $action
     *
     */
    private function hasAclProjectsRights(array $actions):bool
    {
        $userHasProjects = $this->user->getUserHasProjects();

        if (count($userHasProjects) > 0) {
            foreach ($actions as $action) {
                if (!in_array($action, VoteOptions::getConstants(), true)) {
                    throw new \InvalidArgumentException('Action is not valid, please list your action in the options list');
                }

                /** @var UserHasProject $uhp */
                foreach ($userHasProjects as $uhp) {
                    $acl = $uhp->getAcl();
                    if (null !== $acl && in_array($action, $acl, true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Every User has a custom array of access rights
     *
     * @param string $action
     * @param User $user
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function hasAclRights($action, User $user):bool
    {
        if (!in_array($action, VoteOptions::getConstants(), true)) {
            throw new \InvalidArgumentException('Action ins not valid, please list your action in the options list');
        }

        $acl = $user->getAcl();

        if (null !== $acl && in_array($action, $acl, true)) {
            return true;
        }

        return false;
    }
}