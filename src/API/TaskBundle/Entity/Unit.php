<?php

namespace API\TaskBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Unit
 *
 * @ORM\Table(name="unit")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\UnitRepository")
 */
class Unit
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
     * @ORM\Column(name="shortcut", type="string", length=45)
     * @Assert\NotBlank(message="Shortcut is required")
     * @Assert\Type("string")
     */
    private $shortcut;

    /**
     * @ORM\Column(name="is_active", type="boolean", options={"default":1})
     * @Serializer\ReadOnly()
     *
     * @var bool
     */
    private $is_active = true;

    /**
     * @var
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\InvoiceableItem", mappedBy="unit")
     * @Serializer\ReadOnly()
     */
    private $invoiceableItems;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->invoiceableItems = new ArrayCollection();
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
     * @return Unit
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
     * Set shortcut
     *
     * @param string $shortcut
     *
     * @return Unit
     */
    public function setShortcut($shortcut)
    {
        $this->shortcut = $shortcut;

        return $this;
    }

    /**
     * Get shortcut
     *
     * @return string
     */
    public function getShortcut()
    {
        return $this->shortcut;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Unit
     */
    public function setIsActive($isActive)
    {
        if (is_string($isActive)) {
            $isActive = $isActive === 'true' ? true : false;
        }

        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Add invoiceableItem
     *
     * @param InvoiceableItem $invoiceableItem
     *
     * @return Unit
     */
    public function addInvoiceableItem(InvoiceableItem $invoiceableItem)
    {
        $this->invoiceableItems[] = $invoiceableItem;

        return $this;
    }

    /**
     * Remove invoiceableItem
     *
     * @param InvoiceableItem $invoiceableItem
     */
    public function removeInvoiceableItem(InvoiceableItem $invoiceableItem)
    {
        $this->invoiceableItems->removeElement($invoiceableItem);
    }

    /**
     * Get invoiceableItems
     *
     * @return ArrayCollection
     */
    public function getInvoiceableItems()
    {
        return $this->invoiceableItems;
    }
}
