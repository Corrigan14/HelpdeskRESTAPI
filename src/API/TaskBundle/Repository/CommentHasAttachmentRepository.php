<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\CommentHasAttachment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * CommentHasAttachmentRepository
 */
class CommentHasAttachmentRepository extends EntityRepository
{
    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $commentId
     * @param int $page
     * @param int $limit
     * @return array|null
     */
    public function getAllAttachmentSlugs(int $commentId, int $page, int $limit)
    {
        $query = $this->createQueryBuilder('cha')
            ->select('cha')
            ->leftJoin('cha.comment', 'comment')
            ->orderBy('cha.id')
            ->distinct()
            ->where('comment.id = :commentId')
            ->setParameter('commentId', $commentId);

        $query->setMaxResults(TaskRepository::LIMIT);

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
        } else {
            // Return all entities
            return [
                'array' => $this->formatData($query->getQuery()->getArrayResult(), true)
            ];
        }
    }

    /**
     * @param $paginatorData
     * @param bool $array
     * @return array
     */
    private function formatData($paginatorData, $array = false): array
    {
        $response = [];
        /** @var CommentHasAttachment $data */
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
     * @param CommentHasAttachment $data
     * @return array
     */
    private function processData(CommentHasAttachment $data): array
    {
        return [
            'slug' => $data->getSlug()
        ];
    }


    /**
     * @param  array $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $response = [

        ];

        return $response;
    }

}
