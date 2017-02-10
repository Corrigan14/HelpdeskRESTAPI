<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Repository\RepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * TaskAttributeRepository
 */
class TaskAttributeRepository extends EntityRepository implements RepositoryInterface
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

        if ('true' === $isActive) {
            $isActiveParam = 1;
        } else {
            $isActiveParam = 0;
        }

        if ('true' === $isActive || 'false' === $isActive) {
            $query = $this->createQueryBuilder('ca')
                ->where('ca.is_active = :isActive')
                ->setParameter('isActive', $isActiveParam)
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('ca')
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
        $isActive = $options['isActive'];

        if ('true' === $isActive) {
            $isActiveParam = 1;
        } else {
            $isActiveParam = 0;
        }

        if ('true' === $isActive || 'false' === $isActive) {
            $query = $this->createQueryBuilder('ca')
                ->select('COUNT(ca.id)')
                ->where('ca.is_active = :isActive')
                ->setParameter('isActive', $isActiveParam)
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('ca')
                ->select('COUNT(ca.id)')
                ->getQuery();
        }

        return $query->getSingleScalarResult();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntity(int $id):array
    {
        $query = $this->createQueryBuilder('taskAttribute')
            ->where('taskAttribute.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $query->getArrayResult();
    }
}
