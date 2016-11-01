<?php

namespace API\CoreBundle\Model;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\CoreBundle\Services\HateoasHelper;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use JMS\Serializer\Serializer;
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

    /** @var Router */
    private $router;


    /**
     * UserModel constructor.
     *
     * @param Connection $dbConnection
     * @param Router     $router
     */
    public function __construct(Connection $dbConnection , Router $router )
    {
        parent::__construct($dbConnection);

        $this->router = $router;
    }

    /**
     * Return Users Response  which includes Data and Links and Pagination
     *
     * @param array $fields
     * @param int   $page
     *
     * @return array
     */
    public function getUsersResponse(array $fields , int $page)
    {
        if (0 === count($fields)) {
            $fields = self::DEFAULT_FIELDS;
        }

        $users = $this->getCustomUsers($fields , $page);
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
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        }


        return $this->dbConnection->query($query->getSQL())->fetchAll();
    }

    /**
     * Return all info from field list about user
     *
     * @param int   $id
     * @param array $fields
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCustomUserById(int $id , array $fields = [])
    {
        $query = $this->getUserQuery($fields)->where('u.id = :user');
        $query->setMaxResults(1);

        return [
            'data'   => $this->dbConnection->executeQuery($query->getSQL() , ['user' => $id])->fetch() ,
            '_links' => $this->getUserLinks($id) ,
        ];
    }

    /**
     * Return all info about user
     *
     * @param User $user
     *
     * @return array
     */
    public function getCustomUserData(User $user)
    {
        return [
            'data'   => $user ,
            '_links' => $this->getUserLinks($user->getId()) ,
        ];
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
     * @param int $id
     *
     * @return array
     */
    private function getUserLinks(int $id)
    {
        return [
            'put'    => $this->router->generate('user_update' , ['id' => $id]) ,
            'patch'  => $this->router->generate('user_partial_update' , ['id' => $id]) ,
            'delete' => $this->router->generate('user_delete' , ['id' => $id]) ,
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
        if (0 === count($fields)) {
            $fields = self::DEFAULT_FIELDS;
        }
        /**
         * We are checking if fields exists in related entities, this way we avoid attacks and typing errors
         */
        foreach ($fields as $field) {
            if ('password' === $field) {
                continue;
            }
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
            ->leftJoin('u' , $this->getRelatedTableNames()['ud'] , 'd' , 'u.detail_data_id = d.id');
    }
}