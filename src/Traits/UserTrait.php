<?php

namespace Traits;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Notification;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\TaskSubtask;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Entity\UserRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Exclude;

/**
 * Class UserTrait
 *
 * @package Traits
 */
trait UserTrait
{
    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Tag", mappedBy="createdBy")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $tags;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Project", mappedBy="createdBy")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $projects;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\UserHasProject", mappedBy="user")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $userHasProjects;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Task", mappedBy="createdBy")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $createdTasks;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Task", mappedBy="requestedBy")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $requestedTasks;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="API\TaskBundle\Entity\Task", mappedBy="followers")
     * @Exclude()
     */
    private $followedTasks;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\TaskHasAssignedUser", mappedBy="user")
     * @Serializer\Exclude()
     */
    private $taskHasAssignedUsers;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Comment", mappedBy="createdBy")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $comments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Filter", mappedBy="createdBy")
     * @Exclude()
     */
    private $filters;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\TaskSubtask", mappedBy="createdBy")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $createdSubtasks;

    /**
     * @var UserRole
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\UserRole", inversedBy="users")
     * @ORM\JoinColumn(name="user_role_id", referencedColumnName="id", nullable=true)
     * @Serializer\ReadOnly()
     */
    private $user_role;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Notification", mappedBy="createdBy")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $createdNotifications;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Notification", mappedBy="user")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $notifications;


    /**
     * Add tag
     *
     * @param Tag $tag
     * @return User
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add project
     *
     * @param Project $project
     * @return User
     */
    public function addProject(Project $project)
    {
        $this->projects[] = $project;

        return $this;
    }

    /**
     * Remove project
     *
     * @param Project $project
     */
    public function removeProject(Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * Get projects
     *
     * @return Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add userHasProject
     *
     * @param UserHasProject $userHasProject
     *
     * @return User
     */
    public function addUserHasProject(UserHasProject $userHasProject)
    {
        $this->userHasProjects[] = $userHasProject;

        return $this;
    }

    /**
     * Remove userHasProject
     *
     * @param UserHasProject $userHasProject
     */
    public function removeUserHasProject(UserHasProject $userHasProject)
    {
        $this->userHasProjects->removeElement($userHasProject);
    }

    /**
     * Get userHasProjects
     *
     * @return Collection
     */
    public function getUserHasProjects()
    {
        return $this->userHasProjects;
    }

    /**
     * Add createdTask
     *
     * @param Task $createdTask
     *
     * @return User
     */
    public function addCreatedTask(Task $createdTask)
    {
        $this->createdTasks[] = $createdTask;

        return $this;
    }

    /**
     * Remove createdTask
     *
     * @param Task $createdTask
     */
    public function removeCreatedTask(Task $createdTask)
    {
        $this->createdTasks->removeElement($createdTask);
    }

    /**
     * Get createdTasks
     *
     * @return Collection
     */
    public function getCreatedTasks()
    {
        return $this->createdTasks;
    }

    /**
     * Add createdSubTask
     *
     * @param TaskSubtask $createdSubtask
     *
     * @return User
     */
    public function addCreatedSubtask(TaskSubtask $createdSubtask)
    {
        $this->createdSubtasks[] = $createdSubtask;

        return $this;
    }

    /**
     * Remove createdTask
     *
     * @param TaskSubtask $createdSubtask
     */
    public function removeCreatedSubtask(TaskSubtask $createdSubtask)
    {
        $this->createdSubtasks->removeElement($createdSubtask);
    }

    /**
     * Get createdSubtasks
     *
     * @return Collection
     */
    public function getCreatedSubtasks()
    {
        return $this->createdSubtasks;
    }


    /**
     * Add requestedTask
     *
     * @param Task $requestedTask
     *
     * @return User
     */
    public function addRequestedTask(Task $requestedTask)
    {
        $this->requestedTasks[] = $requestedTask;

        return $this;
    }

    /**
     * Remove requestedTask
     *
     * @param Task $requestedTask
     */
    public function removeRequestedTask(Task $requestedTask)
    {
        $this->requestedTasks->removeElement($requestedTask);
    }

    /**
     * Get requestedTask
     *
     * @return Collection
     */
    public function getRequestedTask()
    {
        return $this->requestedTasks;
    }

    /**
     * Add followed task
     *
     * @param Task $followedTask
     * @return User
     */
    public function addFollowedTask(Task $followedTask)
    {
        $this->followedTasks[] = $followedTask;

        return $this;
    }

    /**
     * Remove followed task
     *
     * @param Task $followedTask
     */
    public function removeFollowedTask(Task $followedTask)
    {
        $this->followedTasks->removeElement($followedTask);
    }

    /**
     * Get followed tasks
     *
     * @return ArrayCollection
     */
    public function getFollowedTasks()
    {
        return $this->followedTasks;
    }

    /**
     * Add taskHasAssignedUser
     *
     * @param TaskHasAssignedUser $taskHasAssignedUser
     *
     * @return User
     */
    public function addTaskHasAssignedUser(TaskHasAssignedUser $taskHasAssignedUser)
    {
        $this->taskHasAssignedUsers[] = $taskHasAssignedUser;

        return $this;
    }

    /**
     * Remove taskHasAssignedUser
     *
     * @param TaskHasAssignedUser $taskHasAssignedUser
     */
    public function removeTaskHasAssignedUser(TaskHasAssignedUser $taskHasAssignedUser)
    {
        $this->taskHasAssignedUsers->removeElement($taskHasAssignedUser);
    }

    /**
     * Get taskHasAssignedUsers
     *
     * @return Collection
     */
    public function getTaskHasAssignedUsers()
    {
        return $this->taskHasAssignedUsers;
    }

    /**
     * Add comment to task
     *
     * @param Comment $comment
     * @return User
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment from task
     *
     * @param Comment $comment
     */
    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add filter
     *
     * @param Filter $filter
     *
     * @return Project
     */
    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Remove filter
     *
     * @param Filter $filter
     */
    public function removeFilter(Filter $filter)
    {
        $this->filters->removeElement($filter);
    }

    /**
     * Get filters
     *
     * @return ArrayCollection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set userRole
     *
     * @param UserRole $userRole
     *
     * @return User
     */
    public function setUserRole(UserRole $userRole)
    {
        $this->user_role = $userRole;

        return $this;
    }

    /**
     * Get userRole
     *
     * @return User
     */
    public function getUserRole()
    {
        return $this->user_role;
    }

    /**
     * Add createdNotification
     *
     * @param $notification
     *
     * @return User
     */
    public function addCreatedNotification(Notification $notification)
    {
        $this->createdNotifications[] = $notification;

        return $this;
    }

    /**
     * Remove createdNotification
     *
     * @param Notification $notification
     */
    public function removeCreatedNotification(Notification $notification)
    {
        $this->createdNotifications->removeElement($notification);
    }

    /**
     * Get createdNotifications
     *
     * @return Collection
     */
    public function getCreatedNotifications()
    {
        return $this->createdNotifications;
    }

    /**
     * Add notification
     *
     * @param $notification
     *
     * @return User
     */
    public function addNotification(Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove Notification
     *
     * @param Notification $notification
     */
    public function removeNotification(Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get Notifications
     *
     * @return Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}