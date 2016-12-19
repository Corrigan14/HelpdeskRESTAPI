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
     *         "0":
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
     * @throws \LogicException
     * @internal param int $userId
     */
    public function listOfTasksAttachmentsAction(Request $request, int $taskId)
    {
        $page = $request->get('page') ?: 1;

        $thaRepository = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAttachment');
        $options['task'] = $taskId;

        $attachmentArray = $this->get('task_additional_service')->getTaskAttachmentsResponse($taskId, $page);
        return $this->json($attachmentArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     * @throws \LogicException
     * @internal param int $userId
     */
    public function addAttachmentToTaskAction(int $taskId, string $slug)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $file = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug
        ]);

        if (!$file instanceof File) {
            return $this->createApiResponse([
                'message' => 'Attachment with requested Slug does not exist! Attachment has to be uploaded before added to task!',
            ], StatusCodesHelper::BAD_REQUEST_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_ATTACHMENT_TO_TASK, $task)) {
            return $this->accessDeniedResponse();
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

        $arrayOfAttachments = $this->getTaskAttachments($task);
        return $this->createApiResponse($arrayOfAttachments, StatusCodesHelper::CREATED_CODE);
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
     * @throws \LogicException
     * @internal param int $userId
     */
    public function removeAttachmentFromTaskAction(int $taskId, string $slug)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
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

        if ($this->canAddAttachmentToTask($task, $slug)) {
            return $this->createApiResponse([
                'message' => 'The requested attachment is not the attachment of the requested Task!',
            ], StatusCodesHelper::BAD_REQUEST_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_ATTACHMENT_FROM_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $taskHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAttachment')->findOneBy([
            'task' => $task,
            'slug' => $slug
        ]);

        $this->getDoctrine()->getManager()->remove($taskHasAttachment);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
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

    /**
     * @param Task $task
     * @return array
     * @throws \LogicException
     */
    private function getTaskAttachments(Task $task): array
    {
        $taskHasAttachments = $task->getTaskHasAttachments();
        $attachmentsOfTasks = [];

        if (count($taskHasAttachments) > 0) {
            /** @var TaskHasAttachment $tha */
            foreach ($taskHasAttachments as $tha) {
                $file = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
                    'slug' => $tha->getSlug(),
                ]);
                $attachmentsOfTasks[] = $file;
            }
        }

        return $attachmentsOfTasks;
    }
}