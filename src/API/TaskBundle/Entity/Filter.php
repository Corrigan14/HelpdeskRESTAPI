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
     * @Assert\NotBlank(message="Filters title is required!")
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
     * Array of task filters
     *
     * @var string
     *
     * @ORM\Column(name="filter", type="text")
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
     * @ORM\Column(name="is_active", type="boolean", options={"default":1}, nullable=true)
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
     * @var string
     *
     * @ORM\Column(name="icon_class", type="string", length=255, nullable=false)
     * @Assert\Type("string")
     * @Assert\NotBlank(message="Filters Icon class is required")
     */
    private $icon_class;

    /**
     * @var int
     *
     * @ORM\Column(name="`order`", type="integer", nullable=false)
     * @Assert\NotBlank(message="Filters Order is required")
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="columns", type="text", nullable = true)
     */
    private $columns;

    /**
     * @var string
     *
     * @ORM\Column(name="columns_task_attributes", type="text", nullable = true)
     */
    private $columns_task_attributes;

    /**
     * @var bool
     *
     * @ORM\Column(name="users_remembered", type="boolean", options={"default":0})
     * @Serializer\ReadOnly()
     */
    private $users_remembered;

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
        if (is_string($public)) {
            $public = ($public === 'true' || $public == 1) ? true : false;
        }

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
     * @param $filter
     *
     * @return Filter
     */
    public function setFilter($filter)
    {
        $this->filter = json_encode($filter);

        return $this;
    }

    /**
     * Get filter
     *
     * @return array
     */
    public function getFilter()
    {
        return json_decode($this->filter, true);
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
        if (is_string($report)) {
            $report = ($report === 'true' || $report == 1) ? true : false;
        }

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
     * Set default
     *
     * @param boolean $default
     *
     * @return Filter
     */
    public function setDefault($default)
    {
        if (is_string($default)) {
            $default = ($default === 'true' || $default == 1) ? true : false;
        }

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
     * Set iconClass
     *
     * @param string $iconClass
     *
     * @return Filter
     */
    public function setIconClass($iconClass)
    {
        $this->icon_class = $iconClass;

        return $this;
    }

    /**
     * Get iconClass
     *
     * @return string
     */
    public function getIconClass()
    {
        return $this->icon_class;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return Filter
     */
    public function setOrder($order)
    {
        $this->order = (int)$order;

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
     * Set columns
     *
     * @param array $columns
     *
     * @return Filter
     */
    public function setColumns(array $columns)
    {
        $this->columns = json_encode($columns);

        return $this;
    }

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns()
    {
        return json_decode($this->columns);
    }

    /**
     * Set columnsAddedParams
     *
     * @param array $columnsAddedParams
     *
     * @return Filter
     */
    public function setColumnsTaskAttributes(array $columnsAddedParams)
    {
        $this->columns_task_attributes = json_encode($columnsAddedParams);

        return $this;
    }

    /**
     * Get columnsAddedParams
     *
     * @return array
     */
    public function getColumnsTaskAttributes()
    {
        return json_decode($this->columns_task_attributes);
    }

    /**
     * Set usersRemembered
     *
     * @param boolean $usersRemembered
     *
     * @return Filter
     */
    public function setUsersRemembered($usersRemembered)
    {
        $this->users_remembered = $usersRemembered;

        return $this;
    }

    /**
     * Get usersRemembered
     *
     * @return boolean
     */
    public function getUsersRemembered()
    {
        return $this->users_remembered;
    }
}
