<?php

namespace API\TaskBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\TaskHasAssignedUser", mappedBy="status")
     * @Serializer\Exclude()
     */
    private $taskHasAssignedUsers;

    /**
     * Status constructor.
     */
    public function __construct()
    {
        $this->taskHasAssignedUsers = new ArrayCollection();
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
}
