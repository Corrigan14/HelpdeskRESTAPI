<?php

namespace API\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserHasTag
 *
 * @ORM\Table(name="user_has_tag")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\UserHasTagRepository")
 */
class UserHasTag
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
     * @var bool
     *
     * @ORM\Column(name="private", type="boolean")
     */
    private $private;


    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\Tag", inversedBy="tags")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tag;

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
     * Set private
     *
     * @param boolean $private
     *
     * @return UserHasTag
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get private
     *
     * @return bool
     */
    public function getPrivate()
    {
        return $this->private;
    }
}

