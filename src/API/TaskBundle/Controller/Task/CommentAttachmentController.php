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
     *        [
     *           {
     *              "slug": "zsskcd-jpg-2016-12-17-15-36"
     *           }
     *        ],
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
     *  description="Returns a list of slugs of comments attachments.",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Limit for Pagination: 999 - returns all entities, null - returns 10 entities"
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

        $pageNum = $request->get('page');
        $pageNum = (int)$pageNum;
        $page = ($pageNum === 0) ? 1 : $pageNum;

        $limitNum = $request->get('limit');
        $limit = (int)$limitNum ? (int)$limitNum : 10;

        $options = [
            'comment' => $commentId,
            'limit' => $limit
        ];
        $routeOptions = [
            'routeName' => 'tasks_list_of_comments_attachments',
            'routeParams' => ['commentId' => $commentId]
        ];

        $attachmentArray = $this->get('task_additional_service')->getCommentAttachmentsResponse($options, $page, $routeOptions);
        return $this->json($attachmentArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *       "data":
     *       {
     *          "id": 1,
     *          "title": "Koment - public",
     *          "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *          "internal": false,
     *          "email": false,
     *          "email_to": null,
     *          "email_cc": null,
     *          "email_bcc": null,
     *          "createdAt":
     *          {
     *             "date": "2017-02-10 15:47:50.000000",
     *             "timezone_type": 3,
     *             "timezone": "Europe/Berlin"
     *          },
     *          "updatedAt":
     *          {
     *             "date": "2017-02-10 15:47:50.000000",
     *             "timezone_type": 3,
     *             "timezone": "Europe/Berlin"
     *          },
     *          "createdBy":
     *          {
     *             "id": 4031,
     *             "username": "admin",
     *             "email": "admin@admin.sk",
     *             "name": "Admin",
     *             "surname": "Adminovic",
     *             "avatarSlug": "slug-15-15-2014"
     *          },
     *          "commentHasAttachments":
     *          [
     *             {
     *                 "id": 3,
     *                 "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *          ],
     *          "children": false
     *        },
     *        "_links":
     *        {
     *           "delete": "/api/v1/task-bundle/tasks/comments/9"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Add a new attachment to the Comment. Returns a comment.",
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

        $commentArray = $this->get('task_additional_service')->getCommentOfTaskResponse($commentId);
        return $this->json($commentArray, StatusCodesHelper::CREATED_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *       "data":
     *       {
     *          "id": 1,
     *          "title": "Koment - public",
     *          "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *          "internal": false,
     *          "email": false,
     *          "email_to": null,
     *          "email_cc": null,
     *          "email_bcc": null,
     *          "createdAt":
     *          {
     *             "date": "2017-02-10 15:47:50.000000",
     *             "timezone_type": 3,
     *             "timezone": "Europe/Berlin"
     *          },
     *          "updatedAt":
     *          {
     *             "date": "2017-02-10 15:47:50.000000",
     *             "timezone_type": 3,
     *             "timezone": "Europe/Berlin"
     *          },
     *          "createdBy":
     *          {
     *             "id": 4031,
     *             "username": "admin",
     *             "email": "admin@admin.sk",
     *             "name": "Admin",
     *             "surname": "Adminovic",
     *             "avatarSlug": "slug-15-15-2014"
     *          },
     *          "commentHasAttachments":
     *          [
     *             {
     *                 "id": 3,
     *                 "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *          ],
     *          "children": false
     *        },
     *        "_links":
     *        {
     *           "delete": "/api/v1/task-bundle/tasks/comments/9"
     *         }
     *      }
     *
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

        $commentArray = $this->get('task_additional_service')->getCommentOfTaskResponse($commentId);
        return $this->json($commentArray, StatusCodesHelper::DELETED_CODE);
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
}