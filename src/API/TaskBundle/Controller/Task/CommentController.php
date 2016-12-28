<?php

namespace API\TaskBundle\Controller\Task;

use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommentController
 *
 * @package API\TaskBundle\Controller\Task
 */
class CommentController extends ApiBaseController
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *           "0":
     *           {
     *             "id": 8,
     *             "title": "Koment - public",
     *             "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *             "internal": false,
     *             "email": false,
     *             "createdAt": "2016-12-27T15:03:10+0100",
     *             "updatedAt": "2016-12-27T15:03:10+0100"
     *          },
     *          "1":
     *          {
     *            "id": 11,
     *            "title": "Email - public",
     *            "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *            "internal": false,
     *            "email": true,
     *            "email_to": "a:1:{i:0;s:15:\"email@email.com\";}",
     *            "email_cc": "a:2:{i:0;s:15:\"email2@email.sk\";i:1;s:16:\"email3@email.com\";}",
     *            "createdAt": "2016-12-27T15:03:10+0100",
     *            "updatedAt": "2016-12-27T15:03:10+0100"
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/tasks/21/comments?page=1",
     *           "first": "/api/v1/task-bundle/tasks/21/comments?page=1",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/tasks/21/comments?page=2",
     *           "last": "/api/v1/task-bundle/tasks/21/comments?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Tasks Comments",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     }
     *  },
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="internal",
     *       "description"="Return NO Internal comments (internal = FALSE) if value is FALSE, else returns ALL comments"
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
     * @param Request $request
     * @param int $taskId
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function tasksCommentsListAction(Request $request, int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        // Check if logged user has access to show tasks comments
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_LIST_OF_TASKS_COMMENTS, $task)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;
        $internal = $request->get('internal') ?: 'all';

        $options = [
            'task' => $task,
            'internal' => $internal
        ];
        $routeOptions = [
            'routeName' => 'tasks_list_of_tasks_comments',
            'routeParams' => ['taskId' => $taskId]
        ];

        $commentsArray = $this->get('task_additional_service')->getCommentsOfTaskResponse($options, $page, $routeOptions);
        return $this->createApiResponse($commentsArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 9,
     *           "title": "Koment - publik, podkomentar komentu",
     *           "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *           "internal": true,
     *           "email": false,
     *           "comment":
     *           {
     *              "id": 8,
     *              "title": "Koment - public",
     *              "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *              "internal": false,
     *              "email": false,
     *              "created_at": "2016-12-27T15:03:10+0100",
     *              "updated_at": "2016-12-27T15:03:10+0100"
     *           },
     *           "created_at": "2016-12-27T15:03:10+0100",
     *           "updated_at": "2016-12-27T15:03:10+0100"
     *        },
     *        "_links":
     *        {
     *           "delete": "/api/v1/task-bundle/tasks/comments/9"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Comment",
     *  requirements={
     *     {
     *       "name"="commentId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output="API\TaskBundle\Entity\Comment",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $commentId
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getTasksCommentAction(int $commentId)
    {
        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            return $this->notFoundResponse();
        }

        $task = $comment->getTask();
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASKS_COMMENT, $task)) {
            return $this->accessDeniedResponse();
        }

        $commentArray = $this->get('task_additional_service')->getCommentOfTaskResponse($comment);
        return $this->createApiResponse($commentArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 9,
     *           "title": "Koment - publik, podkomentar komentu",
     *           "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *           "internal": true,
     *           "email": false,
     *           "comment":
     *           {
     *              "id": 8,
     *              "title": "Koment - public",
     *              "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *              "internal": false,
     *              "email": false,
     *              "created_at": "2016-12-27T15:03:10+0100",
     *              "updated_at": "2016-12-27T15:03:10+0100"
     *           },
     *           "created_at": "2016-12-27T15:03:10+0100",
     *           "updated_at": "2016-12-27T15:03:10+0100"
     *        },
     *        "_links":
     *        {
     *          "delete": "/api/v1/task-bundle/tasks/comments/9"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Comment to Task",
     *  input={"class"="API\TaskBundle\Entity\Comment"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Comment"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @return Response
     * @throws \LogicException
     */
    public function createTasksCommentAction(Request $request, int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if(!$task instanceof Task){
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 9,
     *           "title": "Koment - publik, podkomentar komentu",
     *           "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *           "internal": true,
     *           "email": false,
     *           "comment":
     *           {
     *              "id": 8,
     *              "title": "Koment - public",
     *              "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *              "internal": false,
     *              "email": false,
     *              "created_at": "2016-12-27T15:03:10+0100",
     *              "updated_at": "2016-12-27T15:03:10+0100"
     *           },
     *           "created_at": "2016-12-27T15:03:10+0100",
     *           "updated_at": "2016-12-27T15:03:10+0100"
     *        },
     *        "_links":
     *        {
     *          "delete": "/api/v1/task-bundle/tasks/comments/9"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Comment",
     *  input={"class"="API\TaskBundle\Entity\Comment"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Comment"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $commentId
     * @return Response
     */
    public function createCommentsCommentAction(Request $request, int $commentId)
    {

    }

    /**
     * @ApiDoc(
     *  description="Delete Entity (DELETE)",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
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
     *      204 ="The Entity was successfully deleted",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $commentId
     *
     * @return Response
     */
    public function deleteAction(int $commentId)
    {
    }
}
