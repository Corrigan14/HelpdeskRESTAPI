<?php

namespace API\TaskBundle\Controller;

use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NotificationController
 * @package API\TaskBundle\Controller
 */
class NotificationController extends ApiBaseController
{
    /**
     *  ### Response ###
     *     {
     *        "data":
     *        [
     *           {
     *              "id": 3,
     *              "title": "User updated task",
     *              "body": "following parameters were changed: title (FROM .. TO) + link na task",
     *              "read": true,
     *              "createdAt": 1524504678,
     *              "createdBy":
     *              {
     *                  "id": 5,
     *                  "username": "user",
     *                  "email": "user@user.sk",
     *                  "name": null,
     *                  "surname": null
     *              },
     *              "project": null,
     *              "task":
     *              {
     *                  "id": 1,
     *                  "title": "test 2"
     *              }
     *          },
     *          {
     *              "id": 22,
     *              "title": "Task UPDATE",
     *              "body":
     *              {
     *                  "project":
     *                  {
     *                      "from": "Project of admin 3 - inactive",
     *                      "to": "Project of user 2"
     *                  },
     *                  "status":
     *                  {
     *                      "from": "In Progress",
     *                      "to": "Completed"
     *                  }
     *              },
     *              "read": false,
     *              "createdAt": 1525171078,
     *              "createdBy":
     *              {
     *                  "id": 1,
     *                  "username": "admin",
     *                  "email": "admin@admin.sk",
     *                  "name": "Admin",
     *                  "surname": "Adminovic"
     *              },
     *              "project": null,
     *              "task":
     *              {
     *                  "id": 1,
     *                  "title": "test 2"
     *              }
     *           }
     *        ]
     *        "_counts":
     *        {
     *            "not read": 9,
     *            "read": 2,
     *            "all": 11
     *        },
     *        "_links":
     *        {
     *            "self": "/api/v1/task-bundle/notification?page=1&limit=3&read=1&order=ASC",
     *            "first": "/api/v1/task-bundle/notification?page=1&limit=3&read=1&order=ASC",
     *            "prev": false,
     *            "next": false,
     *            "last": "/api/v1/task-bundle/notification?page=1&limit=3&read=1&order=ASC"
     *        },
     *       "total": 2,
     *       "page": 1,
     *       "numberOfPages": 1
     *     }
     *
     * @ApiDoc(
     *  description="Returns logged user's notifications.",
     *  filters={
     *     {
     *       "name"="read",
     *       "description"="Return's only NOT READ notifications if this param is FALSE, only READ notifications if param is TRUE. If null - return all."
     *     },
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Order"
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Limit for Pagination: 999 - returns all entities, null - returns 10 entities"
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
     *      401 ="Unauthorized request"
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getLoggedUsersNotificationAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('users_notifications');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];
            $read = $processedFilterParams['read'];

            $filtersForUrl = [
                'read' => '&read=' . $read,
                'order' => '&order=' . $order,
            ];

            $options = [
                'read' => $read,
                'order' => $order,
                'filtersForUrl' => $filtersForUrl,
                'limit' => $limit,
                'loggedUserId' => $this->getUser()->getId()
            ];

            $notificationArray = $this->get('notifications_service')->getLoggedUserNotifications($page, $options);
            $response = $response->setContent(json_encode($notificationArray));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }

        return $response;
    }

    /**
     *  ### Response ###
     *     {
     *        "data":
     *        [
     *           {
     *              "id": 3,
     *              "title": "User updated task",
     *              "body": "following parameters were changed: title (FROM .. TO) + link na task",
     *              "read": true,
     *              "createdAt": 1524504678,
     *              "createdBy":
     *              {
     *                  "id": 5,
     *                  "username": "user",
     *                  "email": "user@user.sk",
     *                  "name": null,
     *                  "surname": null
     *              },
     *              "project": null,
     *              "task":
     *              {
     *                  "id": 1,
     *                  "title": "test 2"
     *              }
     *          },
     *          {
     *              "id": 22,
     *              "title": "Task UPDATE",
     *              "body":
     *              {
     *                  "project":
     *                  {
     *                      "from": "Project of admin 3 - inactive",
     *                      "to": "Project of user 2"
     *                  },
     *                  "status":
     *                  {
     *                      "from": "In Progress",
     *                      "to": "Completed"
     *                  }
     *              },
     *              "read": false,
     *              "createdAt": 1525171078,
     *              "createdBy":
     *              {
     *                  "id": 1,
     *                  "username": "admin",
     *                  "email": "admin@admin.sk",
     *                  "name": "Admin",
     *                  "surname": "Adminovic"
     *              },
     *              "project": null,
     *              "task":
     *              {
     *                  "id": 1,
     *                  "title": "test 2"
     *              }
     *           }
     *        ],
     *        "_counts":
     *        {
     *            "not read": 9,
     *            "read": 2,
     *            "all": 11
     *        }
     *     }
     *
     * @ApiDoc(
     *  description="Set notification(s) as READ. Return updated notification(s).",
     *  parameters={
     *      {"name"="notifications", "dataType"="string", "required"=true,  "description"="Coma separated list of notifications' IDs"},
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
     * @param bool $read
     * @return JsonResponse
     */
    public function setAsReadNotificationAction(bool $read):Response
    {

    }

    /**
     * @ApiDoc(
     *  description="Delete Notification Entity",
     *  requirements={
     *     {
     *       "name"="notificationId",
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
     *      201 ="Notification was successfully deleted.",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @return Response|JsonResponse
     */
    public function deleteNotificationAction():Response
    {

    }
}
