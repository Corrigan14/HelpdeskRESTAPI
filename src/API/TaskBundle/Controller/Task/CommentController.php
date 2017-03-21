<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\File;
use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\CommentHasAttachment;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use PHPUnit\Framework\Exception;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;
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
     *       {
     *          "188":
     *          {
     *             "id": 188,
     *             "title": "test",
     *             "body": "gggg 222",
     *             "createdAt":
     *             {
     *                "date": "2017-03-16 11:23:43.000000",
     *                "timezone_type": 3,
     *                "timezone": "Europe/Berlin"
     *             },
     *             "updatedAt":
     *             {
     *                "date": "2017-03-16 11:23:43.000000",
     *                "timezone_type": 3,
     *                "timezone": "Europe/Berlin"
     *             },
     *             "internal": true,
     *             "email": true,
     *             "email_to":
     *             [
     *                "mb@web-solutions.sk"
     *             ],
     *             "email_cc": null,
     *             "email_bcc": null,
     *             "createdBy":
     *             {
     *                "id": 4031,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             },
     *             "children":
     *             {
     *                "189": 189,
     *                "190": 190
     *              }
     *           },
     *           "189":
     *           {
     *              "id": 189,
     *              "title": "test",
     *              "body": "gggg 222 222",
     *              "createdAt":
     *              {
     *                 "date": "2017-03-16 11:23:43.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                 "date": "2017-03-16 11:23:43.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "internal": true,
     *              "email": true,
     *              "email_to":
     *              [
     *                 "mb@web-solutions.sk"
     *              ],
     *              "email_cc": null,
     *              "email_bcc": null,
     *              "createdBy":
     *              {
     *                 "id": 4031,
     *                 "username": "admin",
     *                 "email": "admin@admin.sk"
     *               },
     *               "children": false
     *             },
     *             "190":
     *             {
     *                "id": 190,
     *                "title": "test",
     *                "body": "gggg 222 222 555",
     *                "createdAt":
     *                {
     *                   "date": "2017-03-16 11:23:43.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                },
     *                "updatedAt":
     *                {
     *                   "date": "2017-03-16 11:23:43.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                },
     *                "internal": true,
     *                "email": true,
     *                "email_to":
     *                [
     *                   "mb@web-solutions.sk"
     *                ],
     *                "email_cc": null,
     *                "email_bcc": null,
     *                "createdBy":
     *                {
     *                   "id": 4031,
     *                   "username": "admin",
     *                   "email": "admin@admin.sk"
     *                 },
     *                 "children": false
     *               }
     *            }
     *       }
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

        $pageNum = $request->get('page');
        $pageNum = intval($pageNum);
        $page = ($pageNum === 0) ? 1 : $pageNum;

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
     *  parameters={
     *      {"name"="slug", "dataType"="string", "required"=false, "description"="The slug of attachment"}
     *  },
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
     *  parameters={
     *      {"name"="slug", "dataType"="string", "required"=false, "description"="The slug of attachment"}
     *  },
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
            'email_bcc',
            'slug'
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

        // Add attachment to comment
        $attachment = false;
        if (array_key_exists('slug', $requestData)) {
            $slugArray = $requestData['slug'];
            if (!is_array($slugArray)) {
                $slugArray = explode(',', $slugArray);
            }

            if (count($slugArray) > 0) {
                $attachment = [];
                foreach ($slugArray as $slug) {
                    $file = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
                        'slug' => $slug
                    ]);

                    if (!$file instanceof File) {
                        return $this->createApiResponse([
                            'message' => 'Attachment with requested Slug: ' . $slug . ' does not exist! Attachment has to be uploaded before added to comment!',
                        ], StatusCodesHelper::BAD_REQUEST_CODE);
                    }

                    if ($this->canAddAttachmentToComment($comment, $slug)) {
                        $commentHasAttachment = new CommentHasAttachment();
                        $commentHasAttachment->setComment($comment);
                        $commentHasAttachment->setSlug($slug);
                        $comment->addCommentHasAttachment($commentHasAttachment);
                        $this->getDoctrine()->getManager()->persist($commentHasAttachment);
                    }

                    $attachment[] = [
                        'dir' => $file->getUploadDir(),
                        'name' => $file->getTempName()
                    ];
                }
            }

            unset($requestData['slug']);
        }

        // Comment marked like Email - validation of email addresses
        $emailAddresses = [];
        $notValidEmailAddresses = [];
        $isEmail = false;
        if (isset($requestData['email'])) {
            $isEmail = ('true' === strtolower($requestData['email']) || 1 == $requestData['email']) ? true : false;
            if ($isEmail) {
                if (isset($requestData['email_to'])) {
                    $this->processEmailAddress($requestData, $comment, $emailAddresses, $notValidEmailAddresses);

                    if (count($notValidEmailAddresses) > 0) {
                        return $this->createApiResponse(
                            ['message' => 'Not valid email address: ' . implode(";", $notValidEmailAddresses)],
                            StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    }
                } else {
                    return $this->createApiResponse(
                        ['message' => 'Email address is required to sent comment like Email!'],
                        StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
        }

        unset($requestData['email_to']);
        unset($requestData['email_cc']);
        unset($requestData['email_bcc']);

        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($comment, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();

            $commentArray = $this->get('task_additional_service')->getCommentOfTaskResponse($comment->getId());

            $task = $comment->getTask();
            $loggedUserEmail = $this->getUser()->getEmail();

            // If Comment is an Email - send Email
            if ($isEmail) {
                $templateParams = $this->getTemplateParams($task->getId(), $requestData, $emailAddresses, $attachment);
                $sendingError = $this->get('email_service')->sendEmail($templateParams);
                if (true !== $sendingError) {
                    $data = [
                        'errors' => $sendingError,
                        'message' => 'Error with sending emails!'
                    ];
                    return $this->createApiResponse($data, StatusCodesHelper::PROBLEM_WITH_EMAIL_SENDING);
                }
            }

            // Notification about creation of Comment to task REQUESTER, ASSIGNED USERS, FOLLOWERS
            $notificationEmailAddresses = $this->getEmailForAddCommentNotification($task, $loggedUserEmail, $emailAddresses);
            if (count($notificationEmailAddresses) > 0) {
                $templateParams = $this->getTemplateParams($task->getId(), $requestData, $notificationEmailAddresses, $attachment);
                $sendingError = $this->get('email_service')->sendEmail($templateParams);
                if (true !== $sendingError) {
                    $data = [
                        'errors' => $sendingError,
                        'message' => 'Error with sending notifications!'
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
     * @param array $notValidEmailAddresses
     */
    private function processEmailAddress(&$requestData, Comment &$comment, array &$emailAddress, array &$notValidEmailAddresses)
    {
        $validator = $this->get('validator');
        $constraints = [
            new Email(),
            new NotBlank()
        ];
        $emailBccArray = [];
        $emailCcArray = [];

        // Email TO
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
                $notValidEmailAddresses[] = $item;
            }
        }
        $comment->setEmailTo($emailToArray);


        // Email CC
        if (isset($requestData['email_cc'])) {
            $emailCc = $requestData['email_cc'];
            if (!is_array($emailCc)) {
                $emailCcArray = explode(';', $emailCc);
            } else {
                $emailCcArray = $emailCc;
            }

            // Check the correct email address
            foreach ($emailCcArray as $item) {
                $emailError = $validator->validate($item, $constraints);
                if (count($emailError)) {
                    $notValidEmailAddresses[] = $item;
                }
            }
            $comment->setEmailCc($emailCcArray);
        }

        // Email BCC
        if (isset($requestData['email_bcc'])) {
            $emailBcc = $requestData['email_bcc'];
            if (!is_array($emailBcc)) {
                $emailBccArray = explode(';', $emailBcc);
            } else {
                $emailBccArray = $emailBcc;
            }

            // Check the correct email address
            foreach ($emailBccArray as $item) {
                $emailError = $validator->validate($item, $constraints);
                if (count($emailError)) {
                    $notValidEmailAddresses[] = $item;
                }
            }
            $comment->setEmailBcc($emailBccArray);
        }

        $emailAddress = array_merge($emailToArray, $emailCcArray, $emailBccArray);
    }

    /**
     * @param int $taskId
     * @param array $requestData
     * @param array $emailAddresses
     * @param array|bool $attachmentParams
     * @return array
     */
    private function getTemplateParams(int $taskId, array $requestData, array $emailAddresses, $attachmentParams):array
    {
        /** @var User $user */
        $user = $this->getUser();
        $userDetailData = $user->getDetailData();
        if ($userDetailData instanceof UserData) {
            $usersSignature = $userDetailData->getSignature();
            $username = $userDetailData->getName() . ' ' . $userDetailData->getSurname();
        } else {
            $usersSignature = '';
            $username = '';
        }
        $todayDate = new \DateTime();
        $email = $user->getEmail();
        $baseFrontURL = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->findOneBy([
            'title' => 'Base Front URL'
        ]);
        $templateParams = [
            'date' => $todayDate,
            'username' => $username,
            'email' => $email,
            'taskId' => $taskId,
            'subject' => $requestData['title'],
            'commentBody' => $requestData['body'],
            'signature' => $usersSignature,
            'taskLink' => $baseFrontURL->getValue() . '/tasks/' . $taskId
        ];
        $params = [
            'subject' => 'LanHelpdesk - ' . '[#' . $taskId . ']' . ' ' . $requestData['title'],
            'from' => $email,
            'to' => $emailAddresses,
            'body' => $this->renderView('@APITask/Emails/comment.html.twig', $templateParams),
            'attachment' => $attachmentParams
        ];

        return $params;
    }

    /**
     * @param Task $task
     * @param string $loggedUserEmail
     * @param array $emailAddresses
     * @return array
     */
    private function getEmailForAddCommentNotification(Task $task, string $loggedUserEmail, array $emailAddresses):array
    {
        $notificationEmailAddresses = [];

        $requesterEmail = $task->getRequestedBy()->getEmail();
        if ($loggedUserEmail !== $requesterEmail && !in_array($requesterEmail, $emailAddresses) && !in_array($requesterEmail, $notificationEmailAddresses)) {
            $notificationEmailAddresses[] = $requesterEmail;
        }

        $followers = $task->getFollowers();
        if (count($followers) > 0) {
            /** @var User $follower */
            foreach ($followers as $follower) {
                $followerEmail = $follower->getEmail();
                if ($loggedUserEmail !== $followerEmail && !in_array($followerEmail, $emailAddresses) && !in_array($followerEmail, $notificationEmailAddresses)) {
                    $notificationEmailAddresses[] = $followerEmail;
                }
            }
        }

        $assignedUsers = $task->getTaskHasAssignedUsers();
        if (count($assignedUsers) > 0) {
            /** @var TaskHasAssignedUser $item */
            foreach ($assignedUsers as $item) {
                $assignedUserEmail = $item->getUser()->getEmail();
                if ($loggedUserEmail !== $assignedUserEmail && !in_array($assignedUserEmail, $emailAddresses) && !in_array($assignedUserEmail, $notificationEmailAddresses)) {
                    $notificationEmailAddresses[] = $assignedUserEmail;
                }
            }
        }

        return $notificationEmailAddresses;
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


