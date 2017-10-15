<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\Tag;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * TagRepository
 */
class TagRepository extends EntityRepository
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
        $userId = $options['loggedUserId'];
        $limit = $options['limit'];

        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->leftJoin('t.createdBy', 'createdBy')
            ->orderBy('t.id', 'DESC')
            ->distinct()
            ->where('t.createdBy = :userId')
            ->orWhere('t.public = :public')
            ->setParameters(['userId' => $userId, 'public' => true])
            ->getQuery();


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
                'array' => $this->formatData($paginator)
            ];
        }else {
            // Return all entities
            return [
                'array' => $this->formatData($query->getArrayResult(), true)
            ];
        }
    }

    /**
     * Return's all entities without pagination
     *
     * @param int $loggedUserId
     * @return array
     */
    public function getAllUsersTagsWithoutPagination(int $loggedUserId):array
    {
        $query = $this->createQueryBuilder('t')
            ->select('t, createdBy')
            ->leftJoin('t.createdBy', 'createdBy')
            ->orderBy('t.id', 'DESC')
            ->distinct()
            ->where('t.createdBy = :userId')
            ->orWhere('t.public = :public')
            ->setParameters(['userId' => $loggedUserId, 'public' => true])
            ->getQuery();

        $query = $query->getArrayResult();

        $arrayProcessed = [];
        foreach ($query as $data) {
            $arrayProcessed [] = [
                'id' => $data['id'],
                'title' => $data['title'],
                'color' => $data['color'],
                'public' => $data['public'],
                'createdBy' => [
                    'id' => $data['createdBy']['id'],
                    'username' =>$data['createdBy']['username'],
                    'email' => $data['createdBy']['email']
                ]
            ];
        }
        return $arrayProcessed;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntity(int $id): array
    {
        $query = $this->createQueryBuilder('tag')
            ->select('tag')
            ->where('tag.id = :tagId')
            ->setParameter('tagId', $id)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * Return all User's Tags
     *
     * @param int $userId
     * @return array
     */
    public function getUsersTags(int $userId)
    {
        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.createdBy = :userId')
            ->setParameter('userId', $userId);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getAllTagEntitiesWithIdAndTitle(int $userId): array
    {
        $query = $this->createQueryBuilder('tag')
            ->select('tag.id, tag.title')
            ->where('tag.public = :public')
            ->orWhere('tag.createdBy = :userId')
            ->setParameters(['public' => true, 'userId' => $userId]);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $paginatorData
     * @param bool $array
     * @return array
     */
    private function formatData($paginatorData, $array = false): array
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
     * @param Tag $data
     * @return array
     */
    private function processData(Tag $data): array
    {
        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'color' => $data->getColor(),
            'public' => $data->getPublic(),
            'createdBy' => [
                'id' => $data->getCreatedBy()->getId(),
                'username' => $data->getCreatedBy()->getUsername(),
                'email' => $data->getCreatedBy()->getEmail()
            ]
        ];

        return $response;
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
}
