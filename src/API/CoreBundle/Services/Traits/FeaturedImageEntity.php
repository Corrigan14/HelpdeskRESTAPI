<?php


namespace API\CoreBundle\Services\Traits;


use API\CoreBundle\Entity\File;
use Doctrine\ORM\Mapping as ORM;
//use Gedmo\Mapping\Annotation as Gedmo;


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
     * @ORM\OneToOne(targetEntity="API\CoreBundle\Entity\File")
     * @ORM\JoinColumn(name="image", referencedColumnName="id", nullable=true, onDelete="SET NULL" )
     */
    private $image;


    /**
     * @return mixed
     */
    public function getImage()
    {
        return unserialize($this->image);
    }


    /**
     * @param File $image
     */
    public function setImage(File $image)
    {
        $this->image =$image;
    }
}