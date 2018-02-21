<?php

namespace API\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TaskData
 *
 * @ORM\Table(name="task_data")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\TaskDataRepository")
 */
class TaskData
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
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_value", type="datetime", nullable=true)
     * @Assert\DateTime(message="dateValue has to be a correct Date Time object")
     */
    private $dateValue;

    /**
     * @var bool
     *
     * @ORM\Column(name="bool_value", type="boolean", nullable=true)
     */
    private $boolValue;


    /**
     * @var TaskAttribute
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\TaskAttribute", inversedBy="taskData")
     * @ORM\JoinColumn(name="task_attribute_id", referencedColumnName="id", nullable=false)
     * @Serializer\Exclude()
     */
    private $taskAttribute;

    /**
     * @var Task
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Task", inversedBy="taskData")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=false)
     * @Serializer\Exclude()
     */
    private $task;

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
     * Set value
     *
     * @param string|array|null $value
     *
     * @return TaskData
     */
    public function setValue($value): TaskData
    {
        if (\is_array($value)) {
            $this->value = json_encode($value);
        } else {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        $decodedTry = json_decode($this->value);
        if (null === $decodedTry) {
            return $this->value;
        } else {
            return $decodedTry;
        }
    }

    /**
     * @param int|\DateTime|null $dateValue
     * @return $this
     */
    public function setDateValue($dateValue)
    {
        if (\is_int($dateValue) && null !== $dateValue) {
            $dateTimeUnix = new \DateTime("@$dateValue");
            $this->dateValue = $dateTimeUnix;
        } else {
            $this->dateValue = $dateValue;
        }
        return $this;
    }

    /**
     * Get dateValue
     *
     * @return \DateTime|int
     */
    public function getDateValue()
    {
        if ($this->dateValue) {
            return $this->dateValue->getTimestamp();
        } else {
            return $this->dateValue;
        }
    }

    /**
     * Set isActive
     *
     * @param boolean|null $boolValue
     *
     * @return $this
     */
    public function setBoolValue($boolValue)
    {
        $this->boolValue = $boolValue;
        return $this;
    }

    /**
     * Get boolValue
     *
     * @return bool
     */
    public function getBoolValue()
    {
        return $this->boolValue;
    }

    /**
     * Set taskAttribute
     *
     * @param TaskAttribute $taskAttribute
     *
     * @return TaskData
     */
    public function setTaskAttribute(TaskAttribute $taskAttribute)
    {
        $this->taskAttribute = $taskAttribute;

        return $this;
    }

    /**
     * Get taskAttribute
     *
     * @return TaskAttribute
     */
    public function getTaskAttribute()
    {
        return $this->taskAttribute;
    }

    /**
     * Set task
     *
     * @param \API\TaskBundle\Entity\Task $task
     *
     * @return TaskData
     */
    public function setTask(\API\TaskBundle\Entity\Task $task = null)
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
}
