<?php

namespace API\TaskBundle\Security;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\VoterInterface;
use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Entity\UserRole;
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
     * @throws \InvalidArgumentException
     */
    public function isGranted($action, $options = false)
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in if not, deny access
            return false;
        }

        switch ($action) {
            case VoteOptions::SHOW_TASK:
                return $this->canRead($options, $this->user);
            case VoteOptions::CREATE_TASK_IN_PROJECT:
                return $this->canCreateTaskInProject($options);
            case VoteOptions::UPDATE_TASK:
                return $this->canUpdate($options);
            case VoteOptions::DELETE_TASK:
                return $this->canDelete($options);
            case VoteOptions::ADD_TASK_FOLLOWER:
                return $this->canAddTaskFollower($options);
            case VoteOptions::REMOVE_TASK_FOLLOWER:
                return $this->canRemoveTaskFollower($options);
            case VoteOptions::ADD_TAG_TO_TASK:
                return $this->canAddTagToTask($options);
            case VoteOptions::REMOVE_TAG_FROM_TASK:
                return $this->canRemoveTagFromTask($options);
            case VoteOptions::SHOW_LIST_OF_TASK_TAGS:
                return $this->canShowListOfTasksTags($options);
            case VoteOptions::ASSIGN_USER_TO_TASK:
                return $this->canAssignUserToTask($options);
            case VoteOptions::UPDATE_ASSIGN_USER_TO_TASK:
                return $this->casUpdateAssignUserToTask($options);
            case VoteOptions::REMOVE_ASSIGN_USER_FROM_TASK:
                return $this->casRemoveAssignUserFromTask($options);
            case VoteOptions::ADD_ATTACHMENT_TO_TASK:
                return $this->canAddAttachmentToTask($options);
            case VoteOptions::REMOVE_ATTACHMENT_FROM_TASK:
                return $this->canRemoveAttachmentFromTask($options);
            case VoteOptions::SHOW_LIST_OF_TASK_ATTACHMENTS:
                return $this->canShowListOfTaskAttachments($options);
            case VoteOptions::SHOW_LIST_OF_TASK_FOLLOWERS:
                return $this->canShowListOfTaskFollowers($options);
            case VoteOptions::SHOW_LIST_OF_USERS_ASSIGNED_TO_TASK:
                return $this->canShowListOfUsersAssignedToTask($options);
            case VoteOptions::SHOW_LIST_OF_TASKS_COMMENTS:
                return $this->canShowListOfTasksComments($options);
            case VoteOptions::SHOW_TASKS_COMMENT:
                return $this->canShowTasksComment($options);
            case VoteOptions::ADD_COMMENT_TO_TASK:
                return $this->canAddCommentToTask($options);
            case VoteOptions::ADD_COMMENT_TO_COMMENT:
                return $this->canAddCommentToComment($options);
            case VoteOptions::DELETE_COMMENT:
                return $this->canDeleteComment($options);
            case VoteOptions::ADD_ATTACHMENT_TO_COMMENT:
                return $this->canAddAttachmentToComment($options);
            case VoteOptions::SHOW_LIST_OF_COMMENTS_ATTACHMENTS:
                return $this->canShowListOfCommentsAttachments($options);
            case VoteOptions::REMOVE_ATTACHMENT_FROM_COMMENT:
                return $this->canRemoveAttachmentFromComment($options);
            default:
                return false;
        }

    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->decisionManager->decide($this->token, ['ROLE_ADMIN']);
    }

    /**
     * User can view a task
     *
     * @param Task $task
     * @param User $user
     * @return bool
     */
    private function canRead(Task $task, User $user): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can view a task if he created it or task is requested by him or task is assigned to him
        // and this task isn't in any Project
        $taskIsAssignedToUser = $this->taskIsAssignedToUser($task, $user);
        if (null === $task->getProject() && ($taskIsAssignedToUser || $task->getCreatedBy()->getId() === $user->getId() || $task->getRequestedBy()->getId() === $user->getId())) {
            return true;
        }

        $taskProject = $task->getProject();
        if ($taskProject instanceof Project) {
            $userHasProject = $this->em->getRepository('APITaskBundle:UserHasProject')->findOneBy([
                'user' => $user,
                'project' => $taskProject
            ]);
            if ($userHasProject instanceof UserHasProject) {
                $acl = $userHasProject->getAcl();
                if (null !== $acl) {
                    // User can view a task if this task is from project where user has access: VIEW_ALL_TASKS
                    if (in_array(ProjectAclOptions::VIEW_ALL_TASKS, $acl, true)) {
                        return true;
                    } elseif (in_array(ProjectAclOptions::VIEW_TASKS_FROM_USERS_COMPANY, $acl, true)) {
                        // User can view a task if this task is from project where user has access: VIEW_TASKS_FROM_USERS_COMPANY
                        // and user is from the same company like creator of task
                        $usersCompany = $user->getCompany();
                        $companyU = ($usersCompany instanceof Company ? $usersCompany : false);

                        $taskCreatorCompany = $task->getCreatedBy()->getCompany();
                        $companyT = ($taskCreatorCompany instanceof Company ? $taskCreatorCompany : false);

                        return $companyU && $companyT && $companyU->getId() === $companyT->getId();
                    } elseif (in_array(ProjectAclOptions::VIEW_OWN_TASKS, $acl, true) && ($task->getCreatedBy()->getId() === $user->getId() || $task->getRequestedBy()->getId() === $user->getId())) {
                        // User can view a task if this task is from project where user has access: VIEW_OWN_TASKS
                        // and user created or requested this task
                        return true;
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
     * @param Project $project
     * @return bool
     */
    private function canCreateTaskInProject(Project $project): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can create task without project if his role has CREATE_TASKS_IN_ALL_PROJECTS ACL
        $acl = UserRoleAclOptions::CREATE_TASKS_IN_ALL_PROJECTS;
        /** @var User $user */
        $user = $this->user;
        $userRole = $user->getUserRole();
        if ($userRole instanceof UserRole) {
            $userRoleHasAcl = $userRole->getAcl();
            if (in_array($acl, $userRoleHasAcl, true)) {
                return true;
            }
        }

        // User can create task if he has CREATE_TASK access in projects ACL
        return $this->hasProjectAclRight(ProjectAclOptions::CREATE_TASK, $project->getId());
    }

    /**
     * User can update the task entity
     *
     * @param  Task|null $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canUpdate($task): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // User can update task if his role has UPDATE_ALL_TASKS ACL
        $acl = UserRoleAclOptions::UPDATE_ALL_TASKS;
        /** @var User $user */
        $user = $this->user;
        $userRole = $user->getUserRole();
        if ($userRole instanceof UserRole) {
            $userRoleHasAcl = $userRole->getAcl();
            if (in_array($acl, $userRoleHasAcl, true)) {
                return true;
            }
        }

        $project = $task->getProject();
        if ($project instanceof Project) {
            // User can update task if he has RESOLVE_TASK access in projects ACL
            return $this->hasProjectAclRight(ProjectAclOptions::RESOLVE_TASK, $project->getId());
        } else {
            // User can update task without project if he created ar requested this task
            return ($this->user->getId() === $task->getRequestedBy()->getId() || $this->user->getId() === $task->getCreatedBy()->getId());
        }
    }

    /**
     * User can delete the task entity
     *
     * @param  Task|null $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function canDelete($task): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        $project = $task->getProject();
        if ($project instanceof Project) {
            // User can delete task if he has DELETE_TASK access in projects ACL
            return $this->hasProjectAclRight(ProjectAclOptions::DELETE_TASK, $project->getId());
        }

        return false;
    }

    /**
     *
     * @param array $options
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canAddTaskFollower(array $options): bool
    {
        $task = $options['task'];
        $follower = $options['follower'];

        // If logged user can update task, he can add or remove follower to this task
        $canUpdate = $this->canUpdate($task);

        // Check if selected Follower can follow selected Task
        if (true === $canUpdate) {
            return $this->userCanFollowTask($task, $follower);
        }

        return false;
    }

    /**
     * @param array $options
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canRemoveTaskFollower(array $options): bool
    {
        $task = $options['task'];

        // If logged user can update task, he can add or remove follower to this task
        return $this->canUpdate($task);
    }

    /**
     * @param $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canShowListOfTaskFollowers(Task $task): bool
    {
        // User Can View Task Followers if he CAN READ this task
        return $this->canRead($task, $this->user);
    }

    /**
     * @param array $options
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canAddTagToTask(array $options): bool
    {
        /** @var Task $task */
        $task = $options['task'];
        /** @var Tag $tag */
        $tag = $options['tag'];

        // User Can add Tag to the Task if he can UPDATE_TASK
        $canUpdate = $this->canUpdate($task);

        // User can add tag if it's public or it's his tag (he created it)
        $canTag = ($tag->getCreatedBy()->getId() === $this->user->getId() || $tag->getPublic());

        return ($canUpdate && $canTag);
    }

    /**
     * @param array $options
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canRemoveTagFromTask(array $options): bool
    {
        // User Can remove Tag from the Task if he can UPDATE_TASK
        return $this->canUpdate($options['task']);
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \InvalidArgumentException
     * @internal param array $options
     */
    public function canShowListOfTasksTags(Task $task): bool
    {
        // User Can View Tags of Task if he CAN READ this task
        return $this->canRead($task, $this->user);
    }

    /**
     * @param array $options
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canAssignUserToTask(array $options): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        /** @var Task $task */
        $task = $options['task'];
        /** @var User $user */
        $user = $options['user'];

        $canUser = false;

        // User Can assign User to the Task if he can UPDATE_TASK
        $canUpdate = $this->canUpdate($task);

        $project = $task->getProject();

        if (null === $project) {
            // User can be assigned to the task without project, if he created or requested this task
            if ($task->getCreatedBy()->getId() === $user->getId() || $task->getRequestedBy()->getId() === $user->getId()) {
                $canUser = true;
            }
        }

        if ($project instanceof Project) {
            // User be assigned to the task if he has RESOLVE_TASK access in projects ACL
            $canUser = $this->hasProjectAclRight(ProjectAclOptions::RESOLVE_TASK, $project->getId());
        }

        return ($canUpdate && $canUser);
    }

    /**
     * @param TaskHasAssignedUser $taskHasAssignedUser
     * @return bool
     */
    private function casUpdateAssignUserToTask(TaskHasAssignedUser $taskHasAssignedUser): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        // Only admin or user assigned to task can update this entity
        return $taskHasAssignedUser->getUser()->getId() === $this->user->getId();
    }

    /**
     * @param TaskHasAssignedUser $taskHasAssignedUser
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function casRemoveAssignUserFromTask(TaskHasAssignedUser $taskHasAssignedUser): bool
    {
        // User Can remove Assigned User from Task if he can UPDATE_TASK
        return $this->canUpdate($taskHasAssignedUser->getTask());
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canShowListOfUsersAssignedToTask(Task $task): bool
    {
        // User Can view a list of users assigned to Task if he can READ_TASK
        return $this->canRead($task, $this->user);
    }

    /**
     * @param $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canAddAttachmentToTask(Task $task): bool
    {
        // User Can add Attachment to Task if he can UPDATE_TASK
        return $this->canUpdate($task);
    }

    /**
     * @param $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canRemoveAttachmentFromTask(Task $task): bool
    {
        // User Can Remove Attachment from Task if he can Add this attachment to this task
        return $this->canAddAttachmentToTask($task);
    }

    /**
     * @param $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canShowListOfTaskAttachments(Task $task): bool
    {
        // User Can View Attachments of Task if he CAN READ this task
        return $this->canRead($task, $this->user);
    }

    /**
     * @param $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canShowListOfTasksComments(Task $task): bool
    {
        // User can view a list of tasks comment if he can READ_TASK
        return $this->canRead($task, $this->user);
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canShowTasksComment(Task $task): bool
    {
        // User can view a tasks comment if he can READ_TASK
        return $this->canRead($task, $this->user);
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canAddCommentToTask(Task $task): bool
    {
        // User Can Add Comment to Task if he can UPDATE this task
        return $this->canUpdate($task);
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canAddCommentToComment(Task $task): bool
    {
        // User can add comment to comment if he can Add comment to Task
        return $this->canAddCommentToTask($task);
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canDeleteComment(Task $task): bool
    {
        // User can delete comment if he can Add comment to Task
        return $this->canAddCommentToTask($task);
    }

    /**
     * @param Comment $comment
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canShowListOfCommentsAttachments(Comment $comment): bool
    {
        // User can view a list of comment's attachments if he canShowTasksComment
        return $this->canShowTasksComment($comment->getTask());
    }

    /**
     * @param Comment $comment
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canAddAttachmentToComment(Comment $comment): bool
    {
        // User can add attachment to comment if he can Add comment to Task
        return $this->canAddCommentToTask($comment->getTask());
    }

    /**
     * @param Comment $comment
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function canRemoveAttachmentFromComment(Comment $comment): bool
    {
        // User can remove attachment from comment if he can Add attachment to
        return $this->canAddAttachmentToComment($comment);
    }

    /**
     * User can follow a task
     *
     * @param Task $task
     * @param User $follower
     * @return bool
     */
    private function userCanFollowTask(Task $task, User $follower): bool
    {
        // User can follow a task if he can view this task
        return $this->canRead($task, $follower);
    }

    /**
     * Every User have a custom array of access rights to every project
     *
     * @param $action
     * @param int $projectId
     * @return bool
     */
    private function hasProjectAclRight($action, int $projectId): bool
    {
        $userHasProject = $this->em->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'project' => $projectId,
            'user' => $this->user,
        ]);

        if ($userHasProject instanceof UserHasProject) {
            $usersProjectAcl = $userHasProject->getAcl();

            if (in_array($action, $usersProjectAcl, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Task $task
     * @param User $user
     * @return bool
     */
    private function taskIsAssignedToUser(Task $task, User $user): bool
    {
        $assignedUsers = $this->getAssignedUsersIds($task);

        if (in_array($user->getId(), $assignedUsers, true)) {
            return true;
        }

        return false;
    }

    /**
     * @param Task $task
     * @return array
     */
    private function getAssignedUsersIds(Task $task): array
    {
        $taskHasAssignedUsers = $task->getTaskHasAssignedUsers();
        $assignedUsers = [];

        if (count($taskHasAssignedUsers) > 0) {
            /** @var TaskHasAssignedUser $thau */
            foreach ($taskHasAssignedUsers as $thau) {
                $assignedUsers[] = $thau->getUser()->getId();
            }
        }

        return $assignedUsers;
    }
}