<?php

namespace API\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var bool
     *
     * @ORM\Column(name="internal", type="boolean")
     */
    private $internal;

    /**
     * @var bool
     *
     * @ORM\Column(name="email", type="boolean")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_to", type="string", length=255, nullable=true)
     */
    private $emailTo;

    /**
     * @var string
     *
     * @ORM\Column(name="email_cc", type="string", length=255, nullable=true)
     */
    private $emailCc;

    /**
     * @var string
     *
     * @ORM\Column(name="email_bcc", type="string", length=255, nullable=true)
     */
    private $emailBcc;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


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
     * @param string $emailTo
     *
     * @return Comment
     */
    public function setEmailTo($emailTo)
    {
        $this->emailTo = $emailTo;

        return $this;
    }

    /**
     * Get emailTo
     *
     * @return string
     */
    public function getEmailTo()
    {
        return $this->emailTo;
    }

    /**
     * Set emailCc
     *
     * @param string $emailCc
     *
     * @return Comment
     */
    public function setEmailCc($emailCc)
    {
        $this->emailCc = $emailCc;

        return $this;
    }

    /**
     * Get emailCc
     *
     * @return string
     */
    public function getEmailCc()
    {
        return $this->emailCc;
    }

    /**
     * Set emailBcc
     *
     * @param string $emailBcc
     *
     * @return Comment
     */
    public function setEmailBcc($emailBcc)
    {
        $this->emailBcc = $emailBcc;

        return $this;
    }

    /**
     * Get emailBcc
     *
     * @return string
     */
    public function getEmailBcc()
    {
        return $this->emailBcc;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Comment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}

