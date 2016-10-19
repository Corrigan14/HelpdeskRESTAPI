<?php

namespace API\CoreBundle\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;

/**
 * Class BaseModel
 * @package API\CoreBundle\Model
 */
class BaseModel
{
    /** @var Connection */
    protected $dbConnection;

    /** @var \Doctrine\DBAL\Query\QueryBuilder  */
    protected $queryBuilder;

    /**
     * BaseModel constructor.
     * @param Connection $dbConnection
     */
    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->queryBuilder = $dbConnection->createQueryBuilder();
    }

    /**
     * @param string $tableName
     * @param array $values
     * @return array
     */
    public function fetchResult(string $tableName, array $values = []): array
    {
        return $this->dbConnection->query($this->queryBuilder->select($values)->from($tableName)->getSQL())->fetch();
    }

    /**
     * @param string $tableName
     * @param array $values
     * @return array
     */
    public function fetchResults(string $tableName, array $values = []): array
    {
        return $this->dbConnection->query($this->queryBuilder->select($values)->from($tableName)->getSQL())->fetchAll();
    }
}