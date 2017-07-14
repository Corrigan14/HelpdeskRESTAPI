<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\CompanyAttribute;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * CompanyAttributeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CompanyAttributeRepository extends EntityRepository
{
    const LIMIT = 10;

    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param array $options
     * @return mixed
     */
    public function getAllEntities(int $page, array $options = [])
    {
        $isActive = $options['isActive'];
        $order = $options['order'];

        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('ca')
                ->select('ca')
                ->orderBy('ca.title', $order)
                ->where('ca.is_active = :isActiveParam')
                ->setParameter('isActiveParam', $isActiveParam)
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('ca')
                ->select('ca')
                ->orderBy('ca.title', $order)
                ->getQuery();
        }

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
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntity(int $id)
    {
        $query = $this->createQueryBuilder('ca')
            ->select()
            ->where('ca.id = :caId')
            ->setParameter('caId', $id)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * @param $paginatorData
     * @return array
     */
    private function formatData($paginatorData):array
    {
        $response = [];
        /** @var CompanyAttribute $data */
        foreach ($paginatorData as $data) {
            $response[] = $this->processData($data);
        }

        return $response;
    }

    /**
     * @param CompanyAttribute $data
     * @return array
     */
    private function processData(CompanyAttribute $data):array
    {
        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'type' => $data->getType(),
            'options' => $data->getOptions(),
            'is_active' => $data->getIsActive()
        ];

        return $response;
    }
}
