<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\TagRepository")
 * @ORM\Table(name="tag")
 * @UniqueEntity("title")
 */
class Tag implements \Serializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @ReadOnly()
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Title is required")
     * @Assert\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="color", type="string", length=45)
     * @Assert\NotBlank(message="Color is required")
     * @Assert\Type("string")
     *
     * @var string
     */
    private $color;

    /**
     * @ORM\Column(name="public", type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    private $public;

    /**
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\User", inversedBy="tags")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * @ReadOnly()
     *
     * @var User
     */
    private $createdBy;

    /**
     * Tag constructor.
     */
    public function __construct()
    {

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
     * @return Tag
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
     * Set color
     *
     * @param string $color
     *
     * @return Tag
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            $this->id ,
            $this->title ,
            $this->color ,
        ]);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        list (
            $this->id ,
            $this->title ,
            $this->color ,
            ) = unserialize($serialized);
    }

    /**
     * Set public
     *
     * @param boolean $public
     *
     * @return Tag
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return Tag
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \API\CoreBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
}
