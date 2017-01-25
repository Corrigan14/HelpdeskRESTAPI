<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\File;
use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\CommentHasAttachment;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommentAttachmentController
 *
 * @package API\TaskBundle\Controller\Task
 */
class CommentAttachmentController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *          "0":
     *          {
     *            "id": 2,
     *            "name": "lamp",
     *            "slug": "lamp-2016-12-19-02-21",
     *            "temp_name": "phpJXtI4V",
     *            "type": "text/plain",
     *            "size": 35,
     *            "upload_dir": "ee4ee8b963284e98df96aa0c04b4e9a6",
     *            "public": false,
     *            "created_at": "2016-12-19T02:21:50+0100",
     *            "updated_at": "2016-12-19T02:21:50+0100"
     *          }
     *        },
     *        "_links":
     *        {
     *          "self": "/api/v1/task-bundle/tasks/comments/14/attachment?page=1",
     *          "first": "/api/v1/task-bundle/tasks/comments/14/attachment?page=1",
     *          "prev": false,
     *          "next": false,
     *          "last": "/api/v1/task-bundle/tasks/comments/14/attachment?page=1"
     *        },
     *        "total": 1,
     *        "page": 1,
     *        "numberOfPages": 1
     *      }
     *
     * @ApiDoc(
     *  description="Returns a list of comments attachments",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     }
     *  },
     *  requirements={
     *     {
     *       "name"="commentId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of comment"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param Request $request
     * @param int $commentId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \LogicException
     */
    public function listOfCommentsAttachmentsAction(Request $request, int $commentId)
    {
        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            return $this->createApiResponse([
                'message' => 'Comment with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_LIST_OF_COMMENTS_ATTACHMENTS, $comment)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;

        $options['comment'] = $commentId;
        $routeOptions = [
            'routeName' => 'tasks_list_of_comments_attachments',
            'routeParams' => ['commentId' => $commentId]
        ];

        $attachmentArray = $this->get('task_additional_service')->getCommentAttachmentsResponse($options, $page, $routeOptions);
        return $this->createApiResponse($attachmentArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "0":
     *        {
     *          "id": 2,
     *          "name": "lamp",
     *          "slug": "lamp-2016-12-19-02-21",
     *          "temp_name": "phpJXtI4V",
     *          "type": "text/plain",
     *          "size": 35,
     *          "upload_dir": "ee4ee8b963284e98df96aa0c04b4e9a6",
     *          "public": false,
     *          "created_at": "2016-12-19T02:21:50+0100",
     *          "updated_at": "2016-12-19T02:21:50+0100"
     *         }
     *         "1":
     *        {
     *          "id": 3,
     *          "name": "lamp2",
     *          "slug": "lamp2-2016-12-19-02-21",
     *          "temp_name": "phpJXtI4V",
     *          "type": "text/plain",
     *          "size": 38,
     *          "upload_dir": "ee4ee8b963284e98df96aa0c04b4e9a6",
     *          "public": false,
     *          "created_at": "2016-12-19T02:21:50+0100",
     *          "updated_at": "2016-12-19T02:21:50+0100"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Add a new attachment to the Comment. Returns a list of comments attachments.",
     *  requirements={
     *     {
     *       "name"="commentId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="slug",
     *       "dataType"="string",
     *       "description"="The slug of uploaded attachment"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      201 ="The attachment was successfully added to task",
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $commentId
     * @param string $slug
     * @return Response
     * @throws \LogicException
     * @internal param int $userId
     */
    public function addAttachmentToCommentAction(int $commentId, string $slug)
    {
        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            return $this->createApiResponse([
                'message' => 'Comment with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $file = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug
        ]);

        if (!$file instanceof File) {
            return $this->createApiResponse([
                'message' => 'Attachment with requested Slug does not exist! Attachment has to be uploaded before added to comment!',
            ], StatusCodesHelper::BAD_REQUEST_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_ATTACHMENT_TO_COMMENT, $comment)) {
            return $this->accessDeniedResponse();
        }

        if ($this->canAddAttachmentToComment($comment, $slug)) {
            $commentHasAttachment = new CommentHasAttachment();
            $commentHasAttachment->setComment($comment);
            $commentHasAttachment->setSlug($slug);
            $comment->addCommentHasAttachment($commentHasAttachment);
            $this->getDoctrine()->getManager()->persist($commentHasAttachment);
            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();
        }

        $commentAttachmentsArray = $this->getCommentAttachments($comment);
        return $this->createApiResponse($commentAttachmentsArray, StatusCodesHelper::CREATED_CODE);
    }

    /**
     * @ApiDoc(
     *  description="Remove attachment from the Comment",
     *  requirements={
     *     {
     *       "name"="commentId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of comment"
     *     },
     *     {
     *       "name"="slug",
     *       "dataType"="string",
     *       "description"="The slug of uploaded attachment"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      204 ="The attachment was successfully removed",
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $commentId
     * @param string $slug
     * @return Response
     * @throws \LogicException
     */
    public function removeAttachmentFromCommentAction(int $commentId, string $slug)
    {
        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            return $this->createApiResponse([
                'message' => 'Comment with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $file = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug
        ]);

        if (!$file instanceof File) {
            return $this->createApiResponse([
                'message' => 'Attachment with requested Slug does not exist!',
            ], StatusCodesHelper::BAD_REQUEST_CODE);
        }

        $commentHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:CommentHasAttachment')->findOneBy([
            'comment' => $comment,
            'slug' => $slug
        ]);

        if (!$commentHasAttachment instanceof CommentHasAttachment) {
            return $this->createApiResponse([
                'message' => 'The requested attachment is not the attachment of the requested Task!',
            ], StatusCodesHelper::BAD_REQUEST_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_ATTACHMENT_FROM_COMMENT, $comment)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($commentHasAttachment);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param Comment $comment
     * @param string $slug
     * @return bool
     * @throws \LogicException
     */
    private function canAddAttachmentToComment(Comment $comment, string $slug): bool
    {
        $commentHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:CommentHasAttachment')->findOneBy([
            'slug' => $slug,
            'comment' => $comment
        ]);

        return (!$commentHasAttachment instanceof CommentHasAttachment);
    }

    /**
     * @param Comment $comment
     * @return array
     * @throws \LogicException
     */
    private function getCommentAttachments(Comment $comment): array
    {
        $commentHasAttachments = $comment->getCommentHasAttachments();
        $attachmentsOfComments = [];

        if (count($commentHasAttachments) > 0) {
            /** @var CommentHasAttachment $cha */
            foreach ($commentHasAttachments as $cha) {
                $file = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
                    'slug' => $cha->getSlug(),
                ]);
                $attachmentsOfComments[] = $file;
            }
        }

        return $attachmentsOfComments;
    }
}