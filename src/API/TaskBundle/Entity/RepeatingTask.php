<?php

namespace API\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * RepeatingTask
 *
 * @ORM\Table(name="repeating_task")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\RepeatingTaskRepository")
 */
class RepeatingTask
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
     * @Assert\NotBlank(message="Title of arepeating task is required")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="datetime")
     * @Assert\NotBlank(message="Start at param is required")
     * @Assert\DateTime(message="Start at has to be a correct Date Time object")
     */
    private $startAt;

    /**
     * @var string
     *
     * @ORM\Column(name="`interval`", type="string", length=45)
     * @Assert\NotBlank(message="Interval param is required")
     * @Assert\Type("string")
     */
    private $interval;

    /**
     * @var integer
     *
     * @ORM\Column(name="interval_length", type="integer")
     * @Assert\NotBlank(message="Interval length is required")
     * @Assert\Type("integer")
     */
    private $intervalLength;

    /**
     * @var integer
     *
     * @ORM\Column(name="repeats_number", type="integer", nullable=true)
     * @Assert\NotBlank(message="Number of repeats is required")
     */
    private $repeatsNumber;

    /**
     * @var Task
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Task", inversedBy="repeatingTasks")
     * @ORM\JoinColumn(name="task", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $task;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId():int
    {
        return $this->id;
    }

    /**
     * Set startAt.
     *
     * @param \DateTime|int $startAt
     *
     * @return RepeatingTask
     */
    public function setStartAt($startAt): RepeatingTask
    {
        if (\is_int($startAt) && null !== $startAt) {
            $dateTimeUnix = new \DateTime("@$startAt");
            $this->startAt = $dateTimeUnix;
        } else {
            $this->startAt = $startAt;
        }
        return $this;
    }

    /**
     * Get startAt.
     *
     * @return \DateTime|int
     */
    public function getStartAt()
    {
        if ($this->startAt) {
            return $this->startAt->getTimestamp();
        }

        return $this->startAt;
    }

    /**
     * Set intervalLength.
     *
     * @param string $intervalLength
     *
     * @return RepeatingTask
     */
    public function setIntervalLength($intervalLength): RepeatingTask
    {
        $this->intervalLength = $intervalLength;

        return $this;
    }

    /**
     * Get intervalLength.
     *
     * @return string
     */
    public function getIntervalLength(): string
    {
        return $this->intervalLength;
    }

    /**
     * Set interval.
     *
     * @param string $interval
     *
     * @return RepeatingTask
     */
    public function setInterval($interval): RepeatingTask
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Get interval.
     *
     * @return string
     */
    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * Set repeatsNumber.
     *
     * @param int $repeatsNumber
     *
     * @return RepeatingTask
     */
    public function setRepeatsNumber($repeatsNumber): RepeatingTask
    {
        $this->repeatsNumber = $repeatsNumber;

        return $this;
    }

    /**
     * Get repeatsNumber.
     *
     * @return int|null
     */
    public function getRepeatsNumber()
    {
        return $this->repeatsNumber;
    }

    /**
     * Set task.
     *
     * @param Task $task
     *
     * @return RepeatingTask
     */
    public function setTask(Task $task): RepeatingTask
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task.
     *
     * @return Task
     */
    public function getTask(): Task
    {
        return $this->task;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return RepeatingTask
     */
    public function setTitle($title):RepeatingTask
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle():string
    {
        return $this->title;
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
}
