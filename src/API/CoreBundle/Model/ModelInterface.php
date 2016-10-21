<?php
namespace API\CoreBundle\Model;

/**
 * Interface ModelInterface enforcing that a table name is present in the Model
 *
 * @package API\CoreBundle\Model
 */
interface ModelInterface
{
    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return array
     */
    public function getRelatedTableNames();
}