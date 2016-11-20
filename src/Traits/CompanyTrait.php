<?php

namespace Traits;

use API\CoreBundle\Entity\Company;
use API\TaskBundle\Entity\CompanyData;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\Exclude;

/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 11/17/16
 * Time: 9:03 PM
 */
trait CompanyTrait
{
    /**
     * @var CompanyData
     *
     * @ORM\OneToMany(targetEntity="API\TaskBundle\Entity\CompanyData", mappedBy="company")
     * @Exclude()
     */
    private $companyData;

    /**
     * Add companyData
     *
     * @param CompanyData $companyDatum
     *
     * @return Company
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
}