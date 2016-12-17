<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Security\StatusOptions;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AssignController
 *
 * @package API\TaskBundle\Controller\Task
 */
class AssignController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *         "0":
     *         {
     *           "id": 17,
     *           "username": "user",
     *           "email": "user@user.sk",
     *           "roles": "[\"ROLE_USER\"]",
     *           "is_active": true,
     *           "acl": "[]",
     *           "company": ⊕{...},
     *           "followed_tasks": {}
     *         },
     *         "1":
     *         {
     *           "id": 18,
     *           "username": "testuser2",
     *           "email": "testuser2@user.sk",
     *           "roles": "[\"ROLE_USER\"]",
     *           "is_active": true,
     *           "acl": "[]",
     *           "company": ⊕{...},
     *           "followed_tasks": {}
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns array of users assigned to task",
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
     *      201 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @return Response
     */
    public function listOfTasksAssignedUsersAction(Request $request, int $taskId)
    {
        $page = $request->get('page') ?: 1;
    }

    /**
     * ### Response ###
     *      {
     *         "0":
     *         {
     *           "id": 17,
     *           "username": "user",
     *           "email": "user@user.sk",
     *           "roles": "[\"ROLE_USER\"]",
     *           "is_active": true,
     *           "acl": "[]",
     *           "company": ⊕{...},
     *           "followed_tasks": {}
     *         },
     *         "1":
     *         {
     *           "id": 18,
     *           "username": "testuser2",
     *           "email": "testuser2@user.sk",
     *           "roles": "[\"ROLE_USER\"]",
     *           "is_active": true,
     *           "acl": "[]",
     *           "company": ⊕{...},
     *           "followed_tasks": {}
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Assign task to the user - create taskHasAssignedUser Entity. Status of this task is set to StatusOption: NEW.
     *  Returns array of users assigned to task",
     *  requirements={
     *     {
     *       "name"="taskId",
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
     *  input={"class"="API\TaskBundle\Entity\TaskHasAssignedUser"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      201 ="The task was successfully assigned to the user",
     *      400 ="Bad Request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @param int $userId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAssignUserToTaskAction(Request $request, int $taskId, int $userId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task,
            'user' => $user
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::ASSIGN_USER_TO_TASK, $options)) {
            return $this->accessDeniedResponse();
        }

        if ($this->canAssignUserToTask($task, $user)) {
            $newStatus = $this->getDoctrine()->getRepository('APITaskBundle:Status')->findOneBy([
                'title' => StatusOptions::NEW,
            ]);
            if ($newStatus instanceof Status) {
                $taskHasAssignedUser = new TaskHasAssignedUser();
                $taskHasAssignedUser->setTask($task);
                $taskHasAssignedUser->setStatus($newStatus);
                $taskHasAssignedUser->setUser($user);

                $requestData = $request->request->all();
                return $this->updateTaskHasAssignUserEntity($taskHasAssignedUser, $requestData, true);
            }
            return $this->createApiResponse([
                'message' => 'New Status Entity does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        return $this->createApiResponse([
            'message' => 'User is already assigned to this task!',
        ], StatusCodesHelper::BAD_REQUEST_CODE);
    }

    /**
     * ### Response ###
     *      {
     *         "0":
     *         {
     *           "id": 17,
     *           "username": "user",
     *           "email": "user@user.sk",
     *           "roles": "[\"ROLE_USER\"]",
     *           "is_active": true,
     *           "acl": "[]",
     *           "company": ⊕{...},
     *           "followed_tasks": {}
     *         },
     *         "1":
     *         {
     *           "id": 18,
     *           "username": "testuser2",
     *           "email": "testuser2@user.sk",
     *           "roles": "[\"ROLE_USER\"]",
     *           "is_active": true,
     *           "acl": "[]",
     *           "company": ⊕{...},
     *           "followed_tasks": {}
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update taskHasAssignedUser Entity. Just status, time spent and status date could be updated!
     *  Returns array of users assigned to task",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="userId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of tag"
     *     },
     *     {
     *       "name"="statusId",
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
     *      200 ="The task was successfully updated",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @param int $userId
     * @param int $statusId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAssignUserToTaskAction(Request $request, int $taskId, int $userId, int $statusId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $taskHasAssignedEntity = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy([
            'user' => $user,
            'task' => $task
        ]);

        if (!$taskHasAssignedEntity instanceof TaskHasAssignedUser) {
            return $this->createApiResponse([
                'message' => 'Requested user is not assigned to the requested task!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_ASSIGN_USER_TO_TASK, $taskHasAssignedEntity)) {
            return $this->accessDeniedResponse();
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($statusId);

        if (!$status instanceof Status) {
            return $this->createApiResponse([
                'message' => 'Status with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $taskHasAssignedEntity->setStatus($status);
        $status->addTaskHasAssignedUser($taskHasAssignedEntity);

        $requestData = $request->request->all();

        $errors = $this->get('entity_processor')->processEntity($taskHasAssignedEntity, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($taskHasAssignedEntity);
            $this->getDoctrine()->getManager()->persist($status);
            $this->getDoctrine()->getManager()->flush();

            $assignedUsersArray = $this->getArrayOfUsersAssignedToTask($task);
            return $this->createApiResponse($assignedUsersArray, StatusCodesHelper::SUCCESSFUL_CODE);
        }

        return $this->createApiResponse($errors, StatusCodesHelper::INVALID_PARAMETERS_CODE);

    }

    /**
     * @ApiDoc(
     *  description="Delete taskHasAssignedUser Entity.",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of task"
     *     },
     *     {
     *       "name"="userId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user"
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
     *      204 ="Assigned user was successfully removed from task",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $taskId
     * @param int $userId
     * @return Response
     * @throws \LogicException
     */
    public function removeAssignUserFromTaskAction(int $taskId, int $userId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $taskHasAssignedEntity = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy([
            'user' => $user,
            'task' => $task
        ]);

        if (!$taskHasAssignedEntity instanceof TaskHasAssignedUser) {
            return $this->createApiResponse([
                'message' => 'Requested user is not assigned to the requested task!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_ASSIGN_USER_FROM_TASK, $taskHasAssignedEntity)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($taskHasAssignedEntity);
        $this->getDoctrine()->getManager()->flush();


        $assignedUsersArray = $this->getArrayOfUsersAssignedToTask($task);
        return $this->createApiResponse($assignedUsersArray, StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param Task $task
     * @param User $user
     * @return bool
     */
    private function canAssignUserToTask(Task $task, User $user): bool
    {
        $assignedUsersArray = $this->getArrayOfUsersAssignedToTask($task);

        if (in_array($user, $assignedUsersArray, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param TaskHasAssignedUser $taskHasAssignedUser
     * @param array $requestData
     * @param bool $create
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    private function updateTaskHasAssignUserEntity(TaskHasAssignedUser $taskHasAssignedUser, array $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($taskHasAssignedUser, $requestData);

        if (false === $errors) {
            $task = $taskHasAssignedUser->getTask();
            $task->addTaskHasAssignedUser($taskHasAssignedUser);
            $user = $taskHasAssignedUser->getUser();
            $user->addTaskHasAssignedUser($taskHasAssignedUser);
            $this->getDoctrine()->getManager()->persist($taskHasAssignedUser);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            $assignedUsersArray = $this->getArrayOfUsersAssignedToTask($task);
            return $this->createApiResponse($assignedUsersArray, $statusCode);
        }

        return $this->createApiResponse($errors, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param Task $task
     * @return array
     */
    private function getArrayOfUsersAssignedToTask(Task $task): array
    {
        $assignedUsers = $task->getTaskHasAssignedUsers();
        $assignedUsersArray = [];

        /** @var TaskHasAssignedUser $au */
        foreach ($assignedUsers as $au) {
            $assignedUsersArray[] = $au->getUser();
        }

        return $assignedUsersArray;
    }
}