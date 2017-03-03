<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Task;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * CommentRepository
 */
class CommentRepository extends EntityRepository
{
    /**
     * @param array $options
     * @param int $page
     * @return array
     */
    public function getTaskComments(array $options, int $page): array
    {
        /** @var Task $task */
        $task = $options['task'];
        $internal = $options['internal'];

        if ('false' === strtolower($internal)) {
            $query = $this->createQueryBuilder('c')
                ->select('c,createdby,commenthasattachments')
                ->leftJoin('c.createdBy', 'createdby')
                ->leftJoin('c.commentHasAttachments', 'commenthasattachments')
                ->orderBy('c.id')
                ->distinct()
                ->where('c.task = :taskId')
                ->andWhere('c.internal = :internal')
                ->setParameters(['taskId' => $task, 'internal' => 0])
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('c')
                ->select('c,createdby,commenthasattachments')
                ->leftJoin('c.createdBy', 'createdby')
                ->leftJoin('c.commentHasAttachments', 'commenthasattachments')
                ->orderBy('c.id')
                ->distinct()
                ->where('c.task = :taskId')
                ->setParameter('taskId', $task)
                ->getQuery();
        }

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
     * @param $id
     * @return array
     */
    public function getCommentEntity($id):array
    {
        $query = $this->createQueryBuilder('comment')
            ->select('comment, creator, subcomments, commentHasAttachments, subCommenthasattachments')
            ->leftJoin('comment.createdBy', 'creator')
            ->leftJoin('comment.comment', 'subcomments')
            ->leftJoin('comment.commentHasAttachments', 'commentHasAttachments')
            ->leftJoin('subcomments.commentHasAttachments', 'subCommenthasattachments')
            ->where('comment.id = :id')
            ->setParameter('id', $id);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $paginatorData
     * @return array
     */
    private function formatData($paginatorData):array
    {
        $response = [];
        $processedCommentIds = [];
        $childrenArray = [];

        /** @var Comment $comment */
        foreach ($paginatorData as $comment) {
            if (in_array($comment->getId(), $processedCommentIds)) {
                continue;
            }
            $this->buildCommentTree($response, $processedCommentIds, $comment, $childrenArray);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $processedCommentIds
     * @param Comment $comment
     * @param  $childrenArray
     */
    private function buildCommentTree(&$response, &$processedCommentIds, Comment $comment, &$childrenArray)
    {
        $processedCommentIds[] = $comment->getId();
//        dump($comment->getId());
//        dump($processedCommentIds);

        $response[$comment->getId()] = $this->fillArray($comment);
        $children = $comment->getInversedComment();
        if (count($children) > 0) {
//            dump('children');
//            dump(count($children));
            foreach ($children as $child) {
                $childrenArray[] = $this->fillArray($child);
                $processedCommentIds[] = $comment->getId();
                $this->buildCommentTree($response, $processedCommentIds, $child, $childrenArray);
            }
            $response[$comment->getId()]['children'] = $childrenArray;
        } else {
            $childrenArray = [];
        }


    }

    /**
     * @param Comment $comment
     * @return array
     */
    private function fillArray(Comment $comment)
    {
        $array = [
            'id' => $comment->getId(),
            'title' => $comment->getTitle(),
            'body' => $comment->getBody(),
            'createdAt' => $comment->getCreatedAt(),
            'updatedAt' => $comment->getUpdatedAt(),
            'internal' => $comment->getInternal(),
            'email' => $comment->getEmail(),
            'email_to' => $comment->getEmailTo(),
            'email_cc' => $comment->getEmailCc(),
            'email_bcc' => $comment->getEmailBcc(),
            'createdBy' => [
                'id' => $comment->getCreatedBy()->getId(),
                'username' => $comment->getCreatedBy()->getUsername(),
                'email' => $comment->getCreatedBy()->getEmail()
            ]
        ];

        return $array;
    }
}
