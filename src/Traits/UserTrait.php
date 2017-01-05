<?php

namespace Traits;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Tag;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class UserTrait
 *
 * @package Traits
 */
trait UserTrait
{
    /**
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\Tag", mappedBy="createdBy")
     * @Serializer\Exclude()
     *
     * @var Tag
     */
    private $tags;


    /**
     * Add tag
     *
     * @param Tag $tag
     * @return User
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     */
    public function getTags()
    {
        return $this->tags;
    }

}