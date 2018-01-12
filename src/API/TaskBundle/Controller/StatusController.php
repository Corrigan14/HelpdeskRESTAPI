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
     *           "patch": "/api/v1/task-bundle/status/id",
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
    public function getAction(int $id):Response
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
     *           "patch": "/api/v1/task-bundle/status/id",
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
    public function createAction(Request $request):Response
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
     *           "patch": "/api/v1/task-bundle/status/id",
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();

        return $this->updateStatus($status, $requestData);
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
     *           "patch": "/api/v1/task-bundle/status/id",
     *           "delete": "/api/v1/task-bundle/status/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the Status Entity",
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();

        return $this->updateStatus($status, $requestData);
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
     * @throws \LogicException
     */
    public function deleteAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            return $this->notFoundResponse();
        }

        $status->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($status);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::UNACITVATE_MESSAGE,
        ], StatusCodesHelper::SUCCESSFUL_CODE);
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
     *           "patch": "/api/v1/task-bundle/status/id",
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
     * @return Response|JsonResponse
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function restoreAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::STATUS_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            return $this->notFoundResponse();
        }

        $status->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($status);
        $this->getDoctrine()->getManager()->flush();

        $statusArray = $this->get('status_service')->getAttributeResponse($status->getId());
        return $this->json($statusArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
    private function updateStatus(Status $status, $requestData, $create = false, $locationUrl):Response
    {
        $allowedUnitEntityParams = [
            'title',
            'color',
            'description',
            'is_active',
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

            // Controll, if function is allowed
            if (isset($requestData['function']) && !in_array($requestData['function'], StatusFunctionOptions::getConstants(), true)) {
                return $this->createApiResponse(
                    ['message' => $requestData['function'] . ' is not allowed parameter for Function of Status Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            $errors = $this->get('entity_processor')->processEntity($status, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($status);
                $this->getDoctrine()->getManager()->flush();

                $statusArray = $this->get('status_service')->getAttributeResponse($status->getId());
                return $this->json($statusArray, $statusCode);
            }

            $data = [
                'errors' => $errors,
                'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
            ];
            return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
        }else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }
        return $response;
    }
}
