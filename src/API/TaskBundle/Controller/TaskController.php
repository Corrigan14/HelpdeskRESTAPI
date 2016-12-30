<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Entity\TaskData;
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
     *       "name"="status",
     *       "description"="A list of coma separated statuses f.i. new,in_progress"
     *     },
     *     {
     *       "name"="project",
     *       "description"="A list of coma separated ID's of Project f.i. 1,2,3"
     *     },
     *     {
     *       "name"="creator",
     *       "description"="A list of coma separated ID's of Creator f.i. 1,2,3"
     *     },
     *     {
     *       "name"="requester",
     *       "description"="A list of coma separated ID's of Requesters f.i. 1,2,3"
     *     },
     *     {
     *       "name"="company",
     *       "description"="A list of coma separated ID's of Companies f.i. 1,2,3"
     *     },
     *     {
     *       "name"="assigned",
     *       "description"="A list of coma separated ID's of Users f.i. 1,2,3"
     *     },
     *     {
     *       "name"="tag",
     *       "description"="A list of coma separated ID's of Tags f.i. 1,2,3"
     *     },
     *     {
     *       "name"="createdTime",
     *       "description"="A coma separated FROM, TO dates in format 2015-02-04T05:10:58+05:30"
     *     },
     *     {
     *       "name"="startedTime",
     *       "description"="A coma separated FROM, TO dates in format 2015-02-04T05:10:58+05:30"
     *     },
     *     {
     *       "name"="deadlineTime",
     *       "description"="A coma separated FROM, TO dates in format 2015-02-04T05:10:58+05:30"
     *     },
     *     {
     *       "name"="closedTime",
     *       "description"="A coma separated FROM, TO dates in format 2015-02-04T05:10:58+05:30"
     *     },
     *     {
     *       "name"="archived",
     *       "description"="If TRUE, just tasks from archived projects are returned"
     *     },
     *     {
     *       "name"="addedParameters",
     *       "description"="& separated data in form: taskAttributeId=value1,value2&taskAttributeId=value"
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
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request)
    {
        $page = $request->get('page') ?: 1;
        $filter = $this->getFilterData($request);

        dump($filter);

        $options = [
            'loggedUser' => $this->getUser(),
            'isAdmin' => $this->get('task_voter')->isAdmin(),
            'filters' => $filter,
        ];

        // Check if logged user has access to show requested data
        if (!$this->get('task_voter')->isGranted(VoteOptions::LIST_TASKS, $options)) {
            return $this->accessDeniedResponse();
        }

        $tasksArray = $this->get('task_service')->getTasksResponse($page, $options);
        return $this->json($tasksArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $response = $this->get('task_service')->getTaskResponse($task);

        return $this->createApiResponse($response, StatusCodesHelper::SUCCESSFUL_CODE);
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
     * @param Request $request
     * @param int|string $projectId
     * @param int|string $requestedUserId
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAction(Request $request, $projectId = 'all', $requestedUserId = 'all')
    {
        $task = new Task();

        // Check if project and requested user exists
        if ('all' !== $projectId && '{projectId}' !== $projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setProject($project);

        } else {
            $project = null;
        }

        if ('all' !== $requestedUserId && '{requestedUserId}' !== $requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);

        } else {
            $requestedUser = null;
            $task->setRequestedBy($this->getUser());
        }

        // Check if user can create task in selected project
        if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK, $project)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $task->setCreatedBy($this->getUser());
        $task->setImportant(false);

        return $this->updateTaskEntity($task, $requestData, true);
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
     * @param int $id
     * @param Request $request
     * @param bool|string $projectId
     * @param bool|string $requestedUserId
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request, $projectId = 'all', $requestedUserId = 'all')
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
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setProject($project);
        }

        if ('all' !== $requestedUserId && '{requestedUserId}' !== $requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateTaskEntity($task, $requestData);
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
     * @param int $id
     * @param Request $request
     * @param bool|string $projectId
     * @param bool|string $requestedUserId
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updatePartialAction(int $id, Request $request, $projectId = 'all', $requestedUserId = 'all')
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
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setProject($project);
        }

        if ('all' !== $requestedUserId && '{requestedUserId}' !== $requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateTaskEntity($task, $requestData);
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

        if (!$this->get('task_voter')->isGranted(VoteOptions::DELETE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param Task $task
     * @param array $requestData
     * @param bool $create
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    private function updateTaskEntity(Task $task, array $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($task, $requestData);

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
                            'taskAttribute' => $taskAttribute,
                            'task' => $task,
                        ]);

                        if (!$cd instanceof TaskData) {
                            $cd = new TaskData();
                            $cd->setTask($task);
                            $cd->setTaskAttribute($taskAttribute);
                        }

                        $cdErrors = $this->get('entity_processor')->processEntity($cd, ['value' => $value]);
                        if (false === $cdErrors) {
                            $task->addTaskDatum($cd);
                            $this->getDoctrine()->getManager()->persist($task);
                            $this->getDoctrine()->getManager()->persist($cd);
                            $this->getDoctrine()->getManager()->flush();
                        } else {
                            $this->createApiResponse([
                                'message' => 'The value of task_data with key: ' . $key . ' is invalid',
                            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        }
                    } else {
                        return $this->createApiResponse([
                            'message' => 'The key: ' . $key . ' of Task Attribute is not valid (Task Attribute with this ID doesn\'t exist)',
                        ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    }
                }
            }

            $response = $this->get('task_service')->getTaskResponse($task);
            return $this->createApiResponse($response, $statusCode);
        }

        return $this->createApiResponse($errors, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getFilterData(Request $request): array
    {
        // Ina-beznejsia moznost ako zadavat pole hodnot v URL adrese, ktora vracia priamo pole: index.php?id[]=1&id[]=2&id[]=3&name=john
        // na zakodovanie dat do URL je mozne pouzit encodeURIComponent

        $filter = [];

        $status = $request->get('status');
        $project = $request->get('project');
        $creator = $request->get('creator');
        $requester = $request->get('requester');
        $company = $request->get('company');
        $assigned = $request->get('assigned');
        $tag = $request->get('tag');
        $created = $request->get('createdTime');
        $started = $request->get('startedTime');
        $deadline = $request->get('deadlineTime');
        $closed = $request->get('closedTime');
        $archived = $request->get('archived');
        $addedParameters = $request->get('addedParameters');

        if (null !== $status) {
            $filter['status'] = explode(",", $status);
        }
        if (null !== $project) {
            $filter['project'] = explode(",", $project);
        }
        if (null !== $creator) {
            $filter['createdBy'] = explode(",", $creator);
        }
        if (null !== $requester) {
            $filter['requestedBy'] = explode(",", $requester);
        }
        if (null !== $company) {
            $filter['company'] = explode(",", $company);
        }
        if (null !== $assigned) {
            $filter['assigned'] = explode(",", $assigned);
        }
        if (null !== $tag) {
            $filter['tag'] = explode(",", $tag);
        }
        if (null !== $created) {
            $filter['created'] = explode(",", $created);
        }
        if (null !== $started) {
            $filter['started'] = explode(",", $started);
        }
        if (null !== $deadline) {
            $filter['deadline'] = explode(",", $deadline);
        }
        if (null !== $closed) {
            $filter['closed'] = explode(",", $closed);
        }
        if (null !== $archived) {
            $filter['archived'] = explode(",", $archived);
        }
        if (null !== $addedParameters) {
            $arrayOfAddedParameters = explode("&", $addedParameters);
            if (!empty($arrayOfAddedParameters[0])) {
                foreach ($arrayOfAddedParameters as $value) {
                    $strpos = explode('=', $value);
                    $attributeId = $strpos[0];
                    $attributeValues = explode(",", $strpos[1]);
                    $filter[$attributeId] = $attributeValues;
                }
            }
        }

        return $filter;
    }
}
