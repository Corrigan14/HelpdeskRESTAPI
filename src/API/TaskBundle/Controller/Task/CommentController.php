<?php

namespace API\TaskBundle\Controller\Task;

use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use PHPUnit\Framework\Exception;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

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
     *         {
     *            "id": 2,
     *            "title": "Koment - publik, podkomentar komentu",
     *            "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *            "internal": true,
     *            "email": false,
     *            "email_to": null,
     *            "email_cc": null,
     *            "email_bcc": null,
     *            "createdAt":
     *            {
     *               "date": "2017-02-10 15:47:50.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "updatedAt":
     *            {
     *               "date": "2017-02-10 15:47:50.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "createdBy":
     *            {
     *               "id": 1846,
     *               "username": "admin",
     *               "password": "$2y$13$elpnHhCe/zvKZeezL8CkS.5.GgBwXYev/32i1AcTqEH2Vg6WzGHz6",
     *               "email": "admin@admin.sk",
     *               "roles": "[\"ROLE_ADMIN\"]",
     *               "is_active": true,
     *               "language": "AJ",
     *               "image": null
     *             },
     *             "commentHasAttachments":
     *             [
     *                {
     *                   "id": 2,
     *                   "slug": "zsskcd-jpg-2016-12-17-15-36"
     *                }
     *             ],
     *             "comment":
     *             {
     *                "id": 1,
     *                "title": "Subcoment - public",
     *                "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *                "internal": false,
     *                "email": false,
     *                "email_to": null,
     *                "email_cc": null,
     *                "email_bcc": null,
     *                "createdAt":
     *                {
     *                   "date": "2017-02-10 15:47:50.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                },
     *                "updatedAt":
     *                {
     *                   "date": "2017-02-10 15:47:50.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                },
     *                "commentHasAttachments":
     *                [
     *                   {
     *                      "id": 1,
     *                      "slug": "zsskcd-jpg-2016-12-17-15-36"
     *                   }
     *                ]
     *             }
     *          },
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
        return $this->json($commentsArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *             "id": 1846,
     *             "username": "admin",
     *             "password": "$2y$13$elpnHhCe/zvKZeezL8CkS.5.GgBwXYev/32i1AcTqEH2Vg6WzGHz6",
     *             "email": "admin@admin.sk",
     *             "roles": "[\"ROLE_ADMIN\"]",
     *             "is_active": true,
     *             "language": "AJ",
     *             "image": null
     *          },
     *          "comment": null,
     *          "commentHasAttachments":
     *          [
     *             {
     *                 "id": 3,
     *                 "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *          ]
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

        $commentArray = $this->get('task_additional_service')->getCommentOfTaskResponse($commentId);
        return $this->json($commentArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
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
     *             "id": 1846,
     *             "username": "admin",
     *             "password": "$2y$13$elpnHhCe/zvKZeezL8CkS.5.GgBwXYev/32i1AcTqEH2Vg6WzGHz6",
     *             "email": "admin@admin.sk",
     *             "roles": "[\"ROLE_ADMIN\"]",
     *             "is_active": true,
     *             "language": "AJ",
     *             "image": null
     *          },
     *          "comment": null,
     *          "commentHasAttachments":
     *          [
     *             {
     *                 "id": 3,
     *                 "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *          ]
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
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     }
     *  },
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createTasksCommentAction(Request $request, int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_COMMENT_TO_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $data = $request->request->all();
        $comment = new Comment();
        $comment->setCreatedBy($this->getUser());
        $comment->setTask($task);

        return $this->updateCommentEntity($comment, $data);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
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
     *             "id": 1846,
     *             "username": "admin",
     *             "password": "$2y$13$elpnHhCe/zvKZeezL8CkS.5.GgBwXYev/32i1AcTqEH2Vg6WzGHz6",
     *             "email": "admin@admin.sk",
     *             "roles": "[\"ROLE_ADMIN\"]",
     *             "is_active": true,
     *             "language": "AJ",
     *             "image": null
     *          },
     *          "comment": null,
     *          "commentHasAttachments":
     *          [
     *             {
     *                 "id": 3,
     *                 "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *          ]
     *        },
     *        "_links":
     *        {
     *          "delete": "/api/v1/task-bundle/tasks/comments/9"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new child Comment to Comment",
     *  requirements={
     *     {
     *       "name"="commentId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of parent comment"
     *     }
     *  },
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
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createCommentsCommentAction(Request $request, int $commentId)
    {
        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            return $this->createApiResponse([
                'message' => 'Parent Comment with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $task = $comment->getTask();

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_COMMENT_TO_COMMENT, $task)) {
            return $this->accessDeniedResponse();
        }

        $data = $request->request->all();
        $commentNew = new Comment();
        $commentNew->setCreatedBy($this->getUser());
        $commentNew->setTask($task);
        $commentNew->setComment($comment);

        return $this->updateCommentEntity($commentNew, $data);
    }

    /**
     * @ApiDoc(
     *  description="Delete Comment Entity",
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
     * @throws \LogicException
     */
    public function deleteAction(int $commentId)
    {
        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            return $this->createApiResponse([
                'message' => 'Parent Comment with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $task = $comment->getTask();

        if (!$this->get('task_voter')->isGranted(VoteOptions::DELETE_COMMENT, $task)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($comment);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param Comment $comment
     * @param $requestData
     * @param bool $create
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    private function updateCommentEntity(Comment $comment, $requestData, $create = true)
    {
        $allowedUnitEntityParams = [
            'title',
            'body',
            'internal',
            'email',
            'email_to',
            'email_cc',
            'email_bcc'
        ];

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!in_array($key, $allowedUnitEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for Comment Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        // If comment is sending like Email, email_to param has to be set
        $emailAddresses = [];
        if (isset($requestData['email'])) {
            $isEmail = ('true' === strtolower($requestData['email']) || 1 == $requestData['email']) ? true : false;
            if ($isEmail) {
                $this->processEmailAddress($requestData, $comment, $emailAddresses);
            }
        }

        dump($emailAddresses);

        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($comment, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();

            $commentArray = $this->get('task_additional_service')->getCommentOfTaskResponse($comment->getId());

            // Comment is an Email
            if ($isEmail) {
                $params = [
                    'subject' => $requestData['title'],
                    'from' => 'symfony@lanhelpdesk.com',
                    'to' => 'mb@web-solutions.sk',
                    'body' => $this->renderView('@APITask/Emails/comment.html.twig', ['text' => $requestData['body']])
                ];

                $sendingError = $this->get('email_service')->sendEmail($params);

                if (true !== $sendingError) {
                    $data = [
                        'errors' => $sendingError,
                        'message' => 'Error with email sending!'
                    ];
                    return $this->createApiResponse($data, StatusCodesHelper::PROBLEM_WITH_EMAIL_SENDING);
                }
            }

            return $this->json($commentArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param $requestData
     * @param Comment $comment
     * @param array $emailAddress
     * @return Response
     */
    private function processEmailAddress(&$requestData, Comment &$comment, array &$emailAddress):Response
    {
        $validator = $this->get('validator');
        $constraints = [
            new Email(),
            new NotBlank()
        ];
        $emailToArray = [];
        $emailCcArray = [];
        $emailBccArray = [];

        if (isset($requestData['email_to'])) {
            $emailTo = $requestData['email_to'];
            if (!is_array($emailTo)) {
                $emailToArray = explode(';', $emailTo);
            } else {
                $emailToArray = $emailTo;
            }

            // Check the correct email address
            foreach ($emailToArray as $item) {
                $emailError = $validator->validate($item, $constraints);
                if (count($emailError)) {
                    return $this->createApiResponse(
                        ['message' => 'Not valid email address: ' . $item],
                        StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
            $comment->setEmailTo($emailToArray);
            unset($requestData['email_to']);
        } else {
            return $this->createApiResponse(
                ['message' => 'Email address is required to sent comment like Email!'],
                StatusCodesHelper::INVALID_PARAMETERS_CODE);
        }

        $emailAddress = array_merge($emailToArray, $emailCcArray, $emailBccArray);
    }
}


