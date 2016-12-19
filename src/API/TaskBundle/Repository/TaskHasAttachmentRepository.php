<?php

namespace API\TaskBundle\Repository;

/**
 * TaskHasAttachmentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaskHasAttachmentRepository
{
    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param int $taskId
     * @return mixed
     */
    public function getAllEntities(int $taskId, int $page)
    {

        return [];
    }

    /**
     * Return count of all Entities
     *
     * @param int $taskId
     * @return int
     * @internal param array $options
     *
     */
    public function countEntities(int $taskId)
    {
        return 10;
    }
}
