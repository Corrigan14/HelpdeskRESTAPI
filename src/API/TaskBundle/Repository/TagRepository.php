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

        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->leftJoin('t.createdBy', 'createdBy')
            ->orderBy('t.id','DESC')
            ->distinct()
            ->where('t.createdBy = :userId')
            ->orWhere('t.public = :public')
            ->setParameters(['userId' => $userId, 'public' => true])
            ->getQuery();

        $query->setMaxResults(self::LIMIT);

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
            'array' => $this->formatData($paginator)
        ];
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntity(int $id):array
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
    public function getAllTagEntitiesWithIdAndTitle(int $userId):array
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
     * @return array
     */
    private function formatData($paginatorData):array
    {
        $response = [];
        /** @var Tag $data */
        foreach ($paginatorData as $data) {
            $response[] = $this->processData($data);
        }

        return $response;
    }

    /**
     * @param Tag $data
     * @return array
     */
    private function processData(Tag $data):array
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
}
