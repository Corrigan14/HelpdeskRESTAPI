<?php
namespace API\CoreBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\MaxDepth;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="API\CoreBundle\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 * @ExclusionPolicy("none")
 */
class User implements AdvancedUserInterface , \Serializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @ReadOnly()
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank(message="Username is required")
     * @Assert\Type("string")
     *
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     * @Exclude()
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=8,
     *     minMessage="Your password must have at least {{ limit }} characters."
     * )
     * @Assert\Type("string")
     *
     * @var string
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @Assert\Type("string")
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $roles;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     * @ReadOnly()
     *
     * @var bool
     */
    private $is_active = true;

    /**
     * @ORM\Column(name="acl", type="text", nullable=true)
     *
     * @var array
     */
    private $acl;

    /**
     * @var UserData
     * @ORM\OneToOne(targetEntity="UserData", mappedBy="user", orphanRemoval=true)
     * @MaxDepth(1)
     *
     * @var UserData
     */
    private $detailData;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="users")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * @ReadOnly()
     */
    private $company;

    public function __construct()
    {
        $this->is_active = true;
        $this->acl = json_encode([]);
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
        return json_decode($this->roles);
    }

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = json_encode($roles);

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
     * @param boolean $is_active
     *
     * @return User
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * @return array
     */
    public function getAcl(): array
    {
        return json_decode($this->acl);
    }

    /**
     * @param array $acl
     */
    public function setAcl(array $acl)
    {
        $this->acl = json_encode($acl);
    }

    /**
     * Set detailData
     *
     * @param UserData $detailData
     *
     * @return User
     */
    public function setDetailData(UserData $detailData = null)
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
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {

        return $this->is_active;
    }

    /**
     * Set company
     *
     * @param Company $company
     *
     * @return User
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
}
