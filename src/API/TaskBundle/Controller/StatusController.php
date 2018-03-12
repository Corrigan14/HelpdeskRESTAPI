<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\Status;
use API\TaskBundle\Security\StatusFunctionOptions;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StatusController
 *
 * @package API\TaskBundle\Controller
 */
class StatusController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *            "id": "1",
     *            "title": "New",
     *            "description": "New task",
     *            "color": "#1E90FF",
     *            "is_active": true,
     *            "default": true,
     *            "function": "new_task"
     *          },
     *          {
     *            "id": 6,
     *            "title": "In Progress",
     *            "description": "In progress task",
     *            "color": "#32CD32",
     *            "is_active": true,
     *            "default": true,
     *            "function": "inprogress_task"
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/status?page=1",
     *           "first": "/api/v1/task-bundle/status?page=1",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/status?page=2",
     *           "last": "/api/v1/task-bundle/status?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Status Entities",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Order"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE statuses if this param is TRUE, only INACTIVE statuses if param is FALSE"
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
     *      401 ="Unauthorized request",
     *      403 ="Access denied"
     *  }
     * )
     *
     * @param Request $request
     * @return Response|JsonResponse
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('status_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];
            $isActive = $processedFilterParams['isActive'];

            $filtersForUrl = [
                'isActive' => '&isActive=' . $isActive,
                'order' => '&order=' . $order,
            ];

            $options = [
                'loggedUserId' => $this->getUser()->getId(),
                'isActive' => $isActive,
                'order' => $order,
                'filtersForUrl' => $filtersForUrl,
                'limit' => $limit
            ];

            $statusArray = $this->get('status_service')->getAttributesResponse($page, $options);
            $response = $response->setContent(json_encode($statusArray));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }

        return $response;
    }

    /**
     * ### Response ###
     *     {
     *       "data":
     *       [
     *           {
     *            "id": "1",
     *            "title": "New",
     *            "description": "New task",
     *            "color": "#1E90FF",
     *            "is_active": true,
     *            "default": true,
     *            "function": "new_task"
     *          },
     *          {
     *            "id": 6,
     *            "title": "In Progress",
     *            "description": "In progress task",
     *            "color": "#32CD32",
     *            "is_active": true,
     *            "default": true,
     *            "function": "inprogress_task"
     *          }
     *       ]
     *       "date": 1518907522
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of All active Statuses",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *  },
     * )
     *
     * @param string|bool $date
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function listOfAllStatusesAction($date = false): Response
    {
        // JSON API Response - Content type and Location settings
        if (false !== $date && 'false' !== $date) {
            $intDate = (int)$date;
            if (is_int($intDate) && null !== $intDate) {
                $locationURL = $this->generateUrl('status_list_of_all_active_from_date', ['date' => $date]);
                $dateTimeObject = new \DateTime("@$date");
            } else {
                $locationURL = $this->generateUrl('status_list_of_all_active');
                $dateTimeObject = false;
            }
        } else {
            $locationURL = $this->generateUrl('status_list_of_all_active');
            $dateTimeObject = false;
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        if ($date && !($dateTimeObject instanceof \Datetime)) {
            $response = $response->setContent(['message' => 'Date parameter is not in a valid format! Expected format: Timestamp']);
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            return $response;
        }

        $allStatuses = $this->get('status_service')->getLAllStatusesForASelectionList($dateTimeObject);
        $currentDate = new \DateTime('UTC');
        $currentDateTimezone = $currentDate->getTimestamp();

        $dataArray = [
            'data' => $allStatuses,
            'date' => $currentDateTimezone
        ];

        $response = $response->setContent(json_encode($dataArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "New",
     *            "description": "New task",
     *            "color": "#1E90FF",
     *            "is_active": true,
     *            "default": true,
     *            "function": "new_task"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/status/id",
     *           "delete": "/api/v1/task-bundle/status/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Status Entity",
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
     *  output="API\TaskBundle\Entity\Status",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('status', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Status with requested Id does not exist!']));
            return $response;
        }

        $statusArray = $this->get('status_service')->getAttributeResponse($id);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($statusArray));
        return $response;
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "New",
     *            "description": "New task",
     *            "color": "#1E90FF",
     *            "is_active": true,
     *            "default": true,
     *            "function": "new_task"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/status/id",
     *           "delete": "/api/v1/task-bundle/status/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Status Entity",
     *  input={"class"="API\TaskBundle\Entity\Status"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Status"},
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
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('status_create');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $status = new Status();
        $status->setIsActive(true);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateStatus($status, $requestBody, true, $locationURL);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "New",
     *            "description": "New task",
     *            "color": "#1E90FF",
     *            "is_active": true,
     *            "default": true,
     *            "function": "new_task"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/status/id",
     *           "delete": "/api/v1/task-bundle/status/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the Status Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Status"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Status"},
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
     * @return Response|JsonResponse
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('status_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);
        if (!$status instanceof Status) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Status with requested Id does not exist!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateStatus($status, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Delete Status Entity",
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
     *      200 ="is_active param of Entity was successfully changed to inactive: 0",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function deleteAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('status_inactivate', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Status with requested Id does not exist!']));
            return $response;
        }

        $status->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($status);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => 'is_active param of Entity was successfully changed to inactive: 0']));
        return $response;
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "New",
     *            "description": "New task",
     *            "color": "#1E90FF",
     *            "is_active": true,
     *            "default": true,
     *            "function": "new_task"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/status/id",
     *           "delete": "/api/v1/task-bundle/status/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Restore Status Entity: set is_active param to 1",
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
     *  output={"class"="API\TaskBundle\Entity\Status"},
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
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function restoreAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('user_role_inactivate', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Status with requested Id does not exist!']));
            return $response;
        }

        $status->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($status);
        $this->getDoctrine()->getManager()->flush();

        $statusArray = $this->get('status_service')->getAttributeResponse($status->getId());
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($statusArray));
        return $response;
    }

    /**
     * @param Status $status
     * @param array $requestData
     * @param bool $create
     * @param string $locationUrl
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    private function updateStatus(Status $status, $requestData, $create = false, $locationUrl): Response
    {
        $allowedUnitEntityParams = [
            'title',
            'color',
            'description',
            'order',
            'function'
        ];

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);

        if (false !== $requestData) {
            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
            }

            foreach ($requestData as $key => $value) {
                if (!\in_array($key, $allowedUnitEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for Status Entity!']));
                    return $response;
                }
            }

            // Control, if function is allowed
            $statusConstants = StatusFunctionOptions::getConstants();
            if (isset($requestData['function']) && 'null' !== strtolower($requestData['function']) && !\in_array($requestData['function'], $statusConstants, true)) {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => $requestData['function'] . ' is not allowed parameter for Function of Status Entity! Allowed are: ' .implode(',',$statusConstants)]));
                return $response;
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            $errors = $this->get('entity_processor')->processEntity($status, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($status);
                $this->getDoctrine()->getManager()->flush();

                $statusArray = $this->get('status_service')->getAttributeResponse($status->getId());
                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($statusArray));
                return $response;
            } else {
                $data = [
                    'errors' => $errors,
                    'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
                ];
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode($data));
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }
        return $response;
    }
}
