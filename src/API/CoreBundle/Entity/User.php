<?php
namespace API\CoreBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as UniqueEntity;


/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class User implements UserInterface , \Serializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @ORM\Column(type="string")
     */
    private $roles;
    /**
     * @ORM\Column(type="string", length=64)
     *
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="UserAddress", mappedBy="user")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="UserData", mappedBy="user")
     */
    private $detailData;

    public function __construct()
    {
        $this->isActive = true;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return unserialize($this->roles);
    }

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = serialize($roles);

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
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
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id ,
            $this->username ,
            $this->password ,
            // see section on salt below
            // $this->salt,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id ,
            $this->username ,
            $this->password ,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     *
     */
    public function eraseCredentials()
    {
    }

    /**
     * Set address
     *
     * @param UserAddress $address
     *
     * @return User
     */
    public function setAddress(UserAddress $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return UserAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set detailData
     *
     * @param \API\CoreBundle\Entity\UserData $detailData
     *
     * @return User
     */
    public function setDetailData(\API\CoreBundle\Entity\UserData $detailData = null)
    {
        $this->detailData = $detailData;

        return $this;
    }

    /**
     * Get detailData
     *
     * @return \API\CoreBundle\Entity\UserData
     */
    public function getDetailData()
    {
        return $this->detailData;
    }
}
