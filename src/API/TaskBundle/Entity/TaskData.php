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
     * @ORM\Column(name="value", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $value;

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
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
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
     * @param string $value
     *
     * @return TaskData
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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
