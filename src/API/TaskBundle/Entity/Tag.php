<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ReadOnly;

/**
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\TagRepository")
 * @ORM\Table(name="tag")
 * @UniqueEntity("title")
 */
class Tag
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @ReadOnly()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false, unique=true)
     * @Assert\Type("string")
     * @Assert\NotBlank(message="Title is required")
     */
    private $title;

    /**
     * @ORM\Column(name="color", type="string", length=45)
     * @Assert\NotBlank(message="Color is required")
     * @Assert\Type("string")
     *
     * @var string
     */
    private $color;

    /**
     * @ORM\Column(name="public", type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    private $public;

    /**
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="tags")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * @ReadOnly()
     *
     * @var User
     */
    private $createdBy;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="API\TaskBundle\Entity\Task", mappedBy="tags")
     * @Serializer\Exclude()
     */
    private $tasks;

    /**
     * Tag constructor.
     */
    public function __construct()
    {
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
     * @return Tag
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
     * Set color
     *
     * @param string $color
     *
     * @return Tag
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
     * Set public
     *
     * @param boolean $public
     *
     * @return Tag
     */
    public function setPublic($public)
    {
        if (is_string($public)) {
            $public = ($public === 'true' || $public == 1) ? true : false;
        }

        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return Tag
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
     * Add task
     *
     * @param Task $task
     *
     * @return Tag
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
     * @return ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}
