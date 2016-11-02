<?php

namespace API\CoreBundle\Model;
use API\CoreBundle\Entity\Tag;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class TagModel
 * @package API\CoreBundle\Model
 */
class TagModel extends BaseModel implements ModelInterface
{
    /** @var Router */
    private $router;

    /**
     * UserModel constructor.
     *
     * @param Connection $dbConnection
     */
    public function __construct(Connection $dbConnection, Router $router)
    {
        parent::__construct($dbConnection);

        $this->router = $router;
    }

    /**
     * @param Tag $tag
     * @return array
     */
    public function getTagDataWithLinks(Tag $tag)
    {
        return [
            'data'   => $tag,
            '_links' => $this->getTagLinks($tag->getId())
        ];
    }

    /**
     * ***********************
     * DB Methods
     * ***********************
     */

    /**
     * Return all User's Tags
     *
     * @param int $user_id
     * @return array
     */
    public function getTags(int $user_id)
    {
        $query = $this->queryBuilder
            ->select('*')
            ->from($this->getTableName(),'t')
            ->where('t.user_id = :user_id');

        return [
            'data'   => $this->dbConnection->executeQuery($query->getSQL() , ['user_id' => $user_id])->fetchAll()
        ];
    }

    /**
     * Return all info about User's Tag
     *
     * @param int $tag_id
     * @param int $user_id
     * @return array
     */
    public function getTagById(int $tag_id, int $user_id)
    {
        $query = $this->queryBuilder
            ->select('*')
            ->from($this->getTableName(),'t')
            ->where('t.user_id = :user_id')
            ->andWhere('t.id = :tag_id');
        $query->setMaxResults(1);

        return [
            'data'   => $this->dbConnection->executeQuery($query->getSQL() , ['user_id' => $user_id, 'tag_id' => $tag_id])->fetch(),
            '_links' => $this->getTagLinks($tag_id)
        ];
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tag';
    }

    /**
     * @return array
     */
    public function getRelatedTableNames()
    {
        return [
            'u' => 'user',
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getTagLinks(int $id)
    {
        return [
            'put'    => $this->router->generate('tag_update' , ['id' => $id]) ,
            'patch'  => $this->router->generate('tag_partial_update' , ['id' => $id]) ,
            'delete' => $this->router->generate('tag_delete' , ['id' => $id]) ,
        ];
    }
}