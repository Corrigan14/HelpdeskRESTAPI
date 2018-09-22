<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TaskSubtask
 *
 * @ORM\Table(name="task_subtask")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\TaskSubtaskRepository")
 */
class TaskSubtask
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
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(message="Title is required")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var bool
     *
     * @ORM\Column(name="done", type="boolean", options={"default: 0"})
     */
    private $done;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="from_time", type="datetime", nullable=true)
     * @Assert\DateTime(message="FROM param has to be a correct Date Time object")
     */
    private $from;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="to_time", type="datetime", nullable=true)
     * @Assert\DateTime(message="TO param has to be a correct Date Time object")
     */
    private $to;

    /**
     * @var float
     *
     * @ORM\Column(name="hours", type="float", nullable=true)
     * @Assert\Type("float", message="The value {{ value }} is not a valid {{ type }}")
     */
    private $hours;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="createdSubtasks")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $createdBy;

    /**
     * @var Task
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Task", inversedBy="subtasks")
     * @ORM\JoinColumn(name="task", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $task;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return TaskSubtask
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set done.
     *
     * @param bool|string $done
     *
     * @return TaskSubtask
     */
    public function setDone($done)
    {
        if (\is_string($done)) {
            $done = ($done === 'true' || $done == 1);
        }

        $this->done = $done;

        return $this;
    }


    /**
     * Get done.
     *
     * @return bool
     */
    public function getDone(): bool
    {
        return $this->done;
    }

    /**
     * Set createdBy.
     *
     * @param User $createdBy
     *
     * @return TaskSubtask
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set task.
     *
     * @param Task $task
     *
     * @return TaskSubtask
     */
    public function setTask(Task $task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task.
     *
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
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
        }

        return $this->createdAt;
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
        }

        return $this->updatedAt;
    }


    /**
     * Set from.
     *
     * @param \DateTime|null|integer $from
     *
     * @return TaskSubtask
     */
    public function setFrom($from = null)
    {
        if (\is_int($from) && null !== $from) {
            $dateTimeUnix = new \DateTime("@$from");
            $this->from = $dateTimeUnix;
        } else {
            $this->from = $from;
        }
        return $this;
    }

    /**
     * Get from.
     *
     * @return integer|null|\DateTime
     */
    public function getFrom()
    {
        if ($this->from) {
            return $this->from->getTimestamp();
        }

        return $this->from;
    }

    /**
     * Set to.
     *
     * @param \DateTime|null|integer $to
     *
     * @return TaskSubtask
     */
    public function setTo($to = null)
    {
        if (\is_int($to) && null !== $to) {
            $dateTimeUnix = new \DateTime("@$to");
            $this->to = $dateTimeUnix;
        } else {
            $this->to = $to;
        }
        return $this;
    }

    /**
     * Get to.
     *
     * @return integer|null|\DateTime
     */
    public function getTo()
    {
        if ($this->to) {
            return $this->to->getTimestamp();
        }

        return $this->to;
    }

    /**
     * Set hours.
     *
     * @param float|null $hours
     *
     * @return TaskSubtask
     */
    public function setHours($hours = null)
    {
        if ($hours) {
            $hours = (float)$hours;
            $this->hours = $hours;
        }

        return $this;
    }

    /**
     * Get hours.
     *
     * @return float
     */
    public function getHours()
    {
        return $this->hours;
    }
}
