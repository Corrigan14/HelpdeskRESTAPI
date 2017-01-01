<?php

namespace API\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CommentHasAttachment
 *
 * @ORM\Table(name="comment_has_attachment")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\CommentHasAttachmentRepository")
 */
class CommentHasAttachment
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
     * @var Comment
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Comment", inversedBy="commentHasAttachments")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=false)
     */
    private $comment;


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
     * @return CommentHasAttachment
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
     * Set comment
     *
     * @param Comment $comment
     *
     * @return CommentHasAttachment
     */
    public function setComment(Comment $comment)
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
}
