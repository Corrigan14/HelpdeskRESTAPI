<?php

namespace API\TaskBundle\Controller\Task;

use Igsem\APIBundle\Controller\ApiBaseController;
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
     *          "self": "/api/v1/task-bundle/tasks/7/attachment?page=1",
     *          "first": "/api/v1/task-bundle/tasks/7/attachment?page=1",
     *          "prev": false,
     *          "next": false,
     *          "last": "/api/v1/task-bundle/tasks/7/attachment?page=1"
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
     *  description="Add a new attachment to the Comment. Returns a list of comments attachments",
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

    }

    /**
     * @ApiDoc(
     *  description="Remove the attachment from the Comment",
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
     * @internal param int $userId
     */
    public function removeAttachmentFromCommentAction(int $commentId, string $slug)
    {

    }
}