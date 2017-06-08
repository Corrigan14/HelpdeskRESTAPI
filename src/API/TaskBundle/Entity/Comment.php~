<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\CommentRepository")
 */
class Comment
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
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Assert\NotBlank(message="Body of comment is required")
     * @Assert\Type("string")
     */
    private $body;

    /**
     * @var bool
     *
     * @ORM\Column(name="internal", type="boolean", nullable=true,  options={"default":0})
     */
    private $internal;

    /**
     * @var bool
     *
     * @ORM\Column(name="email", type="boolean", nullable=true,  options={"default":0})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_to", type="text", nullable=true)
     * @Assert\Type("string")
     */
    private $email_to;

    /**
     * @var string
     *
     * @ORM\Column(name="email_cc", type="text", nullable=true)
     * @Assert\Type("string")
     */
    private $email_cc;

    /**
     * @var string
     *
     * @ORM\Column(name="email_bcc", type="text", nullable=true)
     * @Assert\Type("string")
     */
    private $email_bcc;

    /**
     * @var Task
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Task", inversedBy="comments")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=false)
     * @Serializer\Exclude()
     */
    private $task;

    /**
     * Mapping entity
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Comment", mappedBy="comment", cascade={"persist", "remove"})
     * @Serializer\Exclude()
     */
    private $inversedComment;

    /**
     * Sub-comments
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Comment", inversedBy="inversedComment")
     * @ORM\JoinColumn(name="parent_comment_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @Serializer\ReadOnly()
     */
    private $comment;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $createdBy;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\CommentHasAttachment", mappedBy="comment", cascade={"persist", "remove"})
     * @Serializer\ReadOnly()
     */
    private $commentHasAttachments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inversedComment = new ArrayCollection();
        $this->commentHasAttachments = new ArrayCollection();
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
     * @return Comment
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
     * Set body
     *
     * @param string $body
     *
     * @return Comment
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set internal
     *
     * @param boolean $internal
     *
     * @return Comment
     */
    public function setInternal($internal)
    {
        if (is_string($internal)) {
            $internal = ($internal === 'true' || $internal == 1) ? true : false;
        }

        $this->internal = $internal;

        return $this;
    }

    /**
     * Get internal
     *
     * @return bool
     */
    public function getInternal()
    {
        return $this->internal;
    }

    /**
     * Set email
     *
     * @param boolean $email
     *
     * @return Comment
     */
    public function setEmail($email)
    {
        if (is_string($email)) {
            $email = ($email === 'true' || $email == 1) ? true : false;
        }

        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return bool
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set emailTo
     *
     * @param array $emailTo
     *
     * @return Comment
     */
    public function setEmailTo($emailTo)
    {
        $this->email_to = json_encode($emailTo);

        return $this;
    }

    /**
     * Get emailTo
     *
     * @return array
     */
    public function getEmailTo()
    {
        return json_decode($this->email_to);
    }

    /**
     * Set emailCc
     *
     * @param array $emailCc
     *
     * @return Comment
     */
    public function setEmailCc($emailCc)
    {
        $this->email_cc = json_encode($emailCc);

        return $this;
    }

    /**
     * Get emailCc
     *
     * @return array
     */
    public function getEmailCc()
    {
        return json_decode($this->email_cc);
    }

    /**
     * Set emailBcc
     *
     * @param array $emailBcc
     *
     * @return Comment
     */
    public function setEmailBcc($emailBcc)
    {
        $this->email_bcc = json_encode($emailBcc);

        return $this;
    }

    /**
     * Get emailBcc
     *
     * @return array
     */
    public function getEmailBcc()
    {
        return json_decode($this->email_bcc);
    }

    /**
     * Set task
     *
     * @param Task $task
     *
     * @return Comment
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

    /**
     * Add inversedComment
     *
     * @param Comment $inversedComment
     *
     * @return Comment
     */
    public function addInversedComment(Comment $inversedComment)
    {
        $this->inversedComment[] = $inversedComment;

        return $this;
    }

    /**
     * Remove inversedComment
     *
     * @param Comment $inversedComment
     */
    public function removeInversedComment(Comment $inversedComment)
    {
        $this->inversedComment->removeElement($inversedComment);
    }

    /**
     * Get inversedComment
     *
     * @return ArrayCollection
     */
    public function getInversedComment()
    {
        return $this->inversedComment;
    }

    /**
     * Set comment
     *
     * @param Comment $comment
     *
     * @return Comment
     */
    public function setComment(Comment $comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return Comment
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
     * Add commentHasAttachment
     *
     * @param CommentHasAttachment $commentHasAttachment
     *
     * @return Comment
     */
    public function addCommentHasAttachment(CommentHasAttachment $commentHasAttachment)
    {
        $this->commentHasAttachments[] = $commentHasAttachment;

        return $this;
    }

    /**
     * Remove commentHasAttachment
     *
     * @param CommentHasAttachment $commentHasAttachment
     */
    public function removeCommentHasAttachment(CommentHasAttachment $commentHasAttachment)
    {
        $this->commentHasAttachments->removeElement($commentHasAttachment);
    }

    /**
     * Get commentHasAttachments
     *
     * @return ArrayCollection
     */
    public function getCommentHasAttachments()
    {
        return $this->commentHasAttachments;
    }
}
