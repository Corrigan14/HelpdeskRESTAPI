<?php

namespace API\TaskBundle\Entity;

use API\CoreBundle\Entity\Company;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation\Exclude;

/**
 * CompanyData
 *
 * @ORM\Table(name="company_data")
 * @ORM\Entity(repositoryClass="API\TaskBundle\Repository\CompanyDataRepository")
 */
class CompanyData
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
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     * @Assert\Type("string")
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_value", type="datetime", nullable=true)
     * @Assert\DateTime(message="dateValue has to be a correct Date Time object")
     */
    private $dateValue;

    /**
     * @var bool
     *
     * @ORM\Column(name="bool_value", type="boolean", nullable=true)
     */
    private $boolValue;

    /**
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="API\CoreBundle\Entity\Company", inversedBy="companyData")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Exclude()
     */
    private $company;

    /**
     * @var CompanyAttribute
     *
     * @ORM\ManyToOne(targetEntity="API\TaskBundle\Entity\CompanyAttribute", inversedBy="companyData")
     * @ORM\JoinColumn(name="company_attribute_id", referencedColumnName="id", nullable=false)
     */
    private $companyAttribute;


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
     * Set value
     *
     * @param string|array $value
     *
     * @return CompanyData
     */
    public function setValue($value): CompanyData
    {
        if (\is_array($value)) {
            $this->value = json_encode($value);
        } else {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        $decodedTry = json_decode($this->value);
        if (null === $decodedTry) {
            return $this->value;
        } else {
            return $decodedTry;
        }
    }

    /**
     * @param int|\DateTime $dateValue
     * @return $this
     */
    public function setDateValue($dateValue)
    {
        if (\is_int($dateValue) && null !== $dateValue) {
            $dateTimeUnix = new \DateTime("@$dateValue");
            $this->dateValue = $dateTimeUnix;
        } else {
            $this->dateValue = $dateValue;
        }
        return $this;
    }

    /**
     * Get dateValue
     *
     * @return \DateTime|int
     */
    public function getDateValue()
    {
        if ($this->dateValue) {
            return $this->dateValue->getTimestamp();
        } else {
            return $this->dateValue;
        }
    }

    /**
     * Set isActive
     *
     * @param boolean $boolValue
     *
     * @return $this
     */
    public function setBoolValue($boolValue)
    {
        $this->boolValue = $boolValue;
        return $this;
    }

    /**
     * Get boolValue
     *
     * @return bool
     */
    public function getBoolValue()
    {
        return $this->boolValue;
    }

    /**
     * Set company
     *
     * @param Company $company
     *
     * @return CompanyData
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set companyAttribute
     *
     * @param CompanyAttribute $companyAttribute
     *
     * @return CompanyData
     */
    public function setCompanyAttribute(CompanyAttribute $companyAttribute)
    {
        $this->companyAttribute = $companyAttribute;

        return $this;
    }

    /**
     * Get companyAttribute
     *
     * @return CompanyAttribute
     */
    public function getCompanyAttribute()
    {
        return $this->companyAttribute;
    }
}
