<?php

namespace API\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Imap
 *
 * @ORM\Table(name="imap")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\ImapRepository")
 */
class Imap
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
     * /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", length=255)
     * @Assert\NotBlank(message="Host is required")
     * @Assert\Type("string")
     */
    private $host;

    /**
     * @var int
     *
     * @ORM\Column(name="port", type="integer")
     * @Assert\NotBlank(message="Port is required")
     */
    private $port;

    /**
     * @var string
     *
     * /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="Name is required")
     * @Assert\Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     * @Assert\NotBlank(message="Password is required")
     * @Assert\Type("string")
     */
    private $password;

    /**
     * @var bool
     *
     * @ORM\Column(name="`ssl`", type="boolean", options={"default":0})
     */
    private $ssl;

    /**
     * @var string
     *
     * @ORM\Column(name="inbox_email", type="string", length=255)
     * @Assert\NotBlank(message="Inbox email is required")
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @Assert\Type("string")
     */
    private $inbox_email;

    /**
     * @var string
     *
     * @ORM\Column(name="move_email", type="string", length=255)
     * @Assert\NotBlank(message="Move email is required")
     * @Assert\Type("string")
     */
    private $move_email;

    /**
     * @var bool
     *
     * @ORM\Column(name="ignore_certificate", type="boolean", options={"default":0})
     */
    private $ignore_certificate;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\Type("string")
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", options={"default: 1"})
     * @Serializer\ReadOnly()
     */
    private $is_active;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Project", inversedBy="imaps")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
     * @Serializer\ReadOnly()
     */
    private $project;

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
     * Set host
     *
     * @param string $host
     *
     * @return Imap
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set port
     *
     * @param integer $port
     *
     * @return Imap
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Imap
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Imap
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set inboxEmail
     *
     * @param string $inboxEmail
     *
     * @return Imap
     */
    public function setInboxEmail($inboxEmail)
    {
        $this->inbox_email = $inboxEmail;

        return $this;
    }

    /**
     * Get inboxEmail
     *
     * @return string
     */
    public function getInboxEmail()
    {
        return $this->inbox_email;
    }

    /**
     * Set moveEmail
     *
     * @param string $moveEmail
     *
     * @return Imap
     */
    public function setMoveEmail($moveEmail)
    {
        $this->move_email = $moveEmail;

        return $this;
    }

    /**
     * Get moveEmail
     *
     * @return string
     */
    public function getMoveEmail()
    {
        return $this->move_email;
    }

    /**
     * Set ignoreCertificate
     *
     * @param boolean|string $ignoreCertificate
     *
     * @return Imap
     */
    public function setIgnoreCertificate($ignoreCertificate)
    {
        if (\is_string($ignoreCertificate)) {
            $ignoreCertificate = ($ignoreCertificate === 'true' || $ignoreCertificate == 1);
        }
        $this->ignore_certificate = $ignoreCertificate;

        return $this;
    }

    /**
     * Get ignoreCertificate
     *
     * @return bool
     */
    public function getIgnoreCertificate()
    {
        return $this->ignore_certificate;
    }

    /**
     * Set ssl
     *
     * @param boolean|string $ssl
     *
     * @return Imap
     */
    public function setSsl($ssl)
    {
        if (\is_string($ssl)) {
            $ssl = ($ssl === 'true' || $ssl == 1);
        }

        $this->ssl = $ssl;

        return $this;
    }

    /**
     * Get ssl
     *
     * @return bool
     */
    public function getSsl()
    {
        return $this->ssl;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Imap
     */
    public function setDescription($description)
    {
        if ('null' === strtolower($description)) {
            $this->description = null;
        } else {
            $this->description = $description;
        }

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
     * Set isActive
     *
     * @param boolean|string $isActive
     *
     * @return Imap
     */
    public function setIsActive($isActive)
    {
        if (\is_string($isActive)) {
            $isActive = ($isActive === 'true' || $isActive == 1);
        }

        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set project
     *
     * @param Project $project
     *
     * @return Imap
     */
    public function setProject(Project $project)
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
}
