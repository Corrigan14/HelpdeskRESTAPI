<?php

namespace API\TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ImapRepository
 */
class ImapRepository extends EntityRepository
{
    /**
     * @param array $options
     * @return array
     */
    public function getAllEntities(array $options): array
    {
        $order = $options['order'];
        $isActive = $options['isActive'];

        $isActiveParam = ('true' === $isActive) ? 1 : 0;

        if ('true' === $isActive || 'false' === $isActive) {
            $query = $this->createQueryBuilder('imap')
                ->select('imap, project')
                ->leftJoin('imap.project', 'project')
                ->orderBy('imap.inbox_email', $order)
                ->where('imap.is_active = :isActiveParam')
                ->setParameter('isActiveParam', $isActiveParam)
                ->distinct();
        } else {
            $query = $this->createQueryBuilder('imap')
                ->select('imap, project')
                ->leftJoin('imap.project', 'project')
                ->orderBy('imap.inbox_email', $order)
                ->distinct();
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countEntities(): int
    {
        $query = $this->createQueryBuilder('imap')
            ->select('COUNT(imap.id)')
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntity(int $id): array
    {
        $query = $this->createQueryBuilder('imap')
            ->select('imap, project')
            ->leftJoin('imap.project', 'project')
            ->where('imap.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $query->getArrayResult();
    }
}
