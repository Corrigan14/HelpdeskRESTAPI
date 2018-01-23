<?php

namespace API\CoreBundle\Repository;

use API\CoreBundle\Entity\Company;
use API\TaskBundle\Entity\CompanyData;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Traits\CompanyRepositoryTrait;

/**
 * CompanyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CompanyRepository extends EntityRepository
{
    const LIMIT = 10;

    use CompanyRepositoryTrait;

    /**
     * Return all entities with specific conditions based on actual Entity
     *
     * @param int $page
     *
     * @param array $options
     *
     * @return mixed
     */
    public function getAllEntities(int $page, array $options = [])
    {
        $isActive = $options['isActive'];
        $order = $options['order'];
        $limit = $options['limit'];

        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('c')
                ->select('c, companyData, companyAttribute')
                ->leftJoin('c.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->where('c.is_active = :isActiveParam')
                ->orderBy('c.title', $order)
                ->distinct()
                ->setParameter('isActiveParam', $isActiveParam)
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('c')
                ->select('c,companyData, companyAttribute')
                ->leftJoin('c.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->orderBy('c.title', $order)
                ->distinct()
                ->getQuery();
        }

        if (999 !== $limit) {
            // Pagination
            if (1 < $page) {
                $query->setFirstResult($limit * $page - $limit);
            } else {
                $query->setFirstResult(0);
            }

            $query->setMaxResults($limit);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = $paginator->count();

            return [
                'count' => $count,
                'array' => $this->formatData($paginator)
            ];
        } else {
            // Return all entities
            return [
                'array' => $this->formatData($query->getArrayResult(), true)
            ];
        }
    }

    /**
     * Return one full entity
     *
     * @param int $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getEntity(int $id)
    {
        $query = $this->createQueryBuilder('c')
            ->select('c,companyData, companyAttribute')
            ->leftJoin('c.companyData', 'companyData')
            ->leftJoin('companyData.companyAttribute', 'companyAttribute')
            ->where('c.id = :companyId')
            ->setParameter('companyId', $id)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * @param string|bool $term
     * @param int $page
     * @param string|bool $isActive
     * @param string $order
     * @param int $limit
     * @return array
     */
    public function getCompaniesSearch($term, int $page, $isActive, string $order, int $limit): array
    {
        $parameters = [];
        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('c')
                ->select('c,companyData, companyAttribute')
                ->leftJoin('c.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->where('c.is_active = :isActiveParam')
                ->orderBy('c.title', $order)
                ->distinct();
            $parameters['isActiveParam'] = $isActiveParam;
        } else {
            $query = $this->createQueryBuilder('c')
                ->select('c,companyData, companyAttribute')
                ->leftJoin('c.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->orderBy('c.title', $order)
                ->distinct();
        }

        if ($term) {
            $query->andWhere('c.title LIKE :term');
            $parameters['term'] = '%' . $term . '%';
        }

        $query->setParameters($parameters);
        $query->getQuery();

        if (999 !== $limit) {
            // Pagination
            if (1 < $page) {
                $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
            } else {
                $query->setFirstResult(0);
            }

            $query->setMaxResults(self::LIMIT);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = $paginator->count();

            return [
                'count' => $count,
                'array' => $this->formatData($paginator)
            ];
        } else {
            // Return all entities
            return [
                'array' => $this->formatData($query->getQuery()->getArrayResult(), true)
            ];
        }
    }

    /**
     * @return array
     */
    public function getAllCompanyEntitiesWithIdAndTitle()
    {
        $query = $this->createQueryBuilder('company')
            ->select('company.id,  company.title')
            ->where('company.is_active = :isActive')
            ->setParameter('isActive', true);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $paginatorData
     * @param bool $array
     * @return array
     */
    private function formatData($paginatorData, $array = false): array
    {
        $response = [];
        foreach ($paginatorData as $data) {
            if ($array) {
                $response[] = $this->processArrayData($data);
            } else {
                $response[] = $this->processData($data);
            }
        }

        return $response;
    }

    /**
     * @param Company $data
     * @return array
     */
    private function processData(Company $data): array
    {
        $companyData = $data->getCompanyData();
        $companyDataArray = [];
        if ($companyData) {
            /** @var CompanyData $item */
            foreach ($companyData as $item) {
                $companyDataArray[] = [
                    'id' => $item->getId(),
                    'value' => $item->getValue(),
                    'companyAttribute' => [
                        'id' => $item->getCompanyAttribute()->getId(),
                        'title' => $item->getCompanyAttribute()->getTitle(),
                    ]
                ];
            }
        }

        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'ico' => $data->getIco(),
            'dic' => $data->getDic(),
            'ic_dph' => $data->getIcDph(),
            'street' => $data->getStreet(),
            'city' => $data->getCity(),
            'zip' => $data->getZip(),
            'country' => $data->getCountry(),
            'is_active' => $data->getIsActive(),
            'companyData' => $companyDataArray
        ];

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $companyData = $data['companyData'];
        $companyDataArray = [];
        if ($companyData) {
            foreach ($companyData as $item) {
                $companyDataArray[] = [
                    'id' => $item['id'],
                    'value' =>  $item['value'],
                    'companyAttribute' => [
                        'id' => $item['companyAttribute']['id'],
                        'title' =>  $item['companyAttribute']['title'],
                    ]
                ];
            }
        }

        $response = [
            'id' => $data['id'],
            'title' =>  $data['title'],
            'ico' =>  $data['ico'],
            'dic' =>  $data['dic'],
            'ic_dph' =>  $data['ic_dph'],
            'street' =>  $data['street'],
            'city' =>  $data['city'],
            'zip' =>  $data['zip'],
            'country' =>  $data['country'],
            'is_active' =>  $data['is_active'],
            'companyData' => $companyDataArray
        ];

        return $response;
    }
}
