<?php

namespace API\TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * SmtpRepository
 */
class SmtpRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getAllEntities(): array
    {
        $query = $this->createQueryBuilder('smtp')
            ->select()
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countEntities(): int
    {
        $query = $this->createQueryBuilder('smtp')
            ->select('COUNT(smtp.id)')
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntity(int $id): array
    {
        $query = $this->createQueryBuilder('smtp')
            ->select()
            ->where('smtp.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $query->getArrayResult();
    }
}
