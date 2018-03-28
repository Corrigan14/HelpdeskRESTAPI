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
        $order = $options['order'];

        if ('false' === $internal || false === $internal) {
            $query = $this->createQueryBuilder('c')
                ->select('c,createdBy,detailData,commentHasAttachments,parentComment')
                ->leftJoin('c.createdBy', 'createdBy')
                ->leftJoin('createdBy.detailData', 'detailData')
                ->leftJoin('c.commentHasAttachments', 'commentHasAttachments')
                ->leftJoin('c.comment', 'parentComment')
                ->orderBy('c.createdAt', $order)
                ->distinct()
                ->where('c.task = :taskId')
                ->andWhere('c.internal = :internal')
                ->setParameters(['taskId' => $task, 'internal' => 0])
                ->getQuery();
        } else {
            $query = $this->createQueryBuilder('c')
                ->select('c,createdBy,detailData,commentHasAttachments,parentComment')
                ->leftJoin('c.createdBy', 'createdBy')
                ->leftJoin('createdBy.detailData', 'detailData')
                ->leftJoin('c.commentHasAttachments', 'commentHasAttachments')
                ->leftJoin('c.comment', 'parentComment')
                ->orderBy('c.createdAt', $order)
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

        return $this->processData($query->getQuery()->getSingleResult());
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
     * @param Comment $comment
     * @return array
     */
    private function processData(Comment $comment): array
    {
        $attachments = $comment->getCommentHasAttachments();
        $attachmentArray = [];

        if (count($attachments) > 0) {
            /** @var CommentHasAttachment $attachment */
            foreach ($attachments as $attachment) {
                $attachmentArray[] = $attachment->getSlug();
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

        $parrentComment = $comment->getComment();
        if ($parrentComment instanceof Comment) {
            $parentId = $parrentComment->getId();
            $hasParent = true;
        } else {
            $parentId = null;
            $hasParent = false;
        }

        $childrenComments = $comment->getInversedComment();
        if (count($childrenComments) > 0) {
            $hasChild = true;
            $childId = [];
            foreach ($childrenComments as $data) {
                $childId[] = $data->getId();
            }
        } else {
            $hasChild = false;
            $childId = null;
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
            'commentHasAttachments' => $attachmentArray,
            'hasParent' => $hasParent,
            'parentId' => $parentId,
            'hasChild' => $hasChild,
            'childId' => $childId
        ];

        return $array;
    }

    /**
     * @param array $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $attachments = $data['commentHasAttachments'];
        $attachmentArray = [];

        if (count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                $attachmentArray[] =  $attachment['slug'];
            }
        }

        $detailData = $data['createdBy']['detailData'];
        if (count($detailData) > 0) {
            $nameOfCreator = $detailData['name'];
            $surnameOfCreator = $detailData['surname'];
        } else {
            $nameOfCreator = null;
            $surnameOfCreator = null;
        }

        $parrentComment = $data['comment'];
        if (count($parrentComment) > 0) {
            $parentId = $parrentComment['id'];
            $hasParent = true;
        } else {
            $parentId = null;
            $hasParent = false;
        }

        $array = [
            'id' => $data['id'],
            'title' => $data['title'],
            'body' => $data['body'],
            'createdAt' => isset($data['createdAt']) ? date_timestamp_get($data['createdAt']) : null,
            'updatedAt' => isset($data['updatedAt']) ? date_timestamp_get($data['updatedAt']) : null,
            'internal' => $data['internal'],
            'email' => $data['email'],
            'email_to' => json_decode($data['email_to']),
            'email_cc' => json_decode($data['email_cc']),
            'email_bcc' => json_decode($data['email_bcc']),
            'createdBy' => [
                'id' => $data['createdBy']['id'],
                'username' => $data['createdBy']['username'],
                'email' => $data['createdBy']['email'],
                'name' => $nameOfCreator,
                'surname' => $surnameOfCreator,
                'avatarSlug' => $data['createdBy']['image']
            ],
            'commentHasAttachments' => $attachmentArray,
            'hasParent' => $hasParent,
            'parentId' => $parentId
        ];

        return $array;
    }
}
