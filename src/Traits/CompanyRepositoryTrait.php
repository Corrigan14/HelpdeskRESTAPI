<?php

namespace Traits;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CompanyRepositoryTrait - extends CompanyRepository
 *
 * @package Traits
 */
trait CompanyRepositoryTrait
{
    /**
     * @param $company
     * @return mixed
     */
    public function getFullCompanyEntity($company){
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('c');
        $query = $queryBuilder->select()
            ->where('c.id = :companyId')
            ->setParameter('companyId', $company)
            ->getQuery();

        return $query->getSingleResult();
    }
}