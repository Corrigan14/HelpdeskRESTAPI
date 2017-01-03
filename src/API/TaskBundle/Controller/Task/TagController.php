<?php

namespace API\TaskBundle\Controller\Task;

use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class TagController
 *
 * @package API\TaskBundle\Controller\Task
 */
class TagController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *         "0":
     *         {
     *            "id": 19,
     *            "title": "Home",
     *            "color": "DFD112",
     *            "public": false,
     *            "created_by": ⊕{...}
     *         },
     *         "1":
     *         {
     *            "id": 20,
     *            "title": "Work",
     *            "color": "DFD115",
     *            "public": true,
     *            "created_by": ⊕{...}
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Add a new Tag to the Task. Returns array of tasks tags",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="tagId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of tag"
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
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $tagId
     * @return JsonResponse|Response
     */
    public function addTagToTaskAction(int $taskId, int $tagId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($tagId);

        if (!$tag instanceof Tag) {
            return $this->createApiResponse([
                'message' => 'Tag with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task,
            'tag' => $tag
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TAG_TO_TASK, $options)) {
            return $this->accessDeniedResponse();
        }

        if ($this->canAddTagToTask($task, $tag)) {
            $task->addTag($tag);
            $tag->addTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($tag);
            $this->getDoctrine()->getManager()->flush();
        }

        $arrayOfTags = $task->getTags();
        return $this->createApiResponse($arrayOfTags, StatusCodesHelper::CREATED_CODE);
    }

    /**
     * ### Response ###
     *      {
     *         "0":
     *         {
     *            "id": 19,
     *            "title": "Home",
     *            "color": "DFD112",
     *            "public": false,
     *            "created_by": ⊕{...}
     *         },
     *         "1":
     *         {
     *            "id": 20,
     *            "title": "Work",
     *            "color": "DFD115",
     *            "public": true,
     *            "created_by": ⊕{...}
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Remove the Tag from the Task. Returns array of tasks tags",
     *  requirements={
     *     {
     *       "name"="tagId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="userId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of tag"
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
     *      200 ="The tag was successfully removed from task",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $tagId
     * @return JsonResponse|Response
     */
    public function removeTagFromTaskAction(int $taskId, int $tagId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($tagId);

        if (!$tag instanceof Tag) {
            return $this->createApiResponse([
                'message' => 'Tag with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task,
            'tag' => $tag
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_TAG_FROM_TASK, $options)) {
            return $this->accessDeniedResponse();
        }

        if (!$this->canAddTagToTask($task, $tag)) {
            $task->removeTag($tag);
            $tag->removeTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($tag);
            $this->getDoctrine()->getManager()->flush();

            $arrayOfTags = $task->getTags();
            return $this->createApiResponse($arrayOfTags, StatusCodesHelper::SUCCESSFUL_CODE);
        }

        return $this->createApiResponse([
            'message' => 'Task does not contains requested tag!',
        ], StatusCodesHelper::NOT_FOUND_CODE);
    }

    /**
     * @param Task $task
     * @param Tag $tag
     * @return bool
     */
    private function canAddTagToTask(Task $task, Tag $tag):bool
    {
        $taskHasTags = $task->getTags();

        if (in_array($tag, $taskHasTags->toArray(), true)) {
            return false;
        }
        return true;
    }
}