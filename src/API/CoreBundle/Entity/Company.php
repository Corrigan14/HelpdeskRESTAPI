<?php

namespace API\CoreBundle\Entity;

use API\TaskBundle\Entity\Task;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Traits\CompanyTrait;

/**
 * Company
 *
 * @ORM\Table(name="company")
 * @ORM\Entity(repositoryClass="API\CoreBundle\Repository\CompanyRepository")
 * @UniqueEntity("ico")
 * @UniqueEntity("dic")
 * @UniqueEntity("ic_dph")
 * @ExclusionPolicy("none")
 */
class Company implements \Serializable
{
    use CompanyTrait;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ReadOnly()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(message="Name of company is required")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="ico", type="string", length=255, nullable=true, unique=true)
     * @Assert\Type("string")
     */
    private $ico;

    /**
     * @var string
     *
     * @ORM\Column(name="dic", type="string", length=255, nullable=true, unique=true)
     * @Assert\Type("string")
     */
    private $dic;

    /**
     * @var string
     *
     * @ORM\Column(name="ic_dph", type="string", length=255, nullable=true, unique=true)
     * @Assert\Type("string")
     */
    private $ic_dph;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $country;

    /**
     * @ORM\Column(name="is_active", type="boolean", options={"default":1})
     * @ReadOnly()
     *
     * @var bool
     */
    private $is_active = true;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="company")
     * @Exclude()
     */
    private $users;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Task", mappedBy="company")
     * @Serializer\ReadOnly()
     */
    private $tasks;


    /**
     * Company constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->tasks = new ArrayCollection();
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
     * @return Company
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
     * Set ico
     *
     * @param  string $ico
     *
     * @return Company
     */
    public function setIco($ico)
    {
        if ('null' === strtolower($ico)) {
            $this->ico = null;
        } else {
            $this->ico = $ico;
        }

        return $this;
    }

    /**
     * Get ico
     *
     * @return string
     */
    public function getIco()
    {
        return $this->ico;
    }

    /**
     * Set dic
     *
     * @param string $dic
     *
     * @return Company
     */
    public function setDic($dic)
    {
        if ('null' === strtolower($dic)) {
            $this->dic = null;
        } else {
            $this->dic = $dic;
        }

        return $this;
    }

    /**
     * Get dic
     *
     * @return string
     */
    public function getDic()
    {
        return $this->dic;
    }

    /**
     * Set icDph
     *
     * @param string $icDph
     *
     * @return Company
     */
    public function setIcDph($icDph)
    {
        if ('null' === strtolower($icDph)) {
            $this->ic_dph = null;
        } else {
            $this->ic_dph = $icDph;
        }

        return $this;
    }

    /**
     * Get icDph
     *
     * @return string
     */
    public function getIcDph()
    {
        return $this->ic_dph;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Company
     */
    public function setStreet($street)
    {
        if ('null' === strtolower($street)) {
            $this->street = null;
        } else {
            $this->street = $street;
        }

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Company
     */
    public function setCity($city)
    {
        if ('null' === strtolower($city)) {
            $this->city = null;
        } else {
            $this->city = $city;
        }

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Company
     */
    public function setZip($zip)
    {
        if ('null' === strtolower($zip)) {
            $this->zip = null;
        } else {
            $this->zip = $zip;
        }

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Company
     */
    public function setCountry($country)
    {
        if ('null' === strtolower($country)) {
            $this->country = null;
        } else {
            $this->country = $country;
        }

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set isActive
     *
     * @param boolean|string $isActive
     *
     * @return Company
     */
    public function setIsActive($isActive): Company
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
     * @return boolean
     */
    public function getIsActive(): bool
    {
        return $this->is_active;
    }

    /**
     * String representation of object
     *
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        // TODO: Implement serialize() method.
    }

    /**
     * Constructs the object
     *
     * @link  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
    }

    /**
     * Add user
     *
     * @param User $user
     *
     * @return Company
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add task
     *
     * @param Task $task
     *
     * @return Company
     */
    public function addTask(Task $task)
    {
        $this->tasks[] = $task;

        return $this;
    }

    /**
     * Remove task
     *
     * @param Task $task
     */
    public function removeTask(Task $task)
    {
        $this->tasks->removeElement($task);
    }

    /**
     * Get tasks
     *
     * @return ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}
