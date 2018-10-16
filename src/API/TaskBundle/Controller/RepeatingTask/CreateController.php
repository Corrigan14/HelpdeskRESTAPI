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
 * Class CreateController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class CreateController extends ApiBaseController
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
     *  resource = true,
     *  description="Create a new Repeating Task Entity",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a Repeating's task related Task"
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
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function createAction(Request $request, int $taskId): Response
    {
        $locationURL = $this->generateUrl('repeating_task_create', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $dataValidation = $this->validateData($request, $taskId);
        if (false === $dataValidation['status']) {
            $response->setStatusCode($dataValidation['errorCode'])
                ->setContent(json_encode($dataValidation['errorMessage']));

            return $response;
        }

        $task = $dataValidation['task'];
        $requestData = $dataValidation['requestData'];

        $repeatingTask = new RepeatingTask();
        $repeatingTask->setTask($task);
        $repeatingTask->setIsActive(true);
        $repeatingTask->setLastRepeat(new \DateTime());
        $errors = $this->get('entity_processor')->processEntity($repeatingTask, $requestData);
        $updateEntity = $this->get('repeating_task_update_service')->updateRepeatingTask($errors, $repeatingTask, $requestData);

        if (isset($updateEntity['error'])) {
            $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE)
                ->setContent(json_encode(['message' => $updateEntity['error']]));

            return $response;
        }
        $repeatingTaskArray = $this->get('repeating_task_get_service')->getRepeatingTask($repeatingTask->getId());
        $response->setStatusCode(StatusCodesHelper::CREATED_CODE)
            ->setContent(json_encode($repeatingTaskArray));

        return $response;
    }

    /**
     * @param int $taskId
     * @param Request $request
     * @return array|bool
     * @throws \LogicException
     */
    private function validateData(Request $request, int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);
        if (!$task instanceof Task) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::NOT_FOUND_CODE,
                'errorMessage' => 'Task with requested Id does not exist!'
            ];
        }

        // User can see a repeating task if he is ADMIN or repeating task is related to the task where he has a permission to create a Task
        if (!$this->checkCreatePermission($task)) {
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
            'requestData' => $requestDataCheck['requestData']
        ];
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \LogicException
     */
    private function checkCreatePermission(Task $task): bool
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

        if (!$this->get('repeating_task_voter')->isGranted(VoteOptions::CREATE_REPEATING_TASK, $options)) {
            return false;
        }

        return true;
    }
}
