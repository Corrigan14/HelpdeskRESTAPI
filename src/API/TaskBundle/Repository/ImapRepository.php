<?php

namespace API\TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ImapRepository
 */
class ImapRepository extends EntityRepository
{
    /**
     * @param string $order
     * @return array
     */
    public function getAllEntities(string $order):array
    {
        $query = $this->createQueryBuilder('imap')
            ->select('imap, project')
            ->leftJoin('imap.project', 'project')
            ->orderBy('imap.inbox_email', $order)
            ->distinct()
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countEntities():int
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
    public function getEntity(int $id):array
    {
        $query = $this->createQueryBuilder('imap')
            ->select('imap, project')
            ->leftJoin('imap.project','project')
            ->where('imap.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $query->getArrayResult();
    }
}
