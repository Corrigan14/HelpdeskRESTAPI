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
     *              "id": 4,
     *              "title": "User assigned you a Project",
     *              "body": null,
     *              "checked": false,
     *              "createdAt": 1510407878,
     *              "createdBy":
     *              {
     *                  "id": 521,
     *                  "username": "admin",
     *                  "email": "admin@admin.sk",
     *                  "name": "Admin",
     *                  "surname": "Adminovic"
     *              },
     *              "project":
     *              {
     *                  "id": 23,
     *                  "title": "Project of admin"
     *              },
     *              "task":
     *              {
     *                  "id": 23,
     *                  "title": "Project of admin"
     *              },
     *           }
     *        ],
     *        "not read": 10,
     *        "read": 0
     *     }
     *
     * @ApiDoc(
     *  description="Returns logged user's existed notification list.",
     *  filters={
     *     {
     *       "name"="read",
     *       "description"="Return's only NOT READ notifications if this param is FALSE, only READ notifications if param is TRUE. If null - return all."
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getLoggedUsersNotificationAction(Request $request): Response
    {
        $user = $this->getUser();

        $readString = $request->get('read');
        if ('true' === strtolower($readString) || true === $readString) {
            $read = true;
        } elseif ('false' === strtolower($readString) || false === $readString) {
            $read = false;
        } else {
            $read = null;
        }

        $options = [
            'loggedUserId' => $user->getId(),
            'read' => $read
        ];

        $notificationArray = $this->get('notifications_service')->getLoggedUserNotifications($options);
        return $this->json($notificationArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *     {
     *        "data":
     *        [
     *          {
     *
     *          }
     *        ],
     *        "_links": [],
     *        "total NOT READ": 10,
     *        "total": 15
     *     }
     *
     * @ApiDoc(
     *  description="Set notification as READ. Return updated notification.",
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
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $notificationId
     * @return JsonResponse
     */
    public function setAsReadNotificationAction(int $notificationId)
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
     * @param int $notificationId
     * @return Response|JsonResponse
     */
    public function deleteNotificationAction(int $notificationId)
    {

    }
}
