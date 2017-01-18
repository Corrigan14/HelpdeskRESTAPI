<?php

namespace API\CoreBundle\Repository;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use Doctrine\ORM\EntityRepository;
use Traits\UserRepositoryTrait;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * Default User fields in case no custom fields are defined
     */
    const DEFAULT_FIELDS = ['id', 'email', 'username', 'roles', 'is_active', 'acl'];
    const LIMIT = 10;

    use UserRepositoryTrait;

    /**
     * Return all info about user (User, UserData Entity)
     *
     * @param array $fields
     *
     * @param int $page
     *
     * @param string $isActive
     * @return array
     */
    public function getCustomUsers(array $fields = [], int $page = 1, $isActive)
    {
        $query = $this->getUserQuery($fields, $isActive);

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
     * @param string|bool $isActive
     * @return int
     */
    public function countUsers($isActive = false): int
    {
        if ('true' == $isActive || 'false' == $isActive) {
            if ($isActive == 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.is_active = :isActive')
                ->setParameter('isActive', $isActiveParam)
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $query = $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $query;
    }

    /**
     * @param array $fields
     * @param string $isActive
     * @return \Doctrine\ORM\Query
     */
    private function getUserQuery(array $fields = [], $isActive)
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
            if (property_exists(User::class, $field)) {
                $values[] = 'u.' . $field;
            } elseif (property_exists(UserData::class, $field)) {
                $values[] = 'd.' . $field;
            }
        }
        if (!in_array('u.id', $values, true)) {
            $values[] = 'u.id';
        }

        if ('true' == $isActive || 'false' == $isActive) {
            if ($isActive == 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            return $this->createQueryBuilder('u')
                ->select($values)
                ->where('u.is_active = :isActive')
                ->leftJoin('u.detailData', 'd')
                ->setParameter('isActive', $isActiveParam)
                ->getQuery();
        } else {
            return $this->createQueryBuilder('u')
                ->select($values)
                ->leftJoin('u.detailData', 'd')
                ->getQuery();
        };
    }
}
