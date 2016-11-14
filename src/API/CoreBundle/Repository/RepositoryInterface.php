<?php

namespace API\CoreBundle\Repository;

/**
 * Interface RepositoryInterface
 *
 * @package API\CoreBundle\Repository
 */
interface RepositoryInterface
{
    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param mixed $options
     * @param int $page
     *
     * @return mixed
     */
    public function getAllEntities($options, $page);


    /**
     * Return count of all Entities
     *
     * @param mixed $options
     *
     * @return int
     */
    public function countEntities($options);
}