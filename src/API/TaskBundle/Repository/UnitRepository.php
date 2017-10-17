<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\Unit;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * UnitRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UnitRepository extends EntityRepository
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
        $limit = $options['limit'];

        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('unit')
                ->select('unit')
                ->where('unit.is_active = :isActiveParam')
                ->orderBy('unit.title', $order)
                ->setParameter('isActiveParam', $isActiveParam);
        } else {
            $query = $this->createQueryBuilder('unit')
                ->select()
                ->orderBy('unit.title', $order);
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
        }else {
            // Return all entities
            return [
                'array' => $this->formatData($query->getQuery()->getArrayResult(), true)
            ];
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntity(int $id): array
    {
        $query = $this->createQueryBuilder('unit')
            ->select('unit')
            ->where('unit.id = :unitId')
            ->setParameter('unitId', $id)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * @return array
     */
    public function getListOfUnitsWithShortcutAndId()
    {
        $query = $this->createQueryBuilder('unit')
            ->select('unit.shortcut, unit.id')
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @param $paginatorData
     * @param bool $array
     * @return array
     */
    private function formatData($paginatorData, $array = false):array
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
     * @param Unit $data
     * @return array
     */
    private function processData(Unit $data):array
    {
        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'shortcut' => $data->getShortcut(),
            'is_active' => $data->getIsActive()
        ];

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $response = [
            'id' => $data['id'],
            'title' => $data['title'],
            'shortcut' => $data['shortcut'],
            'is_active' => $data['is_active']
        ];

        return $response;
    }

}
