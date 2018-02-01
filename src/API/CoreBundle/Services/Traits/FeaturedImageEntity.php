<?php


namespace API\CoreBundle\Services\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;


/**
 * Standardize how attachments are added to an entity
 *
 * We use a file entity to store the attachment and we are just saving the slug to be able
 * to access it via url
 *
 * @package API\CoreBundle\Traits
 */
trait FeaturedImageEntity
{
    /**
     * @ORM\Column(name="image", length=128, nullable=true)
     */
    private $image;


    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }


    /**
     * @param String $image
     */
    public function setImage(String $image)
    {
        $this->image =$image;
    }
}