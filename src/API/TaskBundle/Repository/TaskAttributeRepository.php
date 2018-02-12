<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\TaskAttribute;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * TaskAttributeRepository
 */
class TaskAttributeRepository extends EntityRepository
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

        if ('true' === $isActive) {
            $isActiveParam = 1;
        } else {
            $isActiveParam = 0;
        }

        if ('true' === $isActive || 'false' === $isActive) {
            $query = $this->createQueryBuilder('ca')
                ->select('ca')
                ->where('ca.is_active = :isActive')
                ->orderBy('ca.title', $order)
                ->setParameter('isActive', $isActiveParam)
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('ca')
                ->select('ca')
                ->orderBy('ca.title', $order)
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
     * @param int $id
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getEntity(int $id): array
    {
        $query = $this->createQueryBuilder('taskAttribute')
            ->select('taskAttribute')
            ->where('taskAttribute.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * @return array
     */
    public function getAllActiveEntitiesWithTypeOptions():array
    {
        $query = $this->createQueryBuilder('ca')
            ->select('ca')
            ->where('ca.is_active = :isActive')
            ->setParameter('isActive', true)
            ->getQuery()
            ->getArrayResult();

        $response = [];

        if (\count($query) > 0) {
            /** @var TaskAttribute $data */
            foreach ($query as $data) {
                $response[] = [
                    'id' => $data['id'],
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'options' => json_decode($data['options'])
                ];
            }
        }


        return $response;
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
     * @param TaskAttribute $data
     * @return array
     */
    private function processData(TaskAttribute $data): array
    {
        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'type' => $data->getType(),
            'description' => $data->getDescription(),
            'options' => $data->getOptions(),
            'is_active' => $data->getIsActive(),
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
            'type' => $data['type'],
            'description' => $data['description'],
            'options' => $data['options'],
            'is_active' => $data['is_active'],
        ];

        return $response;
    }
}
