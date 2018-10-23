<?php

namespace API\TaskBundle\Controller\TaskParts;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\VoteOptions;
use Doctrine\Common\Collections\Collection;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RepeatingTaskController
 * @package API\TaskBundle\Controller\Task
 */
class RepeatingTaskController extends ApiBaseController
{

    /**
     *  ### Response ###
     *      {
     *       "data":
     *       [
     *          {
     *              "id": 3,
     *              "title": "Weekly repeating task",
     *              "startAt": 1530385892,
     *              "interval": "day",
     *              "intervalLength": "2",
     *              "repeatsNumber": 10,
     *              "createdAt": 1530385892,
     *              "updatedAt": 1530385892,
     *              "is_active": true
     *          },
     *          {
     *              "id": 4,
     *              "title": "Monthly repeating task",
     *              "startAt": 1530385892,
     *              "interval": "month",
     *              "intervalLength": "2",
     *              "repeatsNumber": 10,
     *              "createdAt": 1530385892,
     *              "updatedAt": 1530385892,
     *              "is_active": false
     *          }
     *       ],
     *       "total": 2
     *      }
     *
     * @ApiDoc(
     *  description="Returns a list of Task's Repeating Task Entities",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a processed object"
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
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function listAction(int $taskId): Response
    {
        $locationURL = $this->generateUrl('tasks_list_of_tasks_repeating_tasks', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);
        if (!$task instanceof Task) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));

            return $response;
        }

        // User can see repeating tasks of a requested Task if he is ADMIN or if he has a permission to view requested task
        if (!$this->checkViewPermission($task)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

        $repeatingTasks = $task->getRepeatingTasks();
        $repeatingTaskArray = $this->formatRepeatingTaskArray($repeatingTasks);
        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode($repeatingTaskArray));

        return $response;
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \LogicException
     */
    private function checkViewPermission(Task $task): bool
    {
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            return false;
        }

        return true;
    }

    /**
     * @param Collection $repeatingTasks
     * @return array
     */
    private function formatRepeatingTaskArray(Collection $repeatingTasks):array
    {
        $response['data'] = [];

        /** @var RepeatingTask $repeatingTask */
        foreach ($repeatingTasks as $repeatingTask) {
            $response['data'][]=[
                'id' => $repeatingTask->getId(),
                'title' => $repeatingTask->getTitle(),
                'startAt' => $repeatingTask->getStartAt(),
                'interval' => $repeatingTask->getInterval(),
                'intervalLength'=> $repeatingTask->getIntervalLength(),
                'repeatsNumber' => $repeatingTask->getRepeatsNumber(),
                'createdAt' => $repeatingTask->getCreatedAt(),
                'updatedAt' => $repeatingTask->getUpdatedAt(),
                'is_active' => $repeatingTask->getIsActive()
            ];
        }
        $response['total'] = \count($repeatingTasks);

        return $response;
    }
}
