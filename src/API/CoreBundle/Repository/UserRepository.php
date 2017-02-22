<?php

namespace API\CoreBundle\Repository;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Traits\UserRepositoryTrait;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * Default User fields in case no custom fields are defined
     */
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
        $query->setFirstResult(self::LIMIT * $page - self::LIMIT);

        return $query->getArrayResult();
    }

    /**
     * Return count of all users
     *
     * @param string|bool $isActive
     * @param bool|string $term
     * @return int
     */
    public function countUsers($isActive = false, $term = false): int
    {
        $parameters = [];
        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.is_active = :isActive');
            $parameters['isActive'] = $isActiveParam;
        } else {
            $query = $this->createQueryBuilder('u')
                ->select('COUNT(u.id)');
        }

        if ($term) {
            $query->andWhere('u.username LIKE :term');
            $parameters['term'] = '%' . $term . '%';
        }

        $query->setParameters($parameters);

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string|bool $term
     * @param int $page
     * @param string|bool $isActive
     * @return array
     */
    public function getUsersSearch($term, int $page, $isActive):array
    {
        $parameters = [];
        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->where('u.is_active = :isActive');
            $parameters['isActive'] = $isActiveParam;
        } else {
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute');
        }

        if ($term) {
            $query->andWhere('u.username LIKE :term')
            ->orWhere('u.email LIKE :term')
            ->orWhere('company.title LIKE :term');
            $parameters['term'] = '%' . $term . '%';
        }

        $query->setParameters($parameters);
        $query->setMaxResults(self::LIMIT);
        $query->setFirstResult(self::LIMIT * $page - self::LIMIT);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param array $fields
     * @param string|bool $isActive
     * @return \Doctrine\ORM\Query
     */
    private function getUserQuery(array $fields = [], $isActive)
    {
        if (0 === count($fields)) {
            if ('true' === $isActive || 'false' === $isActive) {
                if ($isActive === 'true') {
                    $isActiveParam = 1;
                } else {
                    $isActiveParam = 0;
                }
                return $this->createQueryBuilder('u')
                    ->select('u,d,userRole,company,companyData,companyAttribute')
                    ->leftJoin('u.detailData', 'd')
                    ->leftJoin('u.user_role', 'userRole')
                    ->leftJoin('u.company', 'company')
                    ->leftJoin('company.companyData', 'companyData')
                    ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                    ->where('u.is_active = :isActive')
                    ->setParameter('isActive', $isActiveParam)
                    ->getQuery();
            } else {
                return $this->createQueryBuilder('u')
                    ->select('u,d,userRole,company,companyData,companyAttribute')
                    ->leftJoin('u.detailData', 'd')
                    ->leftJoin('u.user_role', 'userRole')
                    ->leftJoin('u.company', 'company')
                    ->leftJoin('company.companyData', 'companyData')
                    ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                    ->getQuery();
            }
        }

        $values = [];
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

        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            return $this->createQueryBuilder('u')
                ->select($values)
                ->where('u.is_active = :isActive')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->setParameter('isActive', $isActiveParam)
                ->getQuery();
        } else {
            return $this->createQueryBuilder('u')
                ->select($values)
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->getQuery();
        }
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getUserResponse(int $userId): array
    {
        $query = $this->createQueryBuilder('u')
            ->select('u,d,userRole,company,companyData,companyAttribute')
            ->leftJoin('u.detailData', 'd')
            ->leftJoin('u.user_role', 'userRole')
            ->leftJoin('u.company', 'company')
            ->leftJoin('company.companyData', 'companyData')
            ->leftJoin('companyData.companyAttribute', 'companyAttribute')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @return array
     */
    public function getAllUserEntitiesWithIdAndTitle():array
    {
        $query = $this->createQueryBuilder('user')
            ->select('user.id, user.username')
            ->where('user.is_active = :isActive')
            ->setParameter('isActive', true);

        return $query->getQuery()->getArrayResult();
    }
}
