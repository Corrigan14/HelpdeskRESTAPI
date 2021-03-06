<?php

namespace API\TaskBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation\Exclude;

/**
 * CompanyAttribute
 *
 * @ORM\Table(name="company_attribute")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\CompanyAttributeRepository")
 * @UniqueEntity("title")
 */
class CompanyAttribute
{
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
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Title of attribute is required")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     * @Assert\NotBlank(message="Type of attribute is required")
     * @Assert\Type("string")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="text", nullable=true)
     */
    private $options;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", options={"default":1})
     * @ReadOnly()
     */
    private $is_active = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="required", type="boolean", options={"default":1})
     */
    private $required = true;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var CompanyData
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\CompanyData", mappedBy="companyAttribute")
     * @Exclude()
     */
    private $companyData;

    /**
     * CompanyAttribute constructor.
     */
    public function __construct()
    {
        $this->companyData = new ArrayCollection();
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
     * @return CompanyAttribute
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
     * Set type
     *
     * @param string $type
     *
     * @return CompanyAttribute
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set options
     *
     * @param array $options
     *
     * @return CompanyAttribute
     */
    public function setOptions($options)
    {
        if (!\is_array($options) && 'null' === strtolower($options)) {
            $this->options = null;
        } else {
            $this->options = json_encode($options);
        }

        return $this;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return json_decode($this->options, true);
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return CompanyAttribute
     */
    public function setIsActive($isActive)
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
     * @return bool
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * @param bool|string $required
     * @return $this
     */
    public function setRequired($required)
    {
        if (\is_string($required)) {
            $required = ($required === 'true' || $required == 1);
        }

        $this->required = $required;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRequired():bool
    {
        return $this->required;
    }

    /**
     * Add companyData
     *
     * @param CompanyData $companyDatum
     *
     * @return CompanyAttribute
     */
    public function addCompanyDatum(CompanyData $companyDatum)
    {
        $this->companyData[] = $companyDatum;

        return $this;
    }

    /**
     * Remove companyData
     *
     * @param CompanyData $companyDatum
     */
    public function removeCompanyDatum(CompanyData $companyDatum)
    {
        $this->companyData->removeElement($companyDatum);
    }

    /**
     * Get companyData
     *
     * @return Collection
     */
    public function getCompanyData()
    {
        return $this->companyData;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return CompanyAttribute
     */
    public function setDescription($description)
    {
        if ('null' === strtolower($description)) {
            $this->description = null;
        } else {
            $this->description = $description;
        }

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
}
