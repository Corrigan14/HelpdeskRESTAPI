<?php

namespace Traits;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Tag;
use JMS\Serializer\Annotation as Serializer;

/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 11/17/16
 * Time: 9:03 PM
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
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

}