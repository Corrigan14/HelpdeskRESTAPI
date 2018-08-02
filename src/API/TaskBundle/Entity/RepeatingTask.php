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
     * @Assert\NotBlank(message="Title of a repeating task is required")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var \DateTime|int
     *
     * @ORM\Column(name="start_at", type="datetime")
     * @Assert\NotBlank(message="Start at param is required")
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
     */
    private $intervalLength;

    /**
     * @var integer
     *
     * @ORM\Column(name="repeats_number", type="integer", nullable=true)
     */
    private $repeatsNumber;


    /**
     * @var integer
     *
     * @ORM\Column(name="already_repeated", type="integer", nullable=true)
     */
    private $alreadyRepeated = 0;

    /**
     * @var \DateTime|int
     *
     * @ORM\Column(name="last_repeat", type="datetime", nullable=true)
     */
    private $lastRepeat;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", options={"default":1})
     * @Serializer\ReadOnly()
     *
     */
    private $isActive = true;

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
    public function getId(): int
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
        if($startAt instanceof \DateTime){
            $this->startAt = $startAt;
            return $this;
        }

        $startAt = (int)$startAt;
        $dateTimeUnix = new \DateTime("@$startAt");
        $this->startAt = $dateTimeUnix;
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
        $this->intervalLength = (int)$intervalLength;

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
    public function setTitle($title): RepeatingTask
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set isActive
     *
     * @param boolean|string $isActive
     *
     * @return RepeatingTask
     */
    public function setIsActive($isActive): RepeatingTask
    {
        if (\is_string($isActive)) {
            $isActive = ($isActive === 'true' || $isActive == 1);
        }

        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
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
     * Set alreadyRepeated.
     *
     * @param int|null $alreadyRepeated
     *
     * @return RepeatingTask
     */
    public function setAlreadyRepeated($alreadyRepeated = null):RepeatingTask
    {
        $this->alreadyRepeated = $alreadyRepeated;

        return $this;
    }

    /**
     * Get alreadyRepeated.
     *
     * @return int|null
     */
    public function getAlreadyRepeated()
    {
        return $this->alreadyRepeated;
    }

    /**
     * Set lastRepeat.
     *
     * @param \DateTime|null $lastRepeat
     *
     * @return RepeatingTask
     */
    public function setLastRepeat($lastRepeat = null):RepeatingTask
    {
        if($lastRepeat instanceof \DateTime){
            $this->lastRepeat = $lastRepeat;
            return $this;
        }

        $lastRepeat = (int)$lastRepeat;
        $dateTimeUnix = new \DateTime("@$lastRepeat");
        $this->lastRepeat = $dateTimeUnix;
        return $this;
    }

    /**
     * Get lastRepeat.
     *
     * @return int|null
     */
    public function getLastRepeat()
    {
        if ($this->lastRepeat) {
            return $this->lastRepeat->getTimestamp();
        }

        return $this->lastRepeat;
    }
}
