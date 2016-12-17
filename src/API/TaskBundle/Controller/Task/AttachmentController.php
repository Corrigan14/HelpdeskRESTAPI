<?php

namespace API\TaskBundle\Controller\Task;

use Igsem\APIBundle\Controller\ApiBaseController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AttachmentController
 *
 * @package API\TaskBundle\Controller\Task
 */
class AttachmentController extends ApiBaseController
{

    /**
     * ### Response ###
     *      {
     *         "0":
     *         {
     *            "id": 85,
     *            "slug": "zsskcd-jpg-2016-12-17-15-36",
     *         },
     *         "1":
     *         {
     *            "id": 87,
     *            "slug": "zsskcd-jpg-2016-12-17-15-40",
     *         }
     *         "2":
     *         {
     *            "id": 88,
     *            "slug": "zsskcd-jpg-2016-12-17-15-59",
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a list of tasks attachments",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     }
     *  },
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
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
     * @param int $taskId
     * @return Response
     * @internal param int $userId
     */
    public function listOfTasksAttachmentsAction(Request $request, int $taskId)
    {
        $page = $request->get('page') ?: 1;
    }

    /**
     * ### Response ###
     *      {
     *         "0":
     *         {
     *            "id": 85,
     *            "slug": "zsskcd-jpg-2016-12-17-15-36",
     *         },
     *         "1":
     *         {
     *            "id": 87,
     *            "slug": "zsskcd-jpg-2016-12-17-15-40",
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Add a new attachment to the Task. Returns a list of tasks attachments",
     *  requirements={
     *     {
     *       "name"="taskId",
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
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param string $slug
     * @return Response
     * @internal param int $userId
     */
    public function addAttachmentToTaskAction(int $taskId, string $slug)
    {

    }

    /**
     * @ApiDoc(
     *  description="Remove the attachment from the Task",
     *  requirements={
     *     {
     *       "name"="taskId",
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
     *      204 ="The attachment was successfully removed",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param string $slug
     * @return Response
     * @internal param int $userId
     */
    public function removeAttachmentFromTaskAction(int $taskId, string $slug)
    {

    }
}