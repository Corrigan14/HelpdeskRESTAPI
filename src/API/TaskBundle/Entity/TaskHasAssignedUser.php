<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TaskHasAssignedUser
 *
 * @ORM\Table(name="task_has_assigned_user")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\TaskHasAssignedUserRepository")
 */
class TaskHasAssignedUser
{
    use TimestampableEntity;

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
     * @ORM\Column(name="gps", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $gps;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="status_date", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $status_date;

    /**
     * @var int
     *
     * @ORM\Column(name="time_spent", type="integer", nullable=true)
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    private $time_spent;

    /**
     * @ORM\Column(name="actual", type="boolean", options={"default":1})
     * @Serializer\ReadOnly()
     *
     * @var bool
     */
    private $actual = true;

    /**
     * @var Status
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Status", inversedBy="taskHasAssignedUsers")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $status;

    /**
     * @var Task
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Task", inversedBy="taskHasAssignedUsers")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $task;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="taskHasAssignedUsers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $user;

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
     * Set statusDate
     *
     * @param \DateTime $statusDate
     *
     * @return TaskHasAssignedUser
     */
    public function setStatusDate($statusDate)
    {
        $this->status_date = $statusDate;

        return $this;
    }

    /**
     * Get statusDate
     *
     * @return \DateTime|int
     */
    public function getStatusDate()
    {
        if ($this->status_date) {
            return $this->status_date->getTimestamp();
        } else {
            return $this->status_date;
        }
    }

    /**
     * Set timeSpent
     *
     * @param integer $timeSpent
     *
     * @return TaskHasAssignedUser
     */
    public function setTimeSpent($timeSpent)
    {
        $this->time_spent = $timeSpent;

        return $this;
    }

    /**
     * Get timeSpent
     *
     * @return int
     */
    public function getTimeSpent()
    {
        return $this->time_spent;
    }

    /**
     * Set status
     *
     * @param Status $status
     *
     * @return TaskHasAssignedUser
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set task
     *
     * @param \API\TaskBundle\Entity\Task $task
     *
     * @return TaskHasAssignedUser
     */
    public function setTask(\API\TaskBundle\Entity\Task $task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return \API\TaskBundle\Entity\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set user
     *
     * @param \API\CoreBundle\Entity\User $user
     *
     * @return TaskHasAssignedUser
     */
    public function setUser(\API\CoreBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \API\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set gps
     *
     * @param string $gps
     *
     * @return TaskHasAssignedUser
     */
    public function setGps($gps)
    {
        $this->gps = $gps;

        return $this;
    }

    /**
     * Get gps
     *
     * @return string
     */
    public function getGps()
    {
        return $this->gps;
    }

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

    /**
     * Set actual
     *
     * @param boolean $actual
     *
     * @return TaskHasAssignedUser
     */
    public function setActual($actual)
    {
        $this->actual = $actual;

        return $this;
    }

    /**
     * Get actual
     *
     * @return boolean
     */
    public function getActual()
    {
        return $this->actual;
    }
}
