<?php

namespace API\TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ImapRepository
 */
class ImapRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getAllEntities():array
    {
        $query = $this->createQueryBuilder('imap')
            ->select('imap, project')
            ->leftJoin('imap.project', 'project')
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @return int
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
            ->select()
            ->where('imap.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $query->getArrayResult();
    }
}
