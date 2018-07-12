<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RepeatingTaskController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class RepeatingTaskController extends ApiBaseController implements ControllerInterface
{

    /**
     *  ### Response ###
     *     {
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
     *              "is_active": true,
     *              "task":
     *              {
     *                  "id": 8996,
     *                  "title": "Task 1"
     *              }
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
     *              "is_active": false,
     *              "task":
     *              {
     *                  "id": 8996,
     *                  "title": "Task 1"
     *              }
     *          }
     *       ],
     *       "_links":
     *       {
     *          "self": "/api/v1/task-bundle/repeating-tasks?page=2&limit=10&page=2&limit=10&order=DESC&isActive=true",
     *          "first": "/api/v1/task-bundle/repeating-tasks?page=1&limit=10&page=2&limit=10&order=DESC&isActive=true",
     *          "prev": "/api/v1/task-bundle/repeating-tasks?page=1&limit=10&page=2&limit=10&order=DESC&isActive=true",
     *          "next": false,
     *          "last": "/api/v1/task-bundle/repeating-tasks?page=1&limit=10&page=2&limit=10&order=DESC&isActive=true"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Repeating Task entities.",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE repeating tasks if this param is TRUE, only INACTIVE if param is FALSE"
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Limit for Pagination: 999 - returns all entities, null - returns 10 entities"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Title"
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
     *      200 ="Request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied"
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        $locationURL = $this->generateUrl('repeating_task_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        if (false === $requestBody) {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
            return $response;
        }

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $isAdmin = $this->get('api_base.voter')->isAdmin();
        $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody, false, $loggedUser, $isAdmin);

        $allowedLoggedUsersTasks = $this->get('task_service')->getUsersTasksId($loggedUser);
        $repeatingTaskArray = $this->get('repeating_task_service')->getRepeatingTasks($processedFilterParams, $allowedLoggedUsersTasks);

        $response = $response->setContent(json_encode($repeatingTaskArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
    }

    /**
     *  ### Response ###
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
     *  description="Returns Repeating Task Entity",
     *  requirements={
     *     {
     *       "name"="repeatingTaskId",
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
     *  output="API\TaskBundle\Entity\RepeatingTask",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $repeatingTaskId
     * @return Response
     * @throws \LogicException
     */
    public function getAction(int $repeatingTaskId): Response
    {
        $locationURL = $this->generateUrl('repeating_task', ['repeatingTaskId' => $repeatingTaskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $repeatingTask = $this->getDoctrine()->getRepository('APITaskBundle:RepeatingTask')->find($repeatingTaskId);
        if (!$repeatingTask instanceof RepeatingTask) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Repeating Task with requested Id does not exist!']));
            return $response;
        }

        // User can see a repeating task if he is ADMIN or repeating task is related to the task where he has a permission to view it
        if (!$this->checkViewPermission($repeatingTask)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $repeatingTaskArray = $this->get('repeating_task_service')->getRepeatingTask($repeatingTaskId);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($repeatingTaskArray));
        return $response;
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
     *           "put": "/api/v1/entityName/2",
     *           "patch": "/api/v1/entityName/2",
     *           "delete": "/api/v1/entityName/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Entity (POST)",
     *  input={"class"="API\CoreBundle\Entity\...entityName"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\...entityName"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        // TODO: Implement createAction() method.
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
     *           "put": "/api/v1/entityName/2",
     *           "patch": "/api/v1/entityName/2",
     *           "delete": "/api/v1/entityName/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the Entity (PUT)",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\CoreBundle\Entity\...entityName"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\...entityName"},
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
     * @param Request $request
     * @return Response
     */
    public function updateAction(int $repeatingTaskId, Request $request): Response
    {
        // TODO: Implement updateAction() method.
    }

    /**
     * @ApiDoc(
     *  description="Delete Entity (DELETE)",
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
     * @param int $repeatingTaskId
     *
     * @return Response
     */
    public function deleteAction(int $repeatingTaskId): Response
    {
        // TODO: Implement deleteAction() method.
    }

    /**
     * @param RepeatingTask $repeatingTask
     * @return bool
     * @throws \LogicException
     */
    private function checkViewPermission(RepeatingTask $repeatingTask): bool
    {
        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $options = [
            'allowedTasksId' => $this->get('task_service')->getUsersTasksId($loggedUser),
            'repeatingTasksTaskId' => $repeatingTask->getTask()->getId()
        ];
        if (!$this->get('repeating_task_voter')->isGranted(VoteOptions::VIEW_REPEATING_TASK, $options)) {
            return false;
        }
        return true;
    }
}
