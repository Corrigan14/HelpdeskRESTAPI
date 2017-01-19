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
     * @Assert\NotBlank(message="Filter is required!")
     */
    private $filter;

    /**
     * @var bool
     *
     * @ORM\Column(name="`report`", type="boolean", options={"default":0})
     */
    private $report;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", options={"default":1})
     * @Serializer\ReadOnly()
     */
    private $is_active;

    /**
     * @var bool
     *
     * @ORM\Column(name="`default`", type="boolean", options={"default":0})
     */
    private $default;

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
     * @param string $filter
     *
     * @return Filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
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

    /**
     * Set report
     *
     * @param boolean $report
     *
     * @return Filter
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return boolean
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Filter
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
     * Set default
     *
     * @param boolean $default
     *
     * @return Filter
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
}
