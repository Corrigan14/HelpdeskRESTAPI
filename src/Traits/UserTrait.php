<?php

namespace Traits;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\UserHasProject;
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
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Task", mappedBy="requested_by")
     * @Exclude()
     *
     * @var ArrayCollection
     */
    private $requestedTasks;

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

}