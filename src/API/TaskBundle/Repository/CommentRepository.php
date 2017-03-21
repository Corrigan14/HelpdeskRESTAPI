<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\CommentHasAttachment;
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

        return $this->fillArray($query->getQuery()->getSingleResult(), true);
    }

    /**
     * @param $paginatorData
     * @return array
     */
    private function formatData($paginatorData):array
    {
        $response = [];
        $processedCommentIds = [];

        /** @var Comment $comment */
        foreach ($paginatorData as $comment) {
            if (in_array($comment->getId(), $processedCommentIds)) {
                continue;
            }
            $this->buildCommentTree($response, $processedCommentIds, $comment);
        }
        return $response;
    }

    /**
     * @param $response
     * @param $processedCommentIds
     * @param Comment $comment
     */
    private function buildCommentTree(&$response, &$processedCommentIds, Comment $comment)
    {
        $commentId = $comment->getId();

        if (!in_array($commentId, $processedCommentIds)) {
            $processedCommentIds[] = $commentId;
            $children = $comment->getInversedComment();
            if (count($children) > 0) {
                $response[$commentId] = $this->fillArray($comment);
                foreach ($children as $child) {
                    $response[$commentId]['children'][$child->getId()] = $child->getId();
                }
            } else {
                $response[$commentId] = $this->fillArray($comment);
                $response[$commentId]['children'] = false;
            }
        }
    }

    /**
     * @param Comment $comment
     * @param bool $single
     * @return array
     */
    private function fillArray(Comment $comment, $single = false)
    {
        $attachments = $comment->getCommentHasAttachments();
        $attachmentArray = [];
        if (count($attachments) > 0) {
            /** @var CommentHasAttachment $attachment */
            foreach ($attachments as $attachment) {
                $attachmentArray[] = [
                    'id' => $attachment->getId(),
                    'slug' => $attachment->getSlug()
                ];
            }
        }
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
            ],
            'commentHasAttachments' => $attachmentArray
        ];

        if ($single) {
            $childrenComments = $comment->getInversedComment();
            $childrenCommentsArray = [];
            if (count($childrenComments) > 0) {
                /** @var Comment $comment */
                foreach ($childrenComments as $comment) {
                    $childrenCommentsArray[] = [
                        $comment->getId() => $comment->getId()
                    ];
                }
            }
            $array['children'] = $childrenCommentsArray;
        }

        return $array;
    }
}
