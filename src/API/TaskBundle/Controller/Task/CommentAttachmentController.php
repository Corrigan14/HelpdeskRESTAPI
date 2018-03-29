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
     *          "data":
     *          {
     *              "id": 5,
     *              "slug": "zsskcd-jpg-2016-12-17-15-36",
     *              "fileDir": "Upload dir",
     *              "fileName": "Temp name"
     *          },
     *          "_links":
     *          {
     *              "add attachment to the Comment": "/api/v1/task-bundle/tasks/comments/13/add-attachment/api-documentation-xls-2018-03-28-18-48",
     *              "remove attachment from the Comment": "/api/v1/task-bundle/tasks/comments/13/remove-attachment/api-documentation-xls-2018-03-28-18-48"
     *          }
     *      }
     *
     * @ApiDoc(
     *  description="Add a new attachment to the Comment.",
     *  requirements={
     *     {
     *       "name"="commentId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="Comment Id"
     *     },
     *     {
     *       "name"="slug",
     *       "dataType"="string",
     *       "description"="Uploaded attachment slug"
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
     *      200 ="The attachment was successfully added to the comment",
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
    public function addAttachmentToCommentAction(int $commentId, string $slug): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_add_attachment_to_comment', ['commentId' => $commentId, 'slug' => $slug]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Comment with requested Id does not exist!']));
            return $response;
        }

        $fileEntity = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug
        ]);

        if (!$fileEntity instanceof File) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'File with requested Slug does not exist in as DB!']));
            return $response;
        }

        // Check if File exists in a web-page file system
        $uploadDir = $this->getParameter('upload_dir');
        $file = $uploadDir . DIRECTORY_SEPARATOR . $fileEntity->getUploadDir() . DIRECTORY_SEPARATOR . $fileEntity->getTempName();

        if (!file_exists($file)) {
            $response = $response->setStatusCode(StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'File with requested Slug does not exist in a web-page File System!']));
            return $response;
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_ATTACHMENT_TO_COMMENT, $comment)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
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

        $options['comment'] = $commentId;
        $options['file'] = $fileEntity;
        $attachmentEntity = $this->get('task_additional_service')->getCommentOneAttachmentResponse($options);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($attachmentEntity));
        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *          {
     *              "id": 5,
     *              "slug": "zsskcd-jpg-2016-12-17-15-36",
     *              "fileDir": "Upload dir",
     *              "fileName": "Temp name"
     *          },
     *        "_links":
     *          {
     *              "add attachment to the Comment": "/api/v1/task-bundle/tasks/comments/13/add-attachment/api-documentation-xls-2018-03-28-18-48",
     *              "remove attachment from the Comment": "/api/v1/task-bundle/tasks/comments/13/remove-attachment/api-documentation-xls-2018-03-28-18-48"
     *          }
     *      }
     *
     * @ApiDoc(
     *  description="Remove attachment from the Comment",
     *  requirements={
     *     {
     *       "name"="commentId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="Comment Id"
     *     },
     *     {
     *       "name"="slug",
     *       "dataType"="string",
     *       "description"="Uploaded attachment slug"
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
     *      200 ="The attachment was successfully removed",
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
    public function removeAttachmentFromCommentAction(int $commentId, string $slug): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_remove_attachment_from_comment', ['commentId' => $commentId, 'slug' => $slug]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Comment with requested Id does not exist!']));
            return $response;
        }

        $fileEntity = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug
        ]);

        if (!$fileEntity instanceof File) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'File with requested Slug does not exist in a DB!']));
            return $response;
        }

        $commentHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:CommentHasAttachment')->findOneBy([
            'comment' => $comment,
            'slug' => $slug
        ]);

        if (!$commentHasAttachment instanceof CommentHasAttachment) {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Requested attachment is not an attachment of the requested Comment!']));
            return $response;
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_ATTACHMENT_FROM_COMMENT, $comment)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $this->getDoctrine()->getManager()->remove($commentHasAttachment);
        $this->getDoctrine()->getManager()->flush();

        $options['comment'] = $commentId;
        $options['file'] = $fileEntity;
        $attachmentEntity = $this->get('task_additional_service')->getCommentOneAttachmentResponse($options);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($attachmentEntity));
        return $response;
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