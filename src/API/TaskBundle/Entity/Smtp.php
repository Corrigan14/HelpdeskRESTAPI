<?php

namespace API\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Smtp
 *
 * @ORM\Table(name="smtp")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\SmtpRepository")
 */
class Smtp
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
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank(message="Email is required")
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @Assert\Type("string")
     */
    private $email;

    /**
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
     * @var bool
     *
     * @ORM\Column(name="`tls`", type="boolean", options={"default":1})
     */
    private $tls;

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
     * @return Smtp
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
     * @return Smtp
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
     * Set email
     *
     * @param string $email
     *
     * @return Smtp
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Smtp
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
     * @return Smtp
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
     * Set ssl
     *
     * @param boolean $ssl
     *
     * @return Smtp
     */
    public function setSsl($ssl)
    {
        if (is_string($ssl)) {
            $ssl = ($ssl === 'true' || $ssl == 1) ? true : false;
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
     * Set tls
     *
     * @param boolean $tls
     *
     * @return Smtp
     */
    public function setTls($tls)
    {
        if (is_string($tls)) {
            $tls = ($tls === 'true' || $tls == 1) ? true : false;
        }

        $this->tls = $tls;

        return $this;
    }

    /**
     * Get tls
     *
     * @return bool
     */
    public function getTls()
    {
        return $this->tls;
    }
}
