<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Entity\Task;
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
     *           "put": "/api/v1/entityName/id",
     *           "patch": "/api/v1/entityName/id",
     *           "delete": "/api/v1/entityName/id"
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

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);
        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        // User can see a repeating task if he is ADMIN or repeating task is related to the task where he has a permission to create a Task
        if (!$this->checkCreatePermission($task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestDataCheck = $this->get('api_base.service')->checkRequestData($request, $this->getAllowedEntityParams());
        if (isset($requestDataCheck['error'])) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => $requestDataCheck['error']]));
            return $response;
        }

        $repeatingTask = new RepeatingTask();
        $repeatingTask->setTask($task);
        $repeatingTask->setIsActive(true);
        $errors = $this->get('entity_processor')->processEntity($repeatingTask, $requestDataCheck['requestData']);
        $updateEntity = $this->get('repeating_task_update_service')->updateRepeatingTask($errors, $repeatingTask, $requestDataCheck['requestData']);

        if (isset($updateEntity['error'])) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => $updateEntity['error']]));
            return $response;
        }
        $repeatingTaskArray = $this->get('repeating_task_get_service')->getRepeatingTask($repeatingTask->getId());
        $response = $response->setStatusCode(StatusCodesHelper::CREATED_CODE);
        $response = $response->setContent(json_encode($repeatingTaskArray));
        return $response;
    }

    /**
     * @return array
     */
    private function getAllowedEntityParams(): array
    {
        return [
            'title',
            'startAt',
            'interval',
            'intervalLength',
            'repeatsNumber'
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
