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

        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('systemSettings')
                ->where('systemSettings.is_active = :isActiveParam')
                ->orderBy('systemSettings.id')
                ->setParameter('isActiveParam', $isActiveParam)
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('systemSettings')
                ->select()
                ->orderBy('systemSettings.id')
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
     * @return array
     */
    private function formatData($paginatorData):array
    {
        $response = [];
        /** @var SystemSettings $data */
        foreach ($paginatorData as $data) {
            $response[] = $this->processData($data);
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
            'title' => $data->getId(),
            'value' => $data->getValue(),
            'is_active' => $data->getIsActive()
        ];

        return $response;
    }
}
