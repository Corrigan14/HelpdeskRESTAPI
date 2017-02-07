<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Repository\RepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * StatusRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StatusRepository extends EntityRepository implements RepositoryInterface
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

        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('s')
                ->where('s.is_active = :isActiveParam')
                ->setParameter('isActiveParam', $isActiveParam)
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('s')
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
        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
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
        $query = $this->createQueryBuilder('s')
            ->select()
            ->where('s.id = :sId')
            ->setParameter('sId', $id)
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @return array
     */
    public function getAllStatusEntitiesWithIdAndTitle()
    {
        $query = $this->createQueryBuilder('s')
            ->select('s.id,s.title')
            ->getQuery();

        return $query->getArrayResult();
    }
}
