<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FollowerController
 *
 * @package API\TaskBundle\Controller\Task
 */
class FollowerController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *         "data":
     *         [
     *           {
     *              "id": 2575,
     *              "username": "admin",
     *              "email": "admin@admin.sk",
     *              "name": "admin",
     *              "surname": "adminovic"
     *           },
     *           {
     *              "id": 2576,
     *              "username": "admin2",
     *              "email": "admin2@admin.sk",
     *              "name": "user",
     *              "surname": "userovic"
     *           },
     *
     *        ]
     *        "_links": [],
     *        "total": 2
     *      }
     *
     * @ApiDoc(
     *  description="Returns a list of tasks followers.",
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
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $taskId
     * @return Response
     * @throws \LogicException
     */
    public function listOfTasksFollowersAction(int $taskId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_list_of_tasks_followers', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_LIST_OF_TASK_FOLLOWERS, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $options['task'] = $task;
        $followersArray = $this->get('task_additional_service')->getTaskFollowerResponse($options);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($followersArray));
        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *          "id": 458,
     *          "username": "customer37",
     *          "email": "customer@customer37.sk",
     *          "name": "Customer37",
     *          "surname": "Customerovic37"
     *       },
     *       "_links":
     *      {
     *         "add follower to the Task": "/api/v1/task-bundle/tasks/11991/add-follower/458",
     *         "remove follower from the Task": "/api/v1/task-bundle/tasks/11991/remove-follower/458"
     *      }
     *    }
     *
     * @ApiDoc(
     *  description="Add a new follower to the Task. Returns added user.",
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
     *       "description"="The id of user followed task"
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
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $userId
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function addFollowerToTaskAction(int $taskId, int $userId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_add_follower_to_task', ['taskId' => $taskId, 'userId' => $userId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User with requested Id does not exist!']));
            return $response;
        }

        $options = [
            'task' => $task,
            'follower' => $user
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TASK_FOLLOWER, $options)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        if ($this->canAddTaskFollower($user, $task)) {
            $task->addFollower($user);

            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();
        }

        $options['task'] = $taskId;
        $options['user'] = $user;
        $followerEntity = $this->get('task_additional_service')->getTaskOneFollowerResponse($options);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($followerEntity));
        return $response;
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *          "id": 458,
     *          "username": "customer37",
     *          "email": "customer@customer37.sk",
     *          "name": "Customer37",
     *          "surname": "Customerovic37"
     *       },
     *       "_links":
     *      {
     *         "add follower to the Task": "/api/v1/task-bundle/tasks/11991/add-follower/458",
     *         "remove follower from the Task": "/api/v1/task-bundle/tasks/11991/remove-follower/458"
     *      }
     *    }
     *
     * @ApiDoc(
     *  description="Remove the follower from the Task. Returns Removed User.",
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
     *       "description"="The id of user followed task"
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
     *      200 ="The follower was successfully removed",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $userId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function removeFollowerFromTaskAction(int $taskId, int $userId)
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tasks_remove_follower_from_task', ['taskId' => $taskId, 'userId' => $userId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User with requested Id does not exist!']));
            return $response;
        }

        $options = [
            'task' => $task,
            'follower' => $user
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_TASK_FOLLOWER, $options)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        if (!$this->canAddTaskFollower($user, $task)) {
            $task->removeFollower($user);
            $user->removeFollowedTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            $options['task'] = $taskId;
            $options['user'] = $user;
            $followerEntity = $this->get('task_additional_service')->getTaskOneFollowerResponse($options);

            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode($followerEntity));
            return $response;
        }

        $response = $response->setStatusCode(StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
        $response = $response->setContent(StatusCodesHelper::NOT_FOUND_MESSAGE);
        return $response;
    }

    /**
     * @param User $user
     * @param Task $task
     * @return bool
     * @throws \LogicException
     */
    private function canAddTaskFollower(User $user, Task $task): bool
    {
        $taskHasFollower = $task->getFollowers();

        if (in_array($user, $taskHasFollower->toArray(), true)) {
            return false;
        }
        return true;
    }

}