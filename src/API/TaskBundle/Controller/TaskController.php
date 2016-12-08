<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
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
     *      404 ="Not found entity",
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
        $projectId = $request->get('project') ?: 'all';
        $creatorId = $request->get('creator') ?: 'all';
        $requestedUserId = $request->get('requested') ?: 'all';

        // Check if project, creator and requested users exists
        if ($projectId !== 'all') {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $projectParam = $projectId;
        } else {
            $projectParam = false;
        }

        if ($creatorId !== 'all') {
            $creator = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($creatorId);

            if (!$creator instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Creator with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $creatorParam = $creatorId;
        } else {
            $creatorParam = false;
        }

        if ($requestedUserId !== 'all') {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $requestedUserParam = $requestedUserId;
        } else {
            $requestedUserParam = false;
        }

        $options = [
            'project' => $projectParam,
            'creator' => $creatorParam,
            'requested' => $requestedUserParam,
            'loggedUser' => $this->getUser(),
            'isAdmin' => $this->get('task_voter')->isAdmin()
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
     *           "id": "2",
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
     *  description="Returns full Task Entity including extended Task Data",
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
     */
    public function getAction(int $id)
    {
        // TODO: Implement getAction() method.
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
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
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int|bool $projectId
     * @param int|bool $requestedUserId
     * @return JsonResponse|Response
     */
    public function createAction(Request $request, $projectId = false, $requestedUserId = false)
    {

    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
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
     * @param bool|int $projectId
     * @param bool|int $requestedUserId
     * @return JsonResponse|Response
     */
    public function updateAction(int $id, Request $request, $projectId = false, $requestedUserId = false)
    {
        // TODO: Implement updateAction() method.
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
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
     * @param bool|int $projectId
     * @param bool|int $requestedUserId
     * @return JsonResponse|Response
     */
    public function updatePartialAction(int $id, Request $request, $projectId = false, $requestedUserId = false)
    {
        // TODO: Implement updateAction() method.
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
     */
    public function deleteAction(int $id)
    {
        // TODO: Implement deleteAction() method.
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
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
     *  description="Add a new follower to the Task. Returns array of tasks followers",
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
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @param int $userId
     * @return JsonResponse|Response
     */
    public function addFollowerToTaskAction(Request $request, int $taskId, int $userId)
    {

    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
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
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @param int $userId
     * @return JsonResponse|Response
     */
    public function removeFollowerFromTaskAction(Request $request, int $taskId, int $userId)
    {

    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
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
     *  description="Add a new Tag to the Task. Returns array of tasks tags",
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
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @param int $tagId
     * @return JsonResponse|Response
     */
    public function addTagToTaskAction(Request $request, int $taskId, int $tagId)
    {

    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
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
     *  description="Remove the Tag from the Task. Returns array of tasks tags",
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
     *      200 ="The tag was successfully removed from task",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @param int $tagId
     * @return JsonResponse|Response
     */
    public function removeTagFromTaskAction(Request $request, int $taskId, int $tagId)
    {

    }
}
