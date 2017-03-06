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

        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('unit')
                ->select()
                ->where('unit.is_active = :isActiveParam')
                ->orderBy('unit.title', $order)
                ->setParameter('isActiveParam', $isActiveParam);
        } else {
            $query = $this->createQueryBuilder('unit')
                ->select()
                ->orderBy('unit.title', $order);
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
    public function getEntity(int $id): array
    {
        $query = $this->createQueryBuilder('unit')
            ->select()
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
     * @return array
     */
    private function formatData($paginatorData):array
    {
        $response = [];
        /** @var Unit $data */
        foreach ($paginatorData as $data) {
            $response[] = $this->processData($data);
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

}
