<?php

namespace API\TaskBundle\Controller\Task;

use Igsem\APIBundle\Controller\ApiBaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class SubtaskController
 * @package API\TaskBundle\Controller\Task
 */
class SubtaskController extends ApiBaseController
{

    /**
     * ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 37,
     *             "title": "The first Subtask",
     *             "done": true,
     *             "created_at": 123456778,
     *             "updated_at": 123456778,
     *             "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             },
     *             "task":
     *             {
     *                "id": 2575,
     *                "title": "The main task"
     *             }
     *          },
     *          {
     *             "id": 37,
     *             "title": "The second Subtask",
     *             "done": false,
     *             "created_at": 123456778,
     *             "updated_at": 123456778,
     *             "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             },
     *             "task":
     *             {
     *                "id": 2575,
     *                "title": "The main task"
     *             }
     *          },
     *        ],
     *       "_links":
     *       {
     *           "create subtask": "/api/v1/task-bundle/tags?page=1",
     *           "update subtask": "/api/v1/task-bundle/tags?page=1",
     *           "delete subtask": ,
     *       }
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of task's Subtasks",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a task"
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
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $taskId
     * @return Response
     */
    public function listOfTasksSubtasksAction(int $taskId): Response
    {

    }

    public function createSubtaskAction(int $taskId, Request $request):Response
    {

    }

    public function updateSubtaskAction(int $taskId, int $subtaskId, Request $request):Response
    {

    }

    public function deleteSubtaskAction(int $taskId, int $subtaskId, Request $request):Response
    {

    }

}
