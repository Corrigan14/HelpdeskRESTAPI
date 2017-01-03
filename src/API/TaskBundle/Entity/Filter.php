<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Filter
 *
 * @ORM\Table(name="filter")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\FilterRepository")
 */
class Filter
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\ReadOnly()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(message="Title of task is required")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var bool
     *
     * @ORM\Column(name="public", type="boolean", options={"default":0})
     */
    private $public;

    /**
     * Serialized array of task filters
     *
     * @var string
     *
     * @ORM\Column(name="filter", type="text")
     * @Assert\NotBlank(message="Filter array is required!")
     */
    private $filter;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="filters")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $createdBy;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Project", inversedBy="filters")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=true)
     * @Serializer\ReadOnly()
     */
    private $project;


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
     * @return Filter
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
     * Set public
     *
     * @param boolean $public
     *
     * @return Filter
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return bool
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set filter
     *
     * @param array $filter
     *
     * @return Filter
     */
    public function setFilter($filter)
    {
        $this->filter = serialize($filter);

        return $this;
    }

    /**
     * Get filter
     *
     * @return array
     */
    public function getFilter()
    {
        return unserialize($this->filter);
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return Filter
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
     * Set project
     *
     * @param Project $project
     *
     * @return Filter
     */
    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }
}