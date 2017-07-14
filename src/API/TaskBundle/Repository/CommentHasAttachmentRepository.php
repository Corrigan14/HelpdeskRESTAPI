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
     * @param int $page
     * @param int $commentId
     * @return array|null
     */
    public function getAllAttachmentSlugs(int $commentId, int $page)
    {
        $query = $this->createQueryBuilder('cha')
            ->select('cha')
            ->leftJoin('cha.comment', 'comment')
            ->orderBy('cha.id')
            ->distinct()
            ->where('comment.id = :commentId')
            ->setParameter('commentId', $commentId);

        $query->setMaxResults(TaskRepository::LIMIT);

        // Pagination
        if (1 < $page) {
            $query->setFirstResult(TaskRepository::LIMIT * $page - TaskRepository::LIMIT);
        } else {
            $query->setFirstResult(0);
        }

        $query->setMaxResults(TaskRepository::LIMIT);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = $paginator->count();

        return [
            'count' => $count,
            'array' => $this->formatData($paginator)
        ];
    }

    /**
     * @param $paginatorData
     * @return array
     */
    private function formatData($paginatorData):array
    {
        $response = [];
        /** @var CommentHasAttachment $data */
        foreach ($paginatorData as $data) {
            $response[] = $this->processData($data);
        }

        return $response;
    }

    /**
     * @param CommentHasAttachment $data
     * @return array
     */
    private function processData(CommentHasAttachment $data):array
    {
        return [
            'slug' => $data->getSlug()
        ];
    }

}
