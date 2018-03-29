<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\File;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAttachment;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     *         "data":
     *         [
     *              {
     *                  "id": 1,
     *                  "slug": "zsskcd-jpg-2016-12-17-15-36"
     *              },
     *              {
     *                  "id": 2,
     *                  "slug": "zsskcd-jpg-2016-12-17-15-37"
     *              }
     *         ],
     *        "_links":[]
     *        "total": 1
     *      }
     *
     * @ApiDoc(
     *  description="Returns a list of tasks attachments slugs.",
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
     * @param int $taskId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \LogicException
     */
    public function listOfTasksAttachmentsAction(int $taskId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_list_of_tasks_attachments', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_LIST_OF_TASK_ATTACHMENTS, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $options['task'] = $task;
        $attachmentArray = $this->get('task_additional_service')->getTaskAttachmentsResponse($options);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($attachmentArray));
        return $response;
    }

    /**
     *  ### Response ###
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
     *              "add attachment to the Task": "/api/v1/task-bundle/tasks/11991/add-attachment/zsskcd-jpg-2016-12-17-15-36",
     *              "remove attachment from the Task": "/api/v1/task-bundle/tasks/11991/remove-attachment/zsskcd-jpg-2016-12-17-15-36"
     *          }
     *      }
     *
     * @ApiDoc(
     *  description="Add a new attachment to the Task. Return an Added Entity Slug.",
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
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param string $slug
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @internal param int $userId
     */
    public function addAttachmentToTaskAction(int $taskId, string $slug): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_add_attachment_to_task', ['taskId' => $taskId, 'slug' => $slug]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        $fileEntity = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug,
        ]);

        if (!$fileEntity instanceof File) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'File with requested Slug does not exist in DB!']));
            return $response;
        }

        // Check if the File exists in a web-page file system
        $uploadDir = $this->getParameter('upload_dir');
        $file = $uploadDir . DIRECTORY_SEPARATOR . $fileEntity->getUploadDir() . DIRECTORY_SEPARATOR . $fileEntity->getTempName();

        if (!file_exists($file)) {
            $response = $response->setStatusCode(StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'File with requested Slug does not exist in a web-page File System!']));
            return $response;
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_ATTACHMENT_TO_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        if ($this->canAddAttachmentToTask($task, $slug)) {
            $taskHasAttachment = new TaskHasAttachment();
            $taskHasAttachment->setTask($task);
            $taskHasAttachment->setSlug($slug);
            $task->addTaskHasAttachment($taskHasAttachment);
            $this->getDoctrine()->getManager()->persist($taskHasAttachment);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();
        }


        $options['task'] = $taskId;
        $options['file'] = $file;
        $attachmentEntity = $this->get('task_additional_service')->getTaskOneAttachmentResponse($options);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($attachmentEntity));
        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *         "data":
     *          {
     *              "id": 5,
     *              "slug": "zsskcd-jpg-2016-12-17-15-36",
     *              "fileDir": "Upload dir",
     *              "fileName": "Temp name"
     *          },
     *          "_links":
     *          {
     *              "add attachment to the Task": "/api/v1/task-bundle/tasks/11991/add-attachment/zsskcd-jpg-2016-12-17-15-36",
     *              "remove attachment from the Task": "/api/v1/task-bundle/tasks/11991/remove-attachment/zsskcd-jpg-2016-12-17-15-36"
     *          }
     *      }
     *
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
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param string $slug
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @internal param int $userId
     */
    public function removeAttachmentFromTaskAction(int $taskId, string $slug): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_remove_attachment_from_task', ['taskId' => $taskId, 'slug' => $slug]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        $file = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug
        ]);

        if (!$file instanceof File) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Slug with requested Id does not exist!']));
            return $response;
        }

        if ($this->canAddAttachmentToTask($task, $slug)) {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Requested attachment is not an attachment of the requested Task!']));
            return $response;
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_ATTACHMENT_FROM_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $taskHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAttachment')->findOneBy([
            'task' => $task,
            'slug' => $slug
        ]);

        $this->getDoctrine()->getManager()->remove($taskHasAttachment);
        $this->getDoctrine()->getManager()->flush();

        $options['task'] = $taskId;
        $options['file'] = $file;
        $attachmentEntity = $this->get('task_additional_service')->getTaskOneAttachmentResponse($options);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($attachmentEntity));
        return $response;
    }

    /**
     * @param Task $task
     * @param string $slug
     * @return bool
     * @throws \LogicException
     */
    private function canAddAttachmentToTask(Task $task, string $slug): bool
    {
        $taskHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAttachment')->findOneBy([
            'task' => $task,
            'slug' => $slug
        ]);

        return (!$taskHasAttachment instanceof TaskHasAttachment);
    }
}