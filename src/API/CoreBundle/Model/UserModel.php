<?php

namespace API\CoreBundle\Model;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\CoreBundle\Services\HateoasHelper;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class UserModel
 *
 * @package API\CoreBundle\Model
 */
class UserModel extends BaseModel implements ModelInterface
{
    /**
     * Default User fields in case no custom fields are defined
     */
    const DEFAULT_FIELDS = ['id' , 'email' , 'username'];
    const LIMIT = 10;

    private $router;

    /**
     * UserModel constructor.
     *
     * @param Connection $dbConnection
     * @param Router     $router
     */
    public function __construct(Connection $dbConnection , Router $router)
    {
        parent::__construct($dbConnection);

        $this->router = $router;
    }


    public function getUsersResponse(array $fields , int $page)
    {
        if (0 === count($fields)) {
            $fields = self::DEFAULT_FIELDS;
        }

        $users = $this->getCustomUsers($fields , $page);
        foreach ($users as $key => $value) {
            $users[$key]['_links'] = [
                'self' => $this->router->generate('user' , ['id' => $value['id']]) ,
            ];
        }
        $response = [
            'data' => $users ,
        ];
        $pagination = HateoasHelper::getPagination(
            $this->router->generate('users_list') ,
            $page ,
            $this->countUsers() ,
            self::LIMIT ,
            $fields
        );

        return array_merge($response , $pagination);
    }


    /**
     * ***********************
     * DB Methods
     * ***********************
     */


    /**
     * Return all info about user (User, UserAddress, UserData Entity)
     *
     * @param array $fields
     *
     * @param int   $page
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCustomUsers(array $fields = [] , int $page = 1)
    {
        $query = $this->getUserQuery($fields);

        $query->setMaxResults(self::LIMIT);
        /**
         * Pagination calculating offset
         */
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT + 1);
        }


        return $this->dbConnection->query($query->getSQL())->fetchAll();
    }

    /**
     * Return all info about user (User, UserAddress, UserData Entity)
     *
     * @param int   $id
     * @param array $fields
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCustomUser(int $id , array $fields = [])
    {
        $query = $this->getUserQuery($fields)->where('u.id = :id')->setParameter('id' , $id);
        $query->setMaxResults(1);

        return $this->dbConnection->query($query->getSQL())->fetch();
    }

    /**
     * Return count of all users
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function countUsers(): int
    {
        $query = 'SELECT count(*) AS count FROM ' . $this->getTableName();

        return $this->dbConnection->query($query)->fetchColumn(0);
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'user';
    }

    /**
     * @return array
     */
    public function getRelatedTableNames()
    {
        return [
            'ud' => 'user_data' ,
        ];
    }

    /**
     * @param array $fields
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getUserQuery(array $fields = [])
    {
        $values = [];

        /**
         * We are checking if fields exists in related entities, this way we avoid attacks and typing errors
         */
        foreach ($fields as $field) {
            if (property_exists(User::class , $field)) {
                $values[] = 'u.' . $field;
            } elseif (property_exists(UserData::class , $field)) {
                $values[] = 'd.' . $field;
            }
        }
        if (!in_array('u.id' , $values , true)) {
            $values[] = 'u.id';
        }

        return $this->queryBuilder
            ->select($values)
            ->from($this->getTableName() , 'u')
            ->leftJoin('u' , $this->getRelatedTableNames()['ud'] , 'd' , 'u.id = d.user_id');
    }
}