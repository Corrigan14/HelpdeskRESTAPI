<?php

namespace API\TaskBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation as Serializer;

/**
 * Status
 *
 * @ORM\Table(name="status")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\StatusRepository")
 * @UniqueEntity("title")
 */
class Status
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
     * @ORM\Column(name="title", type="string", length=255, unique=true)
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
     * @ORM\Column(name="color", type="string", length=45)
     * @Assert\NotBlank(message="Color is required")
     * @Assert\Type("string")
     *
     * @var string
     */
    private $color;

    /**
     * @ORM\Column(name="is_active", type="boolean", options={"default":1})
     * @ReadOnly()
     *
     * @var bool
     */
    private $is_active = true;

    /**
     * @var int
     *
     * @ORM\Column(name="`order`", type="integer")
     * @Assert\NotBlank(message="Order number is required!")
     */
    private $order;

    /**
     * @ORM\Column(name="`default`", type="boolean", options={"default":0})
     * @ReadOnly()
     *
     * @var bool
     */
    private $default = false;

    /**
     * @var string
     *
     * @ORM\Column(name="`function`", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $function;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\TaskHasAssignedUser", mappedBy="status")
     * @Serializer\Exclude()
     */
    private $taskHasAssignedUsers;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Task", mappedBy="status")
     * @Serializer\Exclude()
     */
    private $tasks;

    /**
     * Status constructor.
     */
    public function __construct()
    {
        $this->taskHasAssignedUsers = new ArrayCollection();
        $this->tasks = new ArrayCollection();
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
     * @return Status
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
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Status
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
     * Add taskHasAssignedUser
     *
     * @param TaskHasAssignedUser $taskHasAssignedUser
     *
     * @return Status
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
     * Set description
     *
     * @param string $description
     *
     * @return Status
     */
    public function setDescription($description)
    {
        if ('null' === strtolower($description)) {
            $this->description = null;
        } else {
            $this->description = $description;
        }

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
     * Set color
     *
     * @param string $color
     *
     * @return Status
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return Status
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set default
     *
     * @param boolean $default
     *
     * @return Status
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get default
     *
     * @return boolean
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Set function
     *
     * @param string $function
     *
     * @return Status
     */
    public function setFunction($function)
    {
        if ('null' === strtolower($function)) {
            $this->function = null;
        } else {
            $this->function = $function;
        }

        return $this;
    }

    /**
     * Get function
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Add task.
     *
     * @param Task $task
     *
     * @return Status
     */
    public function addTask(Task $task)
    {
        $this->tasks[] = $task;

        return $this;
    }

    /**
     * Remove task.
     *
     * @param Task $task
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeTask(Task $task)
    {
        return $this->tasks->removeElement($task);
    }

    /**
     * Get tasks.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTasks()
    {
        return $this->tasks;
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
        } else {
            return $this->updatedAt;
        }
    }
}
