<?php

namespace API\CoreBundle\Model;
use Doctrine\DBAL\Connection;

/**
 * Class UserModel
 * @package API\CoreBundle\Model
 */
class UserModel extends BaseModel
{
    /**
     * UserModel constructor.
     * @param Connection $dbConnection
     */
    public function __construct(Connection $dbConnection)
    {
        parent::__construct($dbConnection, 'user');
    }
}