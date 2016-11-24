<?php

namespace Traits;
use Doctrine\DBAL\Query\QueryBuilder;

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
            ->leftJoin('c.companyData','company_data','cd')
            ->setParameter('companyId', $company)
            ->getQuery()
            ->getSingleResult();

        dump($query);
        return $query;
    }
}