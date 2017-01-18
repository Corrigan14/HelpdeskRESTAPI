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
     * @param int $page
     * @param array $options
     * @return mixed
     */
    public function getAllEntities(int $page, array $options = []);


    /**
     * Return count of all Entities
     *
     * @param array $options
     *
     * @return int
     */
    public function countEntities(array $options = []);
}