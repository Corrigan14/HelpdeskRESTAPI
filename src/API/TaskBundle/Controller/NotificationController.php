<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\Notification;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
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
     *              },
     *              'type': 'COMMENT',
     *              'internal': false
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
     *              },
     *              'type': 'COMMENT',
     *              'internal': false
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
     *              },
     *              'type': 'COMMENT',
     *              'internal': false
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
     *              },
     *              'type': 'COMMENT',
     *              'internal': false
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
     * @param string $read
     * @param Request $request
     * @return JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function setAsReadNotificationAction(string $read, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('notification_set_as_read', ['read' => $read]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $read = strtolower($read);
        if ('true' !== $read && 'false' !== $read) {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with read param format! Just TRUE = 1, FALSE = 0 are allowed!']));
            return $response;
        }

        if ('true' === $read) {
            $readParam = true;
        } else {
            $readParam = false;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        if (false === $requestBody) {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
            return $response;
        }

        if (!isset($requestBody['notifications'])) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Please fill notification IDs']));
            return $response;
        }

        $notifications = [];
        $decodedIds = json_decode($requestBody['notifications']);
        if (!\is_array($decodedIds)) {
            $decodedIds = explode(',', $requestBody['notifications']);
        }

        if (!\is_array($decodedIds)) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Please pass the correct format of ids! Expected format: JSON array']));
            return $response;
        }

        $this->getDoctrine()->getManager()->getConnection()->beginTransaction();
        try {
            foreach ($decodedIds as $id) {
                $notification = $this->getDoctrine()->getRepository('APITaskBundle:Notification')->find($id);
                if ($notification instanceof Notification) {
                    //User can change only his/her own notifications
                    if ($notification->getUser() !== $this->getUser()) {
                        $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                        $response = $response->setContent(json_encode(['message' => 'You are not allowed to change Notification with ID ' . $notification->getId()]));
                        return $response;
                    }

                    $notification->setChecked($readParam);
                    $this->getDoctrine()->getManager()->persist($notification);

                    // Updated Notification array
                    $notifications[] = $this->processNotificationToArray($notification);
                } else {
                    $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Notification with ID ' . $id . ' does not exist!']));
                    return $response;
                }
            }
            $this->getDoctrine()->getManager()->flush();
            $this->getDoctrine()->getManager()->getConnection()->commit();

            $response = $response->setContent(json_encode($notifications));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            return $response;
        } catch (\Exception $exception) {
            $this->getDoctrine()->getManager()->getConnection()->rollBack();
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with Notification IDs: ' . $exception]));
            return $response;
        }
    }

    /**
     * @ApiDoc(
     *  description="Delete Notifications.",
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
     *      204 ="Notification was successfully deleted.",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param Request $request
     * @return Response|JsonResponse
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function deleteNotificationAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('notification_delete');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        if (false === $requestBody) {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
            return $response;
        }

        if (!isset($requestBody['notifications'])) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Please fill notification IDs']));
            return $response;
        }

        $notifications = [];
        $decodedIds = json_decode($requestBody['notifications']);
        if (!\is_array($decodedIds)) {
            $decodedIds = explode(',', $requestBody['notifications']);
        }

        if (!\is_array($decodedIds)) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Please pass the correct format of ids! Expected format: JSON array']));
            return $response;
        }

        $this->getDoctrine()->getManager()->getConnection()->beginTransaction();
        try {
            foreach ($decodedIds as $id) {
                $notification = $this->getDoctrine()->getRepository('APITaskBundle:Notification')->find($id);
                if ($notification instanceof Notification) {
                    //User can change only his/her own notifications
                    if ($notification->getUser() !== $this->getUser()) {
                        $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                        $response = $response->setContent(json_encode(['message' => 'You are not allowed to change Notification with ID ' . $notification->getId()]));
                        return $response;
                    }

                    $this->getDoctrine()->getManager()->remove($notification);
                } else {
                    $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Notification with ID ' . $id . ' does not exist!']));
                    return $response;
                }
            }
            $this->getDoctrine()->getManager()->flush();
            $this->getDoctrine()->getManager()->getConnection()->commit();

            $response = $response->setContent(json_encode($notifications));
            $response = $response->setStatusCode(StatusCodesHelper::DELETED_CODE);
            return $response;
        } catch (\Exception $exception) {
            $this->getDoctrine()->getManager()->getConnection()->rollBack();
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with Notification IDs: ' . $exception]));
            return $response;
        }
    }


    /**
     * @param Notification $notification
     * @return array
     */
    private function processNotificationToArray(Notification $notification): array
    {
        $userCreatorDetailData = $notification->getCreatedBy()->getDetailData();
        $userCreatorName = null;
        $userCreatorSurname = null;
        if ($userCreatorDetailData) {
            $userCreatorName = $userCreatorDetailData->getName();
            $userCreatorSurname = $userCreatorDetailData->getSurname();
        }

        $project = $notification->getProject();
        $projectArray = null;
        if ($project instanceof Project) {
            $projectArray = [
                'id' => $project->getId(),
                'title' => $project->getTitle()
            ];
        }

        $task = $notification->getTask();
        $taskArray = null;
        if ($task instanceof Task) {
            $taskArray = [
                'id' => $task->getId(),
                'title' => $task->getTitle()
            ];
        }

        $response = [
            'id' => $notification->getId(),
            'title' => $notification->getTitle(),
            'body' => $notification->getBody(),
            'read' => $notification->getChecked(),
            'createdAt' => $notification->getCreatedAt(),
            'createdBy' => [
                'id' => $notification->getCreatedBy()->getId(),
                'username' => $notification->getCreatedBy()->getUsername(),
                'email' => $notification->getCreatedBy()->getEmail(),
                'name' => $userCreatorName,
                'surname' => $userCreatorSurname,
            ],
            'project' => $projectArray,
            'task' => $taskArray
        ];

        return $response;

    }
}
