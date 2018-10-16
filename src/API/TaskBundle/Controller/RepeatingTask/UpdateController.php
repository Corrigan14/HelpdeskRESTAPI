<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\RepeatingTask\EntityParams;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UpdateController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class UpdateController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 4,
     *           "title": "Monthly repeating task",
     *           "startAt": 1530385892,
     *           "interval": "month",
     *           "intervalLength": "2",
     *           "repeatsNumber": 10,
     *           "createdAt": 1530385892,
     *           "updatedAt": 1530385892,
     *           "is_active": false,
     *           "task":
     *           {
     *               "id": 8996,
     *               "title": "Task 1"
     *           }
     *        },
     *        "_links":
     *        {
     *           "update": "/api/v1/task-bundle/repeating-tasks/22",
     *           "delete": "/api/v1/task-bundle/repeating-tasks/22",
     *           "inactivate": "/api/v1/task-bundle/repeating-tasks/22/inactivate",
     *           "restore": "/api/v1/task-bundle/repeating-tasks/22/restore"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update Repeating Task Entity",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a Repeating's task related Task"
     *     },
     *     {
     *       "name"="repeatingTaskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a Repeating task"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\RepeatingTask"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\RepeatingTask"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $repeatingTaskId
     * @param bool|int $taskId
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     */
    public function updateAction(int $repeatingTaskId, $taskId = false, Request $request): Response
    {
        $locationURL = $this->generateUrl('repeating_task_update', ['repeatingTaskId' => $repeatingTaskId]);
        if ($taskId) {
            $locationURL = $this->generateUrl('repeating_task_update_task', ['taskId' => $taskId, 'repeatingTaskId' => $repeatingTaskId]);
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $dataValidation = $this->validateData($request, $taskId, $repeatingTaskId);

        if (false === $dataValidation['status']) {
            $response->setStatusCode($dataValidation['errorCode'])
                ->setContent(json_encode($dataValidation['errorMessage']));

            return $response;
        }

        /** @var Task $task */
        $task = $dataValidation['task'];
        /** @var RepeatingTask $repeatingTask */
        $repeatingTask = $dataValidation['repeatingTask'];
        $requestData = $dataValidation['requestData'];

        $repeatingTask->setTask($task);
        $errors = $this->get('entity_processor')->processEntity($repeatingTask, $requestData);
        $updateEntity = $this->get('repeating_task_update_service')->updateRepeatingTask($errors, $repeatingTask, $requestData);
        if (isset($updateEntity['error'])) {
            $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE)
                ->setContent(json_encode(['message' => $updateEntity['error']]));

            return $response;
        }

        $repeatingTaskArray = $this->get('repeating_task_get_service')->getRepeatingTask($repeatingTask->getId());
        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode($repeatingTaskArray));

        return $response;
    }

    /**
     * @param Request $request
     * @param int $taskId
     * @param int $repeatingTaskId
     * @return array|bool
     * @throws \LogicException
     */
    private function validateData(Request $request, $taskId, int $repeatingTaskId): array
    {
        $repeatingTask = $this->getDoctrine()->getRepository('APITaskBundle:RepeatingTask')->find($repeatingTaskId);
        if (!$repeatingTask instanceof RepeatingTask) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::NOT_FOUND_CODE,
                'errorMessage' => 'Repeating task with requested Id does not exist!'
            ];

        }

        $task = $repeatingTask->getTask();
        if ($taskId) {
            $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);
        }
        if (!$task instanceof Task) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::NOT_FOUND_CODE,
                'errorMessage' => 'Task with requested Id does not exist!'
            ];
        }

        // User can see a repeating task if he is ADMIN or repeating task is related to the task where he has a permission to create a Task
        if (!$this->checkUpdatePermission($task)) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::ACCESS_DENIED_CODE,
                'errorMessage' => StatusCodesHelper::ACCESS_DENIED_MESSAGE
            ];
        }

        $requestDataCheck = $this->get('api_base.service')->checkRequestData($request, EntityParams::getAllowedEntityParams());
        if (isset($requestDataCheck['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => $requestDataCheck['error']
            ];
        }

        return [
            'status' => true,
            'task' => $task,
            'repeatingTask' => $repeatingTask,
            'requestData' => $requestDataCheck['requestData']
        ];
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \LogicException
     */
    private function checkUpdatePermission(Task $task): bool
    {
        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $tasksProject = $task->getProject()->getId();
        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $loggedUser,
            'project' => $tasksProject
        ]);

        $options = [
            'userHasProject' => $userHasProject
        ];

        if (!$this->get('repeating_task_voter')->isGranted(VoteOptions::UPDATE_REPEATING_TASK, $options)) {
            return false;
        }

        return true;
    }
}
