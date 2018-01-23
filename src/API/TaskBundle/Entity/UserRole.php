<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserRole
 *
 * @ORM\Table(name="user_role")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\UserRoleRepository")
 */
class UserRole
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
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(message="Title is required")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\Type("string")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="homepage", type="string", length=255)
     * @Assert\NotBlank(message="Homepage definition is required")
     * @Assert\Type("string")
     */
    private $homepage;

    /**
     * @var string
     *
     * @ORM\Column(name="acl", type="text")
     * @Assert\NotBlank(message="ACL rules are required")
     */
    private $acl;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", options={"default: 1"})
     */
    private $is_active;

    /**
     * @var int
     *
     * @ORM\Column(name="`order`", type="integer")
     * @Assert\NotBlank(message="Order is required")
     * @Assert\Type("integer")
     */
    private $order;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\CoreBundle\Entity\User", mappedBy="user_role")
     * @Serializer\ReadOnly()
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
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
     * @return UserRole
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
     * @return UserRole
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
     * Set homepage
     *
     * @param string $homepage
     *
     * @return UserRole
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;

        return $this;
    }

    /**
     * Get homepage
     *
     * @return string
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Set acl
     *
     * @param array $acl
     *
     * @return UserRole
     */
    public function setAcl(array $acl)
    {
        $this->acl = json_encode($acl);

        return $this;
    }

    /**
     * Get acl
     *
     * @return array
     */
    public function getAcl()
    {
        return json_decode($this->acl);
    }

    /**
     * Set isActive
     *
     * @param boolean|string $isActive
     *
     * @return UserRole
     */
    public function setIsActive($isActive)
    {
        if (\is_string($isActive)) {
            $isActive = ($isActive === 'true' || $isActive == 1) ? true : false;
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
     * Add user
     *
     * @param User $user
     *
     * @return UserRole
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return UserRole
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }
}
