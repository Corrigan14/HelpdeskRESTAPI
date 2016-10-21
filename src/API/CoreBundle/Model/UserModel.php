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
        parent::__construct($dbConnection);
    }

    /**
     * Return all info about user (User, UserAddress, UserData Entity)
     *
     * @param array $values
     * @return array
     */
    public function getCustomUsers(array $values)
    {
        $query = $this->queryBuilder
            ->select($values)
            ->from('user','u')
            ->leftJoin('u','user_address','a','u.id = a.user_id')
            ->leftJoin('u','user_data','d','u.id = d.user_id');

        return $this->dbConnection->query($query->getSQL())->fetchAll();
    }
}