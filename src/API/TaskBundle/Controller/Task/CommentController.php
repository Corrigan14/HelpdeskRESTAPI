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
     *       [
     *          {
     *              "id": 4,
     *              "title": "Email - public",
     *              "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *              "createdAt": 1521708938,
     *              "updatedAt": 1521708938,
     *              "internal": false,
     *              "email": true,
     *              "email_to":
     *              [
     *                  "email@email.com"
     *              ],
     *              "email_cc":
     *              [
     *                  "email2@email.sk",
     *                  "email3@email.com"
     *              ],
     *              "email_bcc": null,
     *              "createdBy":
     *              {
     *                  "id": 313,
     *                  "username": "admin",
     *                  "email": "admin@admin.sk",
     *                  "name": "Admin",
     *                  "surname": "Adminovic",
     *                  "avatarSlug": null
     *              },
     *              "commentHasAttachments":
     *              [
     *                  "slug1",
     *                  "slug2"
     *              ],
     *              "hasParent": false,
     *              "parentId": null,
     *              "hasChild": true,
     *              "childId":
     *              [
     *                  2,
     *                  5
     *              ]
     *          },
     *       ]
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/tasks/8994/comments?page=1&limit=5&order=ASC&internal=false",
     *          "first": "/api/v1/task-bundle/tasks/8994/comments?page=1&limit=5&order=ASC&internal=false",
     *          "prev": "&limit=5&order=ASC&internal=false",
     *          "next": "&limit=5&order=ASC&internal=false",
     *          "last": "/api/v1/task-bundle/tasks/8994/comments?page=1&limit=5&order=ASC&internal=false"
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
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Pagination limit: 999 - returns all entities, null - returns 10 entities"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by CREATION DATE"
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
    public function tasksCommentsListAction(Request $request, int $taskId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_list_of_tasks_comments', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        // Check if logged user has access to show tasks comments
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_LIST_OF_TASKS_COMMENTS, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];
            $internal = $processedFilterParams['internal'];

            $options = [
                'task' => $taskId,
                'internal' => $internal,
                'limit' => $limit,
                'order' => $order,
                'filterForUrl' => '&limit=' . $limit . '&order=' . $order . '&internal=' . $internal
            ];
            $routeOptions = [
                'routeName' => 'tasks_list_of_tasks_comments',
                'routeParams' => ['taskId' => $taskId]
            ];

            $commentsArray = $this->get('task_additional_service')->getTaskCommentsResponse($options, $page, $routeOptions);
            $response = $response->setContent(json_encode($commentsArray));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }

        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *       "data":
     *       {
     *             "id": 4,
     *              "title": "Email - public",
     *              "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *              "createdAt": 1521708938,
     *              "updatedAt": 1521708938,
     *              "internal": false,
     *              "email": true,
     *              "email_to":
     *              [
     *                  "email@email.com"
     *              ],
     *              "email_cc":
     *              [
     *                  "email2@email.sk",
     *                  "email3@email.com"
     *              ],
     *              "email_bcc": null,
     *              "createdBy":
     *              {
     *                  "id": 313,
     *                  "username": "admin",
     *                  "email": "admin@admin.sk",
     *                  "name": "Admin",
     *                  "surname": "Adminovic",
     *                  "avatarSlug": null
     *              },
     *              "commentHasAttachments":
     *              [
     *                  "slug1",
     *                  "slug2"
     *              ],
     *              "hasParent": false,
     *              "parentId": null,
     *              "hasChild": true,
     *              "childId":
     *              [
     *                  2,
     *                  5
     *              ]
     *        },
     *        "_links":
     *        {
     *           "delete": "/api/v1/task-bundle/tasks/comments/9"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Comment Entity",
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
    public function getTasksCommentAction(int $commentId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_comment', ['commentId' => $commentId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Comment with requested Id does not exist!']));
            return $response;
        }

        $task = $comment->getTask();
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASKS_COMMENT, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $commentArray = $this->get('task_additional_service')->getTaskCommentResponse($commentId);
        $response = $response->setContent(json_encode($commentArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);

        return $response;
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *             "id": 4,
     *              "title": "Email - public",
     *              "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *              "createdAt": 1521708938,
     *              "updatedAt": 1521708938,
     *              "internal": false,
     *              "email": true,
     *              "email_to":
     *              [
     *                  "email@email.com"
     *              ],
     *              "email_cc":
     *              [
     *                  "email2@email.sk",
     *                  "email3@email.com"
     *              ],
     *              "email_bcc": null,
     *              "createdBy":
     *              {
     *                  "id": 313,
     *                  "username": "admin",
     *                  "email": "admin@admin.sk",
     *                  "name": "Admin",
     *                  "surname": "Adminovic",
     *                  "avatarSlug": null
     *              },
     *              "commentHasAttachments":
     *              [
     *                  "slug1",
     *                  "slug2"
     *              ],
     *              "hasParent": false,
     *              "parentId": null,
     *              "hasChild": true,
     *              "childId":
     *              [
     *                  2,
     *                  5
     *              ]
     *        },
     *        "_links":
     *        {
     *          "delete": "/api/v1/task-bundle/tasks/comments/9"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Tasks Comment",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="Tasks id"
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
    public function createTasksCommentAction(Request $request, int $taskId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_add_comment_to_task', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_COMMENT_TO_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $comment = new Comment();
        $comment->setCreatedBy($this->getUser());
        $comment->setTask($task);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        return $this->updateCommentEntity($comment, $requestBody, $locationURL);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *             "id": 4,
     *              "title": "Email - public",
     *              "body": "Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien. Lorem Ipsum har vært bransjens standard for dummytekst helt siden 1500-tallet, da en ukjent boktrykker stokket en mengde bokstaver for å lage et prøveeksemplar av en bok. ",
     *              "createdAt": 1521708938,
     *              "updatedAt": 1521708938,
     *              "internal": false,
     *              "email": true,
     *              "email_to":
     *              [
     *                  "email@email.com"
     *              ],
     *              "email_cc":
     *              [
     *                  "email2@email.sk",
     *                  "email3@email.com"
     *              ],
     *              "email_bcc": null,
     *              "createdBy":
     *              {
     *                  "id": 313,
     *                  "username": "admin",
     *                  "email": "admin@admin.sk",
     *                  "name": "Admin",
     *                  "surname": "Adminovic",
     *                  "avatarSlug": null
     *              },
     *              "commentHasAttachments":
     *              [
     *                  "slug1",
     *                  "slug2"
     *              ],
     *              "hasParent": false,
     *              "parentId": null,
     *              "hasChild": true,
     *              "childId":
     *              [
     *                  2,
     *                  5
     *              ]
     *        }
     *        "_links":
     *        {
     *          "delete": "/api/v1/task-bundle/tasks/comments/9"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create Comments child Comment.",
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
    public function createCommentsCommentAction(Request $request, int $commentId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_add_comment_to_comment', ['commentId' => $commentId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Parent Comment with requested Id does not exist!']));
            return $response;
        }

        $task = $comment->getTask();

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_COMMENT_TO_COMMENT, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $commentNew = new Comment();
        $commentNew->setCreatedBy($this->getUser());
        $commentNew->setTask($task);
        $commentNew->setComment($comment);
        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        return $this->updateCommentEntity($commentNew, $requestBody, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Delete a Comment Entity",
     *  requirements={
     *     {
     *       "name"="commentId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="Processed object ID"
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
    public function deleteAction(int $commentId):Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_delete_tasks_comment', ['commentId' => $commentId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $comment = $this->getDoctrine()->getRepository('APITaskBundle:Comment')->find($commentId);

        if (!$comment instanceof Comment) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Parent Comment with requested Id does not exist!']));
            return $response;
        }

        $task = $comment->getTask();

        if (!$this->get('task_voter')->isGranted(VoteOptions::DELETE_COMMENT, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $this->getDoctrine()->getManager()->remove($comment);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::DELETED_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::DELETED_MESSAGE]));
        return $response;
    }

    /**
     * @param Comment $comment
     * @param $requestData
     * @param $locationUrl
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    private function updateCommentEntity(Comment $comment, $requestData, $locationUrl): Response
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

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);

        if (false !== $requestData) {
            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
            }

            foreach ($requestData as $key => $value) {
                if (!in_array($key, $allowedUnitEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for a Comment Entity!']));
                    return $response;
                }
            }

            // Add attachment to the Comment
            $attachment = [];
            if (array_key_exists('slug', $requestData)) {
                $slugArray = json_decode($requestData['slug'], true);
                if (!\is_array($slugArray)) {
                    $slugArray = explode(',', $requestData['slug']);
                }

                if (count($slugArray) > 0) {
                    foreach ($slugArray as $slug) {
                        $fileEntity = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
                            'slug' => $slug,
                        ]);

                        if (!$fileEntity instanceof File) {
                            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                            $response = $response->setContent(json_encode(['message' => 'File with requested Slug ' . $slug . ' does not exist in DB!']));
                            return $response;
                        }

                        // Check if File exists in a web-page file system
                        $uploadDir = $this->getParameter('upload_dir');
                        $file = $uploadDir . DIRECTORY_SEPARATOR . $fileEntity->getUploadDir() . DIRECTORY_SEPARATOR . $fileEntity->getTempName();

                        if (!file_exists($file)) {
                            $response = $response->setStatusCode(StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
                            $response = $response->setContent(json_encode(['message' => 'File with requested Slug ' . $slug . ' does not exist in a web-page File System!']));
                            return $response;
                        }

                        if ($this->canAddAttachmentToComment($comment, $slug)) {
                            $commentHasAttachment = new CommentHasAttachment();
                            $commentHasAttachment->setComment($comment);
                            $commentHasAttachment->setSlug($slug);
                            $comment->addCommentHasAttachment($commentHasAttachment);
                            $this->getDoctrine()->getManager()->persist($commentHasAttachment);
                        }

                        $attachment[] = [
                            'dir' => $fileEntity->getUploadDir(),
                            'name' => $fileEntity->getTempName()
                        ];
                    }
                }
                unset($requestData['slug']);
            }

            // Comment marked like an Email - email addresses validation
            $emailAddresses = [];
            $notValidEmailAddresses = [];
            $isEmail = false;
            if (isset($requestData['email'])) {
                $isEmail = ('true' === strtolower($requestData['email']) || 1 == $requestData['email']) ? true : false;
                if ($isEmail) {
                    if (isset($requestData['email_to'])) {
                        $this->processEmailAddress($requestData, $comment, $emailAddresses, $notValidEmailAddresses);

                        if (count($notValidEmailAddresses) > 0) {
                            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                            $response = $response->setContent(json_encode(['message' => 'Not valid email address: ' . implode(";", $notValidEmailAddresses)]));
                            return $response;
                        }
                    } else {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'Comment marked like an EMAIL has to contain at least one TO Email address']));
                        return $response;
                    }
                }
            }

            unset($requestData['email_to']);
            unset($requestData['email_cc']);
            unset($requestData['email_bcc']);

            $statusCode = $this->getCreateUpdateStatusCode(true);

            $errors = $this->get('entity_processor')->processEntity($comment, $requestData);

            if (false === $errors) {
                $task = $comment->getTask();
                $loggedUserEmail = $this->getUser()->getEmail();

                // If Comment is an Email - send Email
                if ($isEmail) {
                    $templateParams = $this->getTemplateParams($task->getId(), $requestData, $emailAddresses, $attachment);
                    $sendingError = $this->get('email_service')->sendEmail($templateParams);
                    if (true !== $sendingError) {
                        $data = [
                            'errors' => $sendingError,
                            'message' => 'Error with email sending!'
                        ];
                        $response = $response->setStatusCode(StatusCodesHelper::PROBLEM_WITH_EMAIL_SENDING);
                        $response = $response->setContent(json_encode($data));
                        return $response;
                    }
                }

                $this->getDoctrine()->getManager()->persist($comment);
                $this->getDoctrine()->getManager()->flush();

                $commentArray = $this->get('task_additional_service')->getTaskCommentResponse($comment->getId());
                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($commentArray));

                // Notification about a Comment creation to task REQUESTER, ASSIGNED USERS, FOLLOWERS
                $notificationEmailAddresses = $this->getEmailForAddCommentNotification($task, $loggedUserEmail, $emailAddresses);
                if (count($notificationEmailAddresses) > 0) {
                    $templateParams = $this->getTemplateParams($task->getId(), $requestData, $notificationEmailAddresses, $attachment);
                    $sendingError = $this->get('email_service')->sendEmail($templateParams);
                    if (true !== $sendingError) {
                        $data = [
                            'errors' => $sendingError,
                            'message' => 'Error with notification sending!'
                        ];
                        $response = $response->setStatusCode(StatusCodesHelper::PROBLEM_WITH_EMAIL_SENDING);
                        $response = $response->setContent(json_encode($data));
                        return $response;
                    }
                }

                return $response;
            } else {
                $data = [
                    'errors' => $errors,
                    'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
                ];
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode($data));
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;
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
        $emailCcArray = [];
        $emailBccArray = [];

        // Email TO
        $emailTo = json_decode($requestData['email_to'], true);
        if (!\is_array($emailTo)) {
            $emailTo = explode(',', $requestData['email_to']);
        }
        $emailToArray = $emailTo;

        // Check the correct email address
        foreach ($emailTo as $item) {
            $emailError = $validator->validate($item, $constraints);
            if (count($emailError)) {
                $notValidEmailAddresses[] = $item;
            }
        }
        $comment->setEmailTo($emailTo);


        // Email CC
        if (isset($requestData['email_cc'])) {
            $emailCc = json_decode($requestData['email_cc'], true);
            if (!\is_array($emailTo)) {
                $emailCc = explode(',', $requestData['email_cc']);
            }
            $emailCcArray = $emailCc;

            // Check the correct email address
            foreach ($emailCc as $item) {
                $emailError = $validator->validate($item, $constraints);
                if (count($emailError)) {
                    $notValidEmailAddresses[] = $item;
                }
            }
            $comment->setEmailCc($emailCc);
        }

        // Email BCC
        if (isset($requestData['email_bcc'])) {
            $emailBcc = json_decode($requestData['email_bcc'], true);
            if (!\is_array($emailTo)) {
                $emailBcc = explode(',', $requestData['email_bcc']);
            }
            $emailBccArray = $emailBcc;

            // Check the correct email address
            foreach ($emailBcc as $item) {
                $emailError = $validator->validate($item, $constraints);
                if (count($emailError)) {
                    $notValidEmailAddresses[] = $item;
                }
            }
            $comment->setEmailBcc($emailBcc);
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
    private function getTemplateParams(int $taskId, array $requestData, array $emailAddresses, $attachmentParams): array
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
        if (isset($requestData['title'])) {
            $title = $requestData['title'];
        } else {
            $title = ' ';
        }
        $templateParams = [
            'date' => $todayDate,
            'username' => $username,
            'email' => $email,
            'taskId' => $taskId,
            'subject' => $title,
            'commentBody' => $requestData['body'],
            'signature' => $usersSignature,
            'taskLink' => $baseFrontURL ? $baseFrontURL->getValue() : '' . '/tasks/' . $taskId
        ];

        $params = [
            'subject' => 'LanHelpdesk - ' . '[#' . $taskId . ']' . ' ' . $title,
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
    private function getEmailForAddCommentNotification(Task $task, string $loggedUserEmail, array $emailAddresses): array
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


