<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Repository\RepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * FilterRepository
 */
class FilterRepository extends EntityRepository implements RepositoryInterface
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
        $query = $this->createQueryBuilder('f');
        return [];
    }

    /**
     * Return count of all Entities
     *
     * @param array $options
     *
     * @return int
     */
    public function countEntities(array $options = [])
    {
        return 10;
    }
}
