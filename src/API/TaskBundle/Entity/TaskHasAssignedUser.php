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
     * @return \DateTime
     */
    public function getStatusDate()
    {
        return $this->status_date;
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
}