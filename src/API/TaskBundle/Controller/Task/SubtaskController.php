<?php

namespace API\TaskBundle\Controller\Task;

use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskSubtask;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class SubtaskController
 * @package API\TaskBundle\Controller\Task
 */
class SubtaskController extends ApiBaseController
{

    /**
     * ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 37,
     *             "title": "The first Subtask",
     *             "done": true,
     *             "from": 1234567,
     *             "to": 1234567,
     *             "hours": 2.5,
     *             "createdAt": 123456778,
     *             "updatedAt": 123456778,
     *             "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             },
     *             "task":
     *             {
     *                "id": 2575,
     *                "title": "The main task"
     *             }
     *          },
     *          {
     *             "id": 37,
     *             "title": "The second Subtask",
     *             "done": false,
     *             "createdAt": 123456778,
     *             "updatedAt": 123456778,
     *             "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             },
     *             "task":
     *             {
     *                "id": 2575,
     *                "title": "The main task"
     *             }
     *          },
     *        ],
     *       "_links":[],
     *       "total": 12
     *     }
     *
     *
     * @ApiDoc(
     *  description="Return a list of task's Subtasks",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a task"
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
    public function listOfTasksSubtasksAction(int $taskId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_list_of_tasks_subtasks', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));

            return $response;
        }

        // Check if user can see a task
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

        $subtasksArray = $this->get('task_additional_service')->getTaskSubtasksResponse($task);
        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode($subtasksArray));

        return $response;
    }

    /**
     * ### Response ###
     *     {
     *       "data":
     *       {
     *          "id": 37,
     *          "title": "The first Subtask",
     *          "done": true,
     *          "from": 1234567,
     *          "to": 1234567,
     *          "hours": 2.5,
     *          "createdAt": 123456778,
     *          "updatedAt": 123456778,
     *          "createdBy":
     *          {
     *             "id": 2575,
     *             "username": "admin",
     *             "email": "admin@admin.sk"
     *          },
     *          "task":
     *          {
     *             "id": 2575,
     *             "title": "The main task"
     *          }
     *       }
     *       "_links":
     *       {
     *          "create subtask": "/api/v1/task-bundle/tasks/11998/subtask",
     *          "update subtask": "/api/v1/task-bundle/tasks/11998/subtask/15",
     *          "delete subtask": "/api/v1/task-bundle/tasks/11998/subtask/15"
     *       }
     *     }
     *
     *
     * @ApiDoc(
     *  description="Create a new Subtask in a requested Task",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a task"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\TaskSubtask"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\TaskSubtask"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid arguments"
     *  }
     * )
     *
     * @param int $taskId
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function createSubtaskAction(int $taskId, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_create_subtask', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));

            return $response;
        }

        // Check if user can edit a task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

        $subtask = new TaskSubtask();
        $subtask->setCreatedBy($this->getUser());
        $subtask->setTask($task);
        $subtask->setDone(false);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        return $this->updateEntity($subtask, $requestBody, false, $locationURL);
    }

    /**
     * ### Response ###
     *     {
     *       "data":
     *       {
     *          "id": 37,
     *          "title": "The first Subtask",
     *          "done": true,
     *          "from": 1234567,
     *          "to": 1234567,
     *          "hours": 2.5,
     *          "createdAt": 123456778,
     *          "updatedAt": 123456778,
     *          "createdBy":
     *          {
     *             "id": 2575,
     *             "username": "admin",
     *             "email": "admin@admin.sk"
     *          },
     *          "task":
     *          {
     *             "id": 2575,
     *             "title": "The main task"
     *          }
     *       }
     *       "_links":
     *       {
     *          "create subtask": "/api/v1/task-bundle/tasks/11998/subtask",
     *          "update subtask": "/api/v1/task-bundle/tasks/11998/subtask/15",
     *          "delete subtask": "/api/v1/task-bundle/tasks/11998/subtask/15"
     *       }
     *     }
     *
     *
     * @ApiDoc(
     *  description="Update a Subtask in a requested Task",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a task"
     *     },
     *     {
     *       "name"="subtaskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a task"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\TaskSubtask"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\TaskSubtask"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid arguments"
     *  }
     * )
     *
     * @param int $taskId
     * @param int $subtaskId
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function updateSubtaskAction(int $taskId, int $subtaskId, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_update_subtask', ['taskId' => $taskId, 'subtaskId' => $subtaskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));

            return $response;
        }

        // Check if user can edit a task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

        $subtask = $this->getDoctrine()->getRepository('APITaskBundle:TaskSubtask')->findOneBy([
            'id' => $subtaskId,
            'task' => $taskId
        ]);

        if (!$subtask instanceof TaskSubtask) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Subtask with requested Id for a requested task does not exist!']));

            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateEntity($subtask, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Delete a Subtask in a requested Task",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a task"
     *     },
     *     {
     *       "name"="subtaskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a task"
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
     *      204 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $taskId
     * @param int $subtaskId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function deleteSubtaskAction(int $taskId, int $subtaskId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_delete_subtask', ['taskId' => $taskId, 'subtaskId' => $subtaskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));

            return $response;
        }

        // Check if user can edit a task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

        $subtask = $this->getDoctrine()->getRepository('APITaskBundle:TaskSubtask')->findOneBy([
            'id' => $subtaskId,
            'task' => $taskId
        ]);

        if (!$subtask instanceof TaskSubtask) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Subtask with requested Id for a requested task does not exist!']));

            return $response;
        }

        $this->getDoctrine()->getManager()->remove($subtask);
        $this->getDoctrine()->getManager()->flush();

        $response->setStatusCode(StatusCodesHelper::DELETED_CODE)
            ->setContent(json_encode(['message' => StatusCodesHelper::DELETED_MESSAGE]));

        return $response;

    }

    /**
     * @param TaskSubtask $subtask
     * @param $requestData
     * @param bool $create
     * @param string $locationUrl
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    private function updateEntity(TaskSubtask $subtask, $requestData, $create = false, string $locationUrl): Response
    {
        $allowedUnitEntityParams = [
            'title',
            'done',
            'from',
            'to',
            'hours'
        ];

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);

        if (false === $requestData) {
            $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));

            return $response;
        }

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!\in_array($key, $allowedUnitEntityParams, true)) {
                $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE)
                    ->setContent(json_encode(['message' => $key . ' is not allowed parameter for Subtask Entity!']));

                return $response;
            }
        }

        if (array_key_exists('from', $requestData)) {
            $requestData['from'] = (int)$requestData['from'];
        }

        if (array_key_exists('to', $requestData)) {
            $requestData['to'] = (int)$requestData['to'];
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);
        $errors = $this->get('entity_processor')->processEntity($subtask, $requestData);
        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($subtask);
            $this->getDoctrine()->getManager()->flush();

            $subtaskArray = $this->get('task_additional_service')->getTaskSubtaskResponse($subtask->getTask()->getId(), $subtask->getId());
            $response->setStatusCode($statusCode)
                ->setContent(json_encode($subtaskArray));

            return $response;
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE)
            ->setContent(json_encode($data));

        return $response;


    }


}
