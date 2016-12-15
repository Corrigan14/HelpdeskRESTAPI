<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Entity\TaskData;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Security\StatusOptions;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskController
 *
 * @package API\TaskBundle\Controller
 */
class TaskController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *            "id": 7,
     *            "title": "Task 1 - user is creator, user is requested",
     *            "description": "Description of Task 1",
     *            "deadline": null,
     *            "important": false,
     *            "createdAt": ⊕{...}
     *            "updatedAt": ⊕{...}
     *          },
     *         {
     *            "id": 8,
     *            "title": "Task 2 - user is creator, admin is requested",
     *            "description": "Description of Task 2",
     *            "deadline": null,
     *            "important": false,
     *            "createdAt": ⊕{...}
     *            "updatedAt": ⊕{...}
     *         },
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/task?page=1&project=145&creator=21",
     *           "first": "/api/v1/task-bundle/task?page=1&project=145&creator=21",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/task?page=2&project=145&creator=21",
     *           "last": "/api/v1/task-bundle/task?page=3&project=145&creator=21"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of full Task Entities which includes extended Task Data",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="project",
     *       "description"="Project ID: if project exists, just tasks from this project are returned"
     *     },
     *     {
     *       "name"="creator",
     *       "description"="User ID: if user exists, just tasks which he created are returned"
     *     },
     *     {
     *       "name"="requested",
     *       "description"="User ID: if user exists, just tasks requested by him are returned"
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
     *      404 ="Not found entity"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request)
    {
        $page = $request->get('page') ?: 1;
        $projectId = $request->get('project') ?: 'all';
        $creatorId = $request->get('creator') ?: 'all';
        $requestedUserId = $request->get('requested') ?: 'all';

        // Check if project, creator and requested users exists
        if ($projectId !== 'all') {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!' ,
                ] , StatusCodesHelper::NOT_FOUND_CODE);
            }
            $projectParam = $projectId;
        } else {
            $projectParam = false;
        }

        if ($creatorId !== 'all') {
            $creator = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($creatorId);

            if (!$creator instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Creator with requested Id does not exist!' ,
                ] , StatusCodesHelper::NOT_FOUND_CODE);
            }
            $creatorParam = $creatorId;
        } else {
            $creatorParam = false;
        }

        if ($requestedUserId !== 'all') {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!' ,
                ] , StatusCodesHelper::NOT_FOUND_CODE);
            }
            $requestedUserParam = $requestedUserId;
        } else {
            $requestedUserParam = false;
        }

        $options = [
            'project'    => $projectParam ,
            'creator'    => $creatorParam ,
            'requested'  => $requestedUserParam ,
            'loggedUser' => $this->getUser() ,
            'isAdmin'    => $this->get('task_voter')->isAdmin(),
        ];

        // Check if logged user has access to show requested data
        if (!$this->get('task_voter')->isGranted(VoteOptions::LIST_TASKS , $options)) {
            return $this->accessDeniedResponse();
        }

        $tasksArray = $this->get('task_service')->getTasksResponse($page , $options);

        return $this->json($tasksArray , StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *          "id": 1,
     *          "title": "Task 1 - user is creator, user is requested",
     *          "description": "Description of Task 1",
     *          "important": false,
     *          "created_by":⊕{...},
     *          "requested_by": ⊕{...},
     *          "project": ⊕{...},
     *          "task_data":
     *          {
     *             "0":
     *             {
     *               "id": 1,
     *               "value": "some input"
     *             },
     *            "1":
     *            {
     *              "id": 2,
     *              "value": "select1"
     *            }
     *          }
     *          "followers": ⊕{...}
     *          "tags": ⊕{...}
     *          "created_at": "2016-12-09T07:39:52+0100",
     *          "updated_at": "2016-12-09T07:39:52+0100"
     *      },
     *       "_links":
     *       {
     *         "put": "/api/v1/task-bundle/tasks/1/project/all/user/all",
     *         "patch": "/api/v1/task-bundle/tasks/1/project/all/user/all",
     *         "delete": "/api/v1/task-bundle/tasks/1"
     *       }
     *
     * @ApiDoc(
     *  description="Returns full Task Entity including extended about Task Data",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output="API\TaskBundle\Entity\Task",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK , $task)) {
            return $this->accessDeniedResponse();
        }

        $response = $this->get('task_service')->getTaskResponse($task);

        return $this->createApiResponse($response , StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *          "id": 1,
     *          "title": "Task 1 - user is creator, user is requested",
     *          "description": "Description of Task 1",
     *          "important": false,
     *          "created_by":⊕{...},
     *          "requested_by": ⊕{...},
     *          "project": ⊕{...},
     *          "task_data":
     *          {
     *             "0":
     *             {
     *               "id": 1,
     *               "value": "some input"
     *             },
     *            "1":
     *            {
     *              "id": 2,
     *              "value": "select1"
     *            }
     *          }
     *          "followers": ⊕{...}
     *          "tags": ⊕{...}
     *          "created_at": "2016-12-09T07:39:52+0100",
     *          "updated_at": "2016-12-09T07:39:52+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task/2",
     *           "patch": "/api/v1/task-bundle/task/2",
     *           "delete": "/api/v1/task-bundle/task/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Task Entity with extra Task Data.
     *  This can be added by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.
     *  Project and/or Requested User can be added to this Task",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     },
     *     {
     *       "name"="requestedUserId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user requested task"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Task"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request    $request
     * @param int|string $projectId
     * @param int|string $requestedUserId
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAction(Request $request , $projectId = 'all' , $requestedUserId = 'all')
    {
        $task = new Task();

        // Check if project and requested user exists
        if ('all' !== $projectId && '{projectId}' !== $projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!' ,
                ] , StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setProject($project);

        } else {
            $project = null;
        }

        if ('all' !== $requestedUserId && '{requestedUserId}' !== $requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!' ,
                ] , StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);

        } else {
            $requestedUser = null;
            $task->setRequestedBy($this->getUser());
        }

        // Check if user can create task in selected project
        if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK , $project)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $task->setCreatedBy($this->getUser());
        $task->setImportant(false);

        return $this->updateTaskEntity($task , $requestData , true);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *          "id": 1,
     *          "title": "Task 1 - user is creator, user is requested",
     *          "description": "Description of Task 1",
     *          "important": false,
     *          "created_by":⊕{...},
     *          "requested_by": ⊕{...},
     *          "project": ⊕{...},
     *          "task_data":
     *          {
     *             "0":
     *             {
     *               "id": 1,
     *               "value": "some input"
     *             },
     *            "1":
     *            {
     *              "id": 2,
     *              "value": "select1"
     *            }
     *          }
     *          "followers": ⊕{...}
     *          "tags": ⊕{...}
     *          "created_at": "2016-12-09T07:39:52+0100",
     *          "updated_at": "2016-12-09T07:39:52+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task/2",
     *           "patch": "/api/v1/task-bundle/task/2",
     *           "delete": "/api/v1/task-bundle/task/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the Task Entity with extra Task Data.
     *  These could be updated by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.
     *  Project and Requested User could be updated by Id-s in URL.",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     },
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     },
     *     {
     *       "name"="requestedUserId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user requested task"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Task"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int         $id
     * @param Request     $request
     * @param bool|string $projectId
     * @param bool|string $requestedUserId
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id , Request $request , $projectId = 'all' , $requestedUserId = 'all')
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        // Check if project and requested user exists
        if ('all' !== $projectId && '{projectId}' !== $projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!' ,
                ] , StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setProject($project);
        }

        if ('all' !== $requestedUserId && '{requestedUserId}' !== $requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!' ,
                ] , StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK , $task)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateTaskEntity($task , $requestData);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *          "id": 1,
     *          "title": "Task 1 - user is creator, user is requested",
     *          "description": "Description of Task 1",
     *          "important": false,
     *          "created_by":⊕{...},
     *          "requested_by": ⊕{...},
     *          "project": ⊕{...},
     *          "task_data":
     *          {
     *             "0":
     *             {
     *               "id": 1,
     *               "value": "some input"
     *             },
     *            "1":
     *            {
     *              "id": 2,
     *              "value": "select1"
     *            }
     *          }
     *          "followers": ⊕{...}
     *          "tags": ⊕{...}
     *          "created_at": "2016-12-09T07:39:52+0100",
     *          "updated_at": "2016-12-09T07:39:52+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task/2",
     *           "patch": "/api/v1/task-bundle/task/2",
     *           "delete": "/api/v1/task-bundle/task/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the Task Entity with extra Task Data.
     *  These could be updated by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.
     *  Project and Requested User could be updated by Id-s in URL.",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     },
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     },
     *     {
     *       "name"="requestedUserId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user requested task"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Task"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int         $id
     * @param Request     $request
     * @param bool|string $projectId
     * @param bool|string $requestedUserId
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updatePartialAction(int $id , Request $request , $projectId = 'all' , $requestedUserId = 'all')
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        // Check if project and requested user exists
        if ('all' !== $projectId && '{projectId}' !== $projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!' ,
                ] , StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setProject($project);
        }

        if ('all' !== $requestedUserId && '{requestedUserId}' !== $requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!' ,
                ] , StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK , $task)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateTaskEntity($task , $requestData);
    }


    /**
     * @ApiDoc(
     *  description="Delete the Task entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
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
     *      204 ="The Entity was successfully deleted",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function deleteAction(int $id)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::DELETE_TASK , $task)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE ,
        ] , StatusCodesHelper::DELETED_CODE);
    }

    /**
     * ### Response ###
     *      {
     *         "0":
     *         {
     *            "id": 85,
     *            "username": "admin",
     *            "email": "admin@admin.sk",
     *            "roles": "[\"ROLE_ADMIN\"]",
     *            "is_active": true,
     *            "acl": "[]",
     *            "company": ⊕{...}
     *         },
     *         "1":
     *         {
     *            "id": 87,
     *            "username": "testuser2",
     *            "email": "testuser2@user.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "acl": "[]",
     *            "company": ⊕{...}
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Add a new follower to the Task. Returns a list of tasks followers",
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
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function addFollowerToTaskAction(int $taskId , int $userId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task'     => $task ,
            'follower' => $user,
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TASK_FOLLOWER , $options)) {
            return $this->accessDeniedResponse();
        }

        if ($this->canAddTaskFollower($user , $task)) {
            $task->addFollower($user);
            $user->addFollowedTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
        }

        $listOfTaskFollowers = $task->getFollowers();

        return $this->createApiResponse($listOfTaskFollowers , StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *       {
     *         "0":
     *         {
     *            "id": 85,
     *            "username": "admin",
     *            "email": "admin@admin.sk",
     *            "roles": "[\"ROLE_ADMIN\"]",
     *            "is_active": true,
     *            "acl": "[]",
     *            "company": ⊕{...}
     *         },
     *         "1":
     *         {
     *            "id": 87,
     *            "username": "testuser2",
     *            "email": "testuser2@user.sk",
     *            "roles": "[\"ROLE_USER\"]",
     *            "is_active": true,
     *            "acl": "[]",
     *            "company": ⊕{...}
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Remove the follower from the Task. Returns array of tasks followers",
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
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function removeFollowerFromTaskAction(int $taskId , int $userId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task'     => $task ,
            'follower' => $user,
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_TASK_FOLLOWER , $options)) {
            return $this->accessDeniedResponse();
        }

        if (!$this->canAddTaskFollower($user , $task)) {
            $task->removeFollower($user);
            $user->removeFollowedTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            $listOfTaskFollowers = $task->getFollowers();

            return $this->createApiResponse($listOfTaskFollowers , StatusCodesHelper::SUCCESSFUL_CODE);
        }

        return $this->notFoundResponse();
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
     *
     * @return JsonResponse|Response
     */
    public function addTagToTaskAction(int $taskId , int $tagId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($tagId);

        if (!$tag instanceof Tag) {
            return $this->createApiResponse([
                'message' => 'Tag with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task ,
            'tag'  => $tag,
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TAG_TO_TASK , $options)) {
            return $this->accessDeniedResponse();
        }

        if ($this->canAddTagToTask($task , $tag)) {
            $task->addTag($tag);
            $tag->addTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($tag);
            $this->getDoctrine()->getManager()->flush();
        }

        $arrayOfTags = $task->getTags();

        return $this->createApiResponse($arrayOfTags , StatusCodesHelper::CREATED_CODE);
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
     *
     * @return JsonResponse|Response
     */
    public function removeTagFromTaskAction(int $taskId , int $tagId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($tagId);

        if (!$tag instanceof Tag) {
            return $this->createApiResponse([
                'message' => 'Tag with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task ,
            'tag'  => $tag,
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::REMOVE_TAG_FROM_TASK , $options)) {
            return $this->accessDeniedResponse();
        }

        if (!$this->canAddTagToTask($task , $tag)) {
            $task->removeTag($tag);
            $tag->removeTask($task);
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->persist($tag);
            $this->getDoctrine()->getManager()->flush();

            $arrayOfTags = $task->getTags();

            return $this->createApiResponse($arrayOfTags , StatusCodesHelper::SUCCESSFUL_CODE);
        }

        return $this->createApiResponse([
            'message' => 'Task does not contains requested tag!' ,
        ] , StatusCodesHelper::NOT_FOUND_CODE);
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
     *  description="Assign task to the user - create taskHasAssignedUser Entity. Status of this task is set to
     *  StatusOption: NEW. Returns array of users assigned to task", requirements={
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
     * @param int     $taskId
     * @param int     $userId
     *
     * @return JsonResponse|Response
     */
    public function createAssignUserToTaskAction(Request $request , int $taskId , int $userId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $options = [
            'task' => $task ,
            'user' => $user,
        ];

        if (!$this->get('task_voter')->isGranted(VoteOptions::ASSIGN_USER_TO_TASK , $options)) {
            return $this->accessDeniedResponse();
        }

        if ($this->canAssignUserToTask($task , $user)) {
            $newStatus = $this->getDoctrine()->getRepository('APITaskBundle:Status')->findOneBy([
                'title' => StatusOptions::NEW ,
            ]);
            if ($newStatus instanceof Status) {
                $taskHasAssignedUser = new TaskHasAssignedUser();
                $taskHasAssignedUser->setTask($task);
                $taskHasAssignedUser->setStatus($newStatus);
                $taskHasAssignedUser->setUser($user);

                $requestData = $request->request->all();

                return $this->updateTaskHasAssignUserEntity($taskHasAssignedUser , $requestData , true);
            }

            return $this->createApiResponse([
                'message' => 'New Status Entity does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        return $this->createApiResponse([
            'message' => 'User is already assigned to this task!' ,
        ] , StatusCodesHelper::BAD_REQUEST_CODE);
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
     * @param int     $taskId
     * @param int     $userId
     * @param int     $statusId
     *
     * @return JsonResponse|Response
     */
    public function updateAssignUserToTaskAction(Request $request , int $taskId , int $userId , int $statusId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $taskHasAssignedEntity = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy([
            'user' => $user ,
            'task' => $task,
        ]);

        if (!$taskHasAssignedEntity instanceof TaskHasAssignedUser) {
            return $this->createApiResponse([
                'message' => 'Requested user is not assigned to the requested task!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_ASSIGN_USER_TO_TASK , $taskHasAssignedEntity)) {
            return $this->accessDeniedResponse();
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($statusId);

        if (!$status instanceof Status) {
            return $this->createApiResponse([
                'message' => 'Status with requested Id does not exist!' ,
            ] , StatusCodesHelper::NOT_FOUND_CODE);
        }

        $taskHasAssignedEntity->setStatus($status);
        $status->addTaskHasAssignedUser($taskHasAssignedEntity);

        $requestData = $request->request->all();

        $errors = $this->get('entity_processor')->processEntity($taskHasAssignedEntity , $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($taskHasAssignedEntity);
            $this->getDoctrine()->getManager()->persist($status);
            $this->getDoctrine()->getManager()->flush();

            $assignedUsersArray = $this->getArrayOfUsersAssignedToTask($task);

            return $this->createApiResponse($assignedUsersArray , StatusCodesHelper::SUCCESSFUL_CODE);
        }

        return $this->createApiResponse($errors , StatusCodesHelper::INVALID_PARAMETERS_CODE);

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
     *  description="Delete taskHasAssignedUser Entity. Returns array of users assigned to task",
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
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      204 ="The Entity was successfully removed",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param Request $request
     * @param int     $taskId
     * @param int     $userId
     *
     * @return JsonResponse|Response
     */
    public function removeAssignUserFromTaskAction(Request $request , int $taskId , int $userId)
    {

    }

    /**
     * @param Task  $task
     * @param array $requestData
     * @param bool  $create
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    private function updateTaskEntity(Task $task , array $requestData , $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($task , $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            /**
             * Fill TaskData Entity if some of its parameters were sent
             */
            if (isset($requestData['task_data']) && count($requestData['task_data']) > 0) {
                /** @var array $taskData */
                $taskData = $requestData['task_data'];
                foreach ($taskData as $key => $value) {
                    $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($key);
                    if ($taskAttribute instanceof TaskAttribute) {
                        $cd = $this->getDoctrine()->getRepository('APITaskBundle:TaskData')->findOneBy([
                            'taskAttribute' => $taskAttribute ,
                            'task'          => $task ,
                        ]);

                        if (!$cd instanceof TaskData) {
                            $cd = new TaskData();
                            $cd->setTask($task);
                            $cd->setTaskAttribute($taskAttribute);
                        }

                        $cdErrors = $this->get('entity_processor')->processEntity($cd , ['value' => $value]);
                        if (false === $cdErrors) {
                            $task->addTaskDatum($cd);
                            $this->getDoctrine()->getManager()->persist($task);
                            $this->getDoctrine()->getManager()->persist($cd);
                            $this->getDoctrine()->getManager()->flush();
                        } else {
                            $this->createApiResponse([
                                'message' => 'The value of task_data with key: ' . $key . ' is invalid' ,
                            ] , StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        }
                    } else {
                        return $this->createApiResponse([
                            'message' => 'The key: ' . $key . ' of Task Attribute is not valid (Task Attribute with this ID doesn\'t exist)' ,
                        ] , StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    }
                }
            }

            $response = $this->get('task_service')->getTaskResponse($task);

            return $this->createApiResponse($response , $statusCode);
        }

        return $this->createApiResponse($errors , StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param TaskHasAssignedUser $taskHasAssignedUser
     * @param array               $requestData
     * @param bool                $create
     *
     * @return Response
     */
    private function updateTaskHasAssignUserEntity(TaskHasAssignedUser $taskHasAssignedUser , array $requestData , $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($taskHasAssignedUser , $requestData);

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

            return $this->createApiResponse($assignedUsersArray , $statusCode);
        }

        return $this->createApiResponse($errors , StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param User $user
     * @param Task $task
     *
     * @return bool
     * @throws \LogicException
     */
    private function canAddTaskFollower(User $user , Task $task): bool
    {
        $taskHasFollower = $task->getFollowers();

        if (in_array($user , $taskHasFollower->toArray() , true)) {
            return false;
        }

        return true;
    }

    /**
     * @param Task $task
     * @param Tag  $tag
     *
     * @return bool
     */
    private function canAddTagToTask(Task $task , Tag $tag): bool
    {
        $taskHasTags = $task->getTags();

        if (in_array($tag , $taskHasTags->toArray() , true)) {
            return false;
        }

        return true;
    }

    /**
     * @param Task $task
     * @param User $user
     *
     * @return bool
     */
    private function canAssignUserToTask(Task $task , User $user): bool
    {
        $assignedUsersArray = $this->getArrayOfUsersAssignedToTask($task);

        if (in_array($user , $assignedUsersArray , true)) {
            return false;
        }

        return true;
    }

    /**
     * @param Task $task
     *
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
