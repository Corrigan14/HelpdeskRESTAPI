<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation\Exclude;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\ProjectRepository")
 */
class Project
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ReadOnly()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(message="Title of status is required")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(name="is_active", type="boolean", options={"default":1})
     * @ReadOnly()
     *
     * @var bool
     */
    private $is_active = true;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="projects")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * @ReadOnly()
     */
    private $createdBy;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\UserHasProject", mappedBy="project")
     * @Exclude()
     */
    private $userHasProjects;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Task", mappedBy="project")
     * @Exclude()
     */
    private $tasks;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Filter", mappedBy="project")
     * @Exclude()
     */
    private $filters;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Imap", mappedBy="project")
     * @Exclude()
     */
    private $imaps;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Notification", mappedBy="task", cascade={"persist", "remove"})
     * @Serializer\Exclude()
     *
     * @var ArrayCollection
     */
    private $notifications;

    /**
     * Project constructor.
     */
    public function __construct()
    {
        $this->userHasProjects = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->filters = new ArrayCollection();
        $this->imaps = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Project
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Project
     */
    public function setIsActive($isActive)
    {
        if (is_string($isActive)) {
            $isActive = ($isActive === 'true' || $isActive == 1) ? true : false;
        }

        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return Project
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Add userHasProject
     *
     * @param UserHasProject $userHasProject
     *
     * @return Project
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
     * @return ArrayCollection
     */
    public function getUserHasProjects()
    {
        return $this->userHasProjects;
    }

    /**
     * Add task
     *
     * @param Task $task
     *
     * @return Project
     */
    public function addTask(Task $task)
    {
        $this->tasks[] = $task;

        return $this;
    }

    /**
     * Remove task
     *
     * @param Task $task
     */
    public function removeTask(Task $task)
    {
        $this->tasks->removeElement($task);
    }

    /**
     * Get tasks
     *
     * @return Collection
     */
    public function getTasks()
    {
        return $this->tasks;
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
     * Add imap
     *
     * @param Imap $imap
     *
     * @return Project
     */
    public function addImap(Imap $imap)
    {
        $this->imaps[] = $imap;

        return $this;
    }

    /**
     * Remove imap
     *
     * @param Imap $imap
     */
    public function removeImap(Imap $imap)
    {
        $this->imaps->removeElement($imap);
    }

    /**
     * Get imaps
     *
     * @return ArrayCollection
     */
    public function getImaps()
    {
        return $this->imaps;
    }

    /**
     * EXTENSION TO TIMESTAMP TRAIT - RETURNS TIMESTAMP DATE FORMAT
     */

    /**
     * Returns createdAt.
     *
     * @return \DateTime|int
     */
    public function getCreatedAt()
    {
        if ($this->createdAt) {
            return $this->createdAt->getTimestamp();
        } else {
            return $this->createdAt;
        }
    }

    /**
     * Returns updatedAt.
     *
     * @return \DateTime|int
     */
    public function getUpdatedAt()
    {
        if ($this->updatedAt) {
            return $this->updatedAt->getTimestamp();
        }else{
            return $this->updatedAt;
        }
    }

    /**
     * Add notification
     *
     * @param Notification $notification
     *
     * @return Project
     */
    public function addNotification(Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param Notification $notification
     */
    public function removeNotification(Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
