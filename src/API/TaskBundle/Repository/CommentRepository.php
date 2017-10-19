<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Entity\UserData;
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
     * @param int $limit
     * @return array
     */
    public function getTaskComments(array $options, int $page, int $limit): array
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
                'array' => $this->formatData($query->getArrayResult(), true)
            ];
        }
    }

    /**
     * @param $id
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getCommentEntity($id): array
    {
        $query = $this->createQueryBuilder('comment')
            ->select('comment')
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
     * @param bool $array
     * @return array
     */
    private function formatData($paginatorData, $array = false): array
    {
        $response = [];
        $processedCommentIds = [];

        /** @var Comment $comment */
        foreach ($paginatorData as $comment) {
            if (in_array($comment->getId(), $processedCommentIds, true)) {
                continue;
            }
            if (!$array) {
                $this->buildCommentTree($response, $processedCommentIds, $comment);
            }else{

            }
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

        if (!in_array($commentId, $processedCommentIds, true)) {
            $processedCommentIds[] = $commentId;
            $children = $comment->getInversedComment();
            if (count($children) > 0) {
                $response[$commentId] = $this->fillArray($comment);
                foreach ($children as $child) {
                    $childId = $child->getId();
                    $response[$commentId]['children'][$childId] = $childId;
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

        $detailData = $comment->getCreatedBy()->getDetailData();
        if ($detailData instanceof UserData) {
            $nameOfCreator = $detailData->getName();
            $surnameOfCreator = $detailData->getSurname();
        } else {
            $nameOfCreator = null;
            $surnameOfCreator = null;
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
                'email' => $comment->getCreatedBy()->getEmail(),
                'name' => $nameOfCreator,
                'surname' => $surnameOfCreator,
                'avatarSlug' => $comment->getCreatedBy()->getImage()
            ],
            'commentHasAttachments' => $attachmentArray
        ];

        if ($single) {
            $childrenComments = $comment->getInversedComment();
            $childrenCommentsArray = false;
            if (count($childrenComments) > 0) {
                /** @var Comment $comment */
                foreach ($childrenComments as $commentN) {
                    $childrenCommentsArray[] = [
                        $commentN->getId() => $commentN->getId()
                    ];
                }
            }
            $array['children'] = $childrenCommentsArray;
        }

        return $array;
    }
}
