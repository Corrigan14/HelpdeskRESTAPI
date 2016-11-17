<?php


namespace API\CoreBundle\Services\Traits;


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
trait AttachableEntity
{
    /**
     * @var string
     * @ORM\Column(name="attachments", type="text", nullable=true)
     *
     */
    private $attachments;


    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return unserialize($this->attachments);
    }


    /**
     * @param array $attachmentNames
     */
    public function setAttachments(array $attachmentNames)
    {
        $this->attachments = serialize($attachmentNames);
    }
}