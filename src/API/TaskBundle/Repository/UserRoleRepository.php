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
        $limit = $options['limit'];

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

        if (999 !== $limit) {
            // Pagination
            if (1 < $page) {
                $query->setFirstResult($limit * $page - $limit);
            } else {
                $query->setFirstResult(0);
            }

            $query->setMaxResults($limit);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = $paginator->count();

            return [
                'count' => $count,
                'array' => $this->formatListData($paginator)
            ];
        } else {
            // Return all entities
            return [
                'array' => $this->formatListData($query->getQuery()->getArrayResult(), true)
            ];
        }
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
     * @param bool $array
     * @return array
     */
    private function formatListData($paginatorData, $array = false): array
    {
        $response = [];

        foreach ($paginatorData as $data) {
            if ($array) {
                $response[] = $this->processArrayData($data);
            } else {
                $response[] = $this->processData($data);
            }

        }

        return $response;
    }

    /**
     * @param UserRole $data
     * @return array
     */
    private function processData(UserRole $data): array
    {
        return [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'description' => $data->getDescription(),
            'homepage' => $data->getHomepage(),
            'acl' => $data->getAcl(),
            'order' => $data->getOrder(),
            'is_active' => $data->getIsActive(),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $response = [

        ];

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
