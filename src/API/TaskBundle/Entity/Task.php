<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Task
 *
 * @ORM\Table(name="task")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\TaskRepository")
 */
class Task
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
     * @Assert\NotBlank(message="Title of task is required")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deadline", type="datetime", nullable=true)
     * @Assert\DateTime(message="Deadline has to be a correct Date Time object")
     */
    private $deadline;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="started_at", type="datetime", nullable=true)
     * @Assert\DateTime(message="started_at has to be a correct Date Time object")
     */
    private $startedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closed_at", type="datetime", nullable=true)
     * @Assert\DateTime(message="closed_at has to be a correct Date Time object")
     */
    private $closedAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="important", type="boolean", options={"default":0})
     */
    private $important;

    /**
     * @var string
     *
     * @ORM\Column(name="work", type="text", nullable=true)
     */
    private $work;

    /**
     * @var string
     *
     * @ORM\Column(name="work_time", type="string", nullable=true)
     */
    private $work_time;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="createdTasks")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $createdBy;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="requestedTasks")
     * @ORM\JoinColumn(name="requested_by", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $requestedBy;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Project", inversedBy="tasks")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $project;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\TaskData", mappedBy="task", cascade={"persist", "remove"})
     * @Serializer\ReadOnly()
     */
    private $taskData;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="API\CoreBundle\Entity\User", inversedBy="followedTasks", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="task_has_follower")
     * @Serializer\ReadOnly()
     */
    private $followers;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="API\TaskBundle\Entity\Tag", inversedBy="tasks", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="task_has_tag",
     *      joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *      )
     * @Serializer\ReadOnly()
     */
    private $tags;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\TaskHasAssignedUser", mappedBy="task", cascade={"persist", "remove"})
     * @Serializer\Exclude()
     */
    private $taskHasAssignedUsers;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\TaskHasAttachment", mappedBy="task", cascade={"persist", "remove"})
     * @Serializer\ReadOnly()
     */
    private $taskHasAttachments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Comment", mappedBy="task", cascade={"persist", "remove"})
     * @Serializer\ReadOnly()
     */
    private $comments;

    /**
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\Company", inversedBy="tasks")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     * @Serializer\ReadOnly()
     */
    private $company;

    /**
     * @var
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\InvoiceableItem", mappedBy="task", cascade={"persist", "remove"})
     * @Serializer\ReadOnly()
     */
    private $invoiceableItems;

    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Notification", mappedBy="task", cascade={"persist", "remove"})
     * @Serializer\Exclude()
     *
     * @var ArrayCollection
     */
    private $notifications;

    /**
     * Task constructor.
     */
    public function __construct()
    {
        $this->taskData = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->taskHasAssignedUsers = new ArrayCollection();
        $this->taskHasAttachments = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->invoiceableItems = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

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
     * @return Task
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
     * Set description
     *
     * @param string $description
     *
     * @return Task
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int|\DateTime $deadline
     * @return $this
     */
    public function setDeadline($deadline)
    {
        if (is_int($deadline) && null !== $deadline) {
            $dateTimeUnix = new \DateTime("@$deadline");
            $this->deadline = $dateTimeUnix;
        } else {
            $this->deadline = $deadline;
        }
        return $this;
    }

    /**
     * Get deadline
     *
     * @return \DateTime|int
     */
    public function getDeadline()
    {
        if ($this->deadline) {
            return $this->deadline->getTimestamp();
        } else {
            return $this->deadline;
        }
    }

    /**
     * Set important
     *
     * @param $important
     *
     * @return Task
     */
    public function setImportant($important)
    {
        if (is_string($important)) {
            $important = ($important === 'true' || $important == 1) ? true : false;
        }

        $this->important = $important;

        return $this;
    }

    /**
     * Get important
     *
     * @return bool
     */
    public function getImportant()
    {
        return $this->important;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return Task
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
     * Set requestedBy
     *
     * @param User $requestedBy
     *
     * @return Task
     */
    public function setRequestedBy(User $requestedBy)
    {
        $this->requestedBy = $requestedBy;

        return $this;
    }

    /**
     * Get requestedBy
     *
     * @return User
     */
    public function getRequestedBy()
    {
        return $this->requestedBy;
    }

    /**
     * Set project
     *
     * @param Project $project
     *
     * @return Task
     */
    public function setProject(Project $project = null)
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
     * Add taskDatum
     *
     * @param TaskData $taskDatum
     *
     * @return Task
     */
    public function addTaskDatum(TaskData $taskDatum)
    {
        $this->taskData[] = $taskDatum;

        return $this;
    }

    /**
     * Remove taskDatum
     *
     * @param TaskData $taskDatum
     */
    public function removeTaskDatum(TaskData $taskDatum)
    {
        $this->taskData->removeElement($taskDatum);
    }

    /**
     * Get taskData
     *
     * @return ArrayCollection
     */
    public function getTaskData()
    {
        return $this->taskData;
    }

    /**
     * Add follower
     *
     * @param User $follower
     *
     * @return Task
     */
    public function addFollower(User $follower)
    {
        $this->followers[] = $follower;

        return $this;
    }

    /**
     * Remove follower
     *
     * @param User $follower
     */
    public function removeFollower(User $follower)
    {
        $this->followers->removeElement($follower);
    }

    /**
     * Get followers
     *
     * @return ArrayCollection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Add tag
     *
     * @param Tag $tag
     *
     * @return Task
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add taskHasAssignedUser
     *
     * @param TaskHasAssignedUser $taskHasAssignedUser
     *
     * @return Task
     */
    public function addTaskHasAssignedUser(TaskHasAssignedUser $taskHasAssignedUser)
    {
        $this->taskHasAssignedUsers[] = $taskHasAssignedUser;

        return $this;
    }

    /**
     * Remove taskHasAssignedUser
     *
     * @param TaskHasAssignedUser $taskHasAssignedUser
     */
    public function removeTaskHasAssignedUser(TaskHasAssignedUser $taskHasAssignedUser)
    {
        $this->taskHasAssignedUsers->removeElement($taskHasAssignedUser);
    }

    /**
     * Get taskHasAssignedUsers
     *
     * @return ArrayCollection
     */
    public function getTaskHasAssignedUsers()
    {
        return $this->taskHasAssignedUsers;
    }

    /**
     * Add taskHasAttachment
     *
     * @param TaskHasAttachment $taskHasAttachment
     *
     * @return Task
     */
    public function addTaskHasAttachment(TaskHasAttachment $taskHasAttachment)
    {
        $this->taskHasAttachments[] = $taskHasAttachment;

        return $this;
    }

    /**
     * Remove taskHasAttachment
     *
     * @param TaskHasAttachment $taskHasAttachment
     */
    public function removeTaskHasAttachment(TaskHasAttachment $taskHasAttachment)
    {
        $this->taskHasAttachments->removeElement($taskHasAttachment);
    }

    /**
     * Get taskHasAttachments
     *
     * @return ArrayCollection
     */
    public function getTaskHasAttachments()
    {
        return $this->taskHasAttachments;
    }

    /**
     * Add comment
     *
     * @param Comment $comment
     *
     * @return Task
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param Comment $comment
     */
    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param int|\DateTime $startedAt
     * @return $this
     */
    public function setStartedAt($startedAt)
    {
        if (is_int($startedAt) && null !== $startedAt) {
            $dateTimeUnix = new \DateTime("@$startedAt");
            $this->startedAt = $dateTimeUnix;
        } else {
            $this->startedAt = $startedAt;
        }
        return $this;
    }

    /**
     * Get startedAt
     *
     * @return \DateTime|int
     */
    public function getStartedAt()
    {
        if ($this->startedAt) {
            return $this->startedAt->getTimestamp();
        } else {
            return $this->startedAt;
        }

    }

    /**
     * @param int|\DateTime $closedAt
     * @return $this
     */
    public function setClosedAt($closedAt)
    {
        if (is_int($closedAt) && null !== $closedAt) {
            $dateTimeUnix = new \DateTime("@$closedAt");
            $this->closedAt = $dateTimeUnix;
        } else {
            $this->closedAt = $closedAt;
        }
        return $this;
    }

    /**
     * Get closedAt
     *
     * @return \DateTime|int
     */
    public function getClosedAt()
    {
        if ($this->closedAt) {
            return $this->closedAt->getTimestamp();
        } else {
            return $this->closedAt;
        }
    }

    /**
     * Set work
     *
     * @param string $work
     *
     * @return Task
     */
    public function setWork($work)
    {
        $this->work = $work;

        return $this;
    }

    /**
     * Get work
     *
     * @return string
     */
    public function getWork()
    {
        return $this->work;
    }

    /**
     * Set workTime
     *
     * @param string $workTime
     *
     * @return Task
     */
    public function setWorkTime($workTime)
    {
        $this->work_time = $workTime;

        return $this;
    }

    /**
     * Get workTime
     *
     * @return string
     */
    public function getWorkTime()
    {
        return $this->work_time;
    }

    /**
     * Set company
     *
     * @param Company $company
     *
     * @return Task
     */
    public function setCompany(Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Add invoiceableItem
     *
     * @param InvoiceableItem $invoiceableItem
     *
     * @return Task
     */
    public function addInvoiceableItem(InvoiceableItem $invoiceableItem)
    {
        $this->invoiceableItems[] = $invoiceableItem;

        return $this;
    }

    /**
     * Remove invoiceableItem
     *
     * @param InvoiceableItem $invoiceableItem
     */
    public function removeInvoiceableItem(InvoiceableItem $invoiceableItem)
    {
        $this->invoiceableItems->removeElement($invoiceableItem);
    }

    /**
     * Get invoiceableItems
     *
     * @return ArrayCollection
     */
    public function getInvoiceableItems()
    {
        return $this->invoiceableItems;
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
     * Add notification
     *
     * @param Notification $notification
     *
     * @return Task
     */
    public function addNotification(Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param Notification $notification
     */
    public function removeNotification(Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
