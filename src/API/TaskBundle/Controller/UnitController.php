<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\Unit;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class UnitController
 *
 * @package API\TaskBundle\Controller
 */
class UnitController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response###
     *     {
     *        "data":
     *        [
     *          {
     *             "id": 7,
     *             "title": "Kilogram",
     *             "shortcut": "Kg",
     *             "is_active": true
     *          },
     *          {
     *             "id": 8,
     *             "title": "Centimeter",
     *             "shortcut": "cm",
     *             "is_active": true
     *          },
     *          {
     *             "id": 9,
     *             "title": "Meter",
     *             "shortcut": "m",
     *             "is_active": true
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/units?page=1",
     *           "first": "/api/v1/task-bundle/units?page=1",
     *           "prev": false,
     *           "next": false,
     *           "last": "/api/v1/task-bundle/units?page=1"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Unit Entities",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Title"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE units if this param is TRUE, only INACTIVE units if param is FALSE"
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
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('unit_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::UNIT_SETTINGS,
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
            $unitArray = $this->get('unit_service')->getAttributesResponse($page, $options);
            $response = $response->setContent(json_encode($unitArray));
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
     *           "id": 7,
     *           "title": "Kilogram",
     *           "shortcut": "Kg",
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/units/7",
     *           "patch": "/api/v1/task-bundle/units/7",
     *           "delete": "/api/v1/task-bundle/units/7",
     *           "restore": "/api/v1/task-bundle/units/restore/7"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Unit Entity",
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
     *  output="API\TaskBundle\Entity\Unit",
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
        $locationURL = $this->generateUrl('unit', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::UNIT_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($id);

        if (!$unit instanceof Unit) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::NOT_FOUND_MESSAGE]));
            return $response;
        }

        $unitArray = $this->get('unit_service')->getAttributeResponse($id);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($unitArray));
        return $response;

    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 7,
     *           "title": "Kilogram",
     *           "shortcut": "Kg",
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/units/7",
     *           "patch": "/api/v1/task-bundle/units/7",
     *           "delete": "/api/v1/task-bundle/units/7",
     *           "restore": "/api/v1/task-bundle/units/restore/7"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Unit Entity",
     *  input={"class"="API\TaskBundle\Entity\Unit"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Unit"},
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
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('unit_create');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::UNIT_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        $unit = new Unit();
        $unit->setIsActive(true);

        return $this->updateUnit($unit, $requestBody, true, $locationURL);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 7,
     *           "title": "Kilogram",
     *           "shortcut": "Kg",
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/units/7",
     *           "patch": "/api/v1/task-bundle/units/7",
     *           "delete": "/api/v1/task-bundle/units/7",
     *           "restore": "/api/v1/task-bundle/units/restore/7"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the Unit Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Unit"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Unit"},
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
     * @return Response
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updateAction(int $id, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('unit_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::UNIT_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($id);
        if (!$unit instanceof Unit) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Unit with requested Id does not exist!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateUnit($unit, $requestBody, false, $locationURL);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 7,
     *           "title": "Kilogram",
     *           "shortcut": "Kg",
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/units/7",
     *           "patch": "/api/v1/task-bundle/units/7",
     *           "delete": "/api/v1/task-bundle/units/7",
     *           "restore": "/api/v1/task-bundle/units/restore/7"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the Unit Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Unit"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Unit"},
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
     * @return Response
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::UNIT_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($id);

        if (!$unit instanceof Unit) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();
        return $this->updateUnit($unit, $requestData, false);
    }

    /**
     * @ApiDoc(
     *  description="Delete Unit Entity",
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
     * @return Response
     */
    public function deleteAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::UNIT_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($id);

        if (!$unit instanceof Unit) {
            return $this->notFoundResponse();
        }

        $unit->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($unit);
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
     *           "id": 7,
     *           "title": "Kilogram",
     *           "shortcut": "Kg",
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/units/7",
     *           "patch": "/api/v1/task-bundle/units/7",
     *           "delete": "/api/v1/task-bundle/units/7",
     *           "restore": "/api/v1/task-bundle/units/restore/7"
     *         }
     *      }
     *
     *
     * @ApiDoc(
     *  description="Restore Unit Entity",
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
     *      200 ="is_active param of Entity was successfully changed to active: 1",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return Response
     */
    public function restoreAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::UNIT_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($id);

        if (!$unit instanceof Unit) {
            return $this->notFoundResponse();
        }

        $unit->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($unit);
        $this->getDoctrine()->getManager()->flush();

        $unitArray = $this->get('unit_service')->getAttributeResponse($unit->getId());
        return $this->json($unitArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param Unit $unit
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
    private function updateUnit(Unit $unit, $requestData, $create = false, $locationUrl): Response
    {
        $allowedUnitEntityParams = [
            'title',
            'shortcut',
            'is_active'
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
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for User Entity!']));
                    return $response;
                }
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            $errors = $this->get('entity_processor')->processEntity($unit, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($unit);
                $this->getDoctrine()->getManager()->flush();

                $unitArray = $this->get('unit_service')->getAttributeResponse($unit->getId());

                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($unitArray));
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
