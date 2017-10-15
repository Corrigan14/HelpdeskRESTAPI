<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\SystemSettings;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * SystemSettingsRepository
 */
class SystemSettingsRepository extends EntityRepository
{
    const LIMIT = 10;

    /**
     * @param int $page
     * @param array $options
     * @return array
     */
    public function getAllEntities(int $page, array $options):array
    {
        $isActive = $options['isActive'];
        $limit = $options['limit'];

        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('systemSettings')
                ->select('systemSettings')
                ->where('systemSettings.is_active = :isActiveParam')
                ->orderBy('systemSettings.id','DESC')
                ->setParameter('isActiveParam', $isActiveParam)
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('systemSettings')
                ->select()
                ->orderBy('systemSettings.id','DESC')
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
        }else {
            // Return all entities
            return [
                'array' => $this->formatData($query->getArrayResult(), true)
            ];
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntity(int $id): array
    {
        $query = $this->createQueryBuilder('systemSettings')
            ->select()
            ->where('systemSettings.id = :systemSettingsId')
            ->setParameter('systemSettingsId', $id)
            ->getQuery();

        return $this->processData($query->getSingleResult());
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
     * @param SystemSettings $data
     * @return array
     */
    private function processData(SystemSettings $data):array
    {
        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'value' => $data->getValue(),
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

        ];

        return $response;
    }
}
