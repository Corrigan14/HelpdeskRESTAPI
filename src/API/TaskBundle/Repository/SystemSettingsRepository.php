<?php

namespace API\TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;

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
                ->setParameter('isActiveParam', $isActiveParam)
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('systemSettings')
                ->getQuery();
        }

        $query->setMaxResults(self::LIMIT);

        // Pagination calculating offset
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        }

        return $query->getArrayResult();
    }

    /**
     * Return count of all Entities
     *
     * @param array $options
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countEntities(array $options = [])
    {
        $query = $this->createQueryBuilder('systemSettings')
            ->select('COUNT(systemSettings.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $query;
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

        return $query->getArrayResult();
    }
}
