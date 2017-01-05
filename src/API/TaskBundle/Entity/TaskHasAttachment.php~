<?php

namespace API\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TaskHasAttachment
 *
 * @ORM\Table(name="task_has_attachment")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\TaskHasAttachmentRepository")
 */
class TaskHasAttachment
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
     * @ORM\Column(name="slug", type="string", length=128)
     * @Assert\NotBlank(message="Slug of attachment is required")
     * @Assert\Type("string")
     */
    private $slug;

    /**
     * @var Task
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Task", inversedBy="taskHasAttachments")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=false)
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
     * Set slug
     *
     * @param string $slug
     *
     * @return TaskHasAttachment
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set task
     *
     * @param Task $task
     *
     * @return TaskHasAttachment
     */
    public function setTask(Task $task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }
}
