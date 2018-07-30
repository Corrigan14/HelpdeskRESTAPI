<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class ListController extends ApiBaseController
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
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        $locationURL = $this->generateUrl('repeating_task_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        if (false === $requestBody) {
            $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));

            return $response;
        }

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $isAdmin = $this->get('api_base.voter')->isAdmin();
        $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody, false, $loggedUser, $isAdmin);

        $allowedLoggedUsersTasks = $this->get('task_service')->getUsersViewTasksId($loggedUser);
        $repeatingTaskArray = $this->get('repeating_task_get_service')->getRepeatingTasks($processedFilterParams, $allowedLoggedUsersTasks);

        $response->setContent(json_encode($repeatingTaskArray))
            ->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);

        return $response;
    }
}
