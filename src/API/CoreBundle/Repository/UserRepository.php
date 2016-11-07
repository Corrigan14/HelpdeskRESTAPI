<?php

namespace API\CoreBundle\Repository;
use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * Default User fields in case no custom fields are defined
     */
    const DEFAULT_FIELDS = ['id' , 'email' , 'username', 'roles', 'is_active', 'acl'];
    const LIMIT = 10;

    /**
     * Return all info about user (User, UserData Entity)
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

        return $query->getArrayResult();
    }

    /**
     * Return count of all users
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function countUsers(): int
    {
        $query = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $query;
    }

    /**
     * @param array $fields
     * @return \Doctrine\ORM\Query
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

        return $this->createQueryBuilder('u')
            ->select($values)
            ->leftJoin('u.detailData','d')
            ->getQuery();
    }
}
