<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\UserRole;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * UserRoleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRoleRepository extends EntityRepository
{
    const LIMIT = 10;

    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param array $options
     * @return mixed
     */
    public function getAllEntities(int $page, array $options = [])
    {
        $isActive = $options['isActive'];
        $order = $options['order'];

        $isActiveParam = ('true' === $isActive) ? 1 : 0;

        if ('true' === $isActive || 'false' === $isActive) {
            $query = $this->createQueryBuilder('userRole')
                ->select('userRole')
                ->orderBy('userRole.order', $order)
                ->where('userRole.is_active = :isActiveParam')
                ->setParameter('isActiveParam', $isActiveParam);
        } else {
            $query = $this->createQueryBuilder('userRole')
                ->select('userRole')
                ->orderBy('userRole.order', $order);
        }

        // Pagination
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        } else {
            $query->setFirstResult(0);
        }

        $query->setMaxResults(self::LIMIT);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = $paginator->count();

        return [
            'count' => $count,
            'array' => $this->formatListData($paginator)
        ];
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntity(int $id): array
    {
        $query = $this->createQueryBuilder('userRole')
            ->select('userRole')
            ->leftJoin('userRole.users', 'users')
            ->where('userRole.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $this->formatSingleData($query->getSingleResult());
    }

    /**
     * @param int $order
     * @return array
     */
    public function getAllowedUserRoles(int $order): array
    {
        $query = $this->createQueryBuilder('userRole')
            ->select('userRole')
            ->where('userRole.order >= :order')
            ->setParameter('order', $order)
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @param $paginatorData
     * @return array
     */
    private function formatListData($paginatorData): array
    {
        $response = [];

        /** @var UserRole $data */
        foreach ($paginatorData as $data) {
            $response[] = [
                'id' => $data->getId(),
                'title' => $data->getTitle(),
                'description' => $data->getDescription(),
                'homepage' => $data->getHomepage(),
                'acl' => $data->getAcl(),
                'order' => $data->getOrder(),
                'is_active' => $data->getIsActive(),
            ];
        }

        return $response;
    }

    /**
     * @param $data
     * @return array
     */
    private function formatSingleData(UserRole $data): array
    {
        $users = $data->getUsers();
        $usersArray = [];
        if (count($users) > 0) {
            /** @var User $item */
            foreach ($users as $item) {
                $usersArray[] = [
                    'id' => $item->getId(),
                    'username' => $item->getUsername(),
                    'email' => $item->getEmail()
                ];
            }
        }

        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'description' => $data->getDescription(),
            'homepage' => $data->getHomepage(),
            'acl' => $data->getAcl(),
            'order' => $data->getOrder(),
            'is_active' => $data->getIsActive(),
            'users' => $usersArray
        ];

        return $response;
    }
}
