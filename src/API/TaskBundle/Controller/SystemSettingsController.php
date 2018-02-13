<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\SystemSettings;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class SystemSettingsController
 *
 * @package API\TaskBundle\Controller
 */
class SystemSettingsController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *      "data":
     *      [
     *         {
     *            "id": 3,
     *            "title": "Company Name",
     *            "value": "Lan Systems",
     *            "is_active": true
     *         },
     *         {
     *            "id": 4,
     *            "title": "Logo",
     *            "value": "Slug pre logo",
     *            "is_active": true
     *         }
     *      ],
     *      "_links":
     *      {
     *          "self": "/api/v1/task-bundle/status?page=1&isActive=true",
     *          "first": "/api/v1/task-bundle/status?page=1&isActive=true",
     *          "prev": false,
     *          "next": false,
     *          "last": "/api/v1/task-bundle/status?page=1&isActive=true"
     *       },
     *       "total": 2,
     *       "page": 1,
     *       "numberOfPages": 1
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of System settings",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
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
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('system_settings_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
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
            $isActive = $processedFilterParams['isActive'];

            $filtersForUrl = [
                'isActive' => '&isActive=' . $isActive,
            ];

            $options = [
                'isActive' => $isActive,
                'filtersForUrl' => $filtersForUrl,
                'limit' => $limit
            ];

            $systemSettingsArray = $this->get('system_settings_service')->getAttributesResponse($page, $options);
            $response = $response->setContent(json_encode($systemSettingsArray));
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
     *       "data":
     *       {
     *          "id": 3,
     *          "title": "Company Name",
     *          "value": "Lan Systems",
     *          "is_active": true
     *       },
     *       "_links":
     *       {
     *          "put": "/api/v1/task-bundle/system-settings/3",
     *          "delete": "/api/v1/task-bundle/system-settings/3",
     *          "restore": "/api/v1/task-bundle/system-settings/restore/3"
     *        }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a System setting Entity",
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
     *  output="API\TaskBundle\Entity\SystemSettings",
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
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('system_settings', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $systemSettingEntity = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->find($id);
        if (!$systemSettingEntity instanceof SystemSettings) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'System setting with requested Id does not exist!']));
            return $response;
        }

        $systemSettingArray = $this->get('system_settings_service')->getAttributeResponse($id);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($systemSettingArray));
        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *       "data":
     *       {
     *          "id": 3,
     *          "title": "Company Name",
     *          "value": "Lan Systems",
     *          "is_active": true
     *       },
     *       "_links":
     *       {
     *          "put": "/api/v1/task-bundle/system-settings/3",
     *          "delete": "/api/v1/task-bundle/system-settings/3",
     *          "restore": "/api/v1/task-bundle/system-settings/restore/3"
     *        }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new System settings Entity",
     *  input={"class"="API\TaskBundle\Entity\SystemSettings"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\SystemSettings"},
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
        $locationURL = $this->generateUrl('system_settings_create');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $systemSettings = new SystemSettings();
        $systemSettings->setIsActive(true);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateEntity($systemSettings, $requestBody, true, $locationURL);
    }

    /**
     *  ### Response ###
     *      {
     *       "data":
     *       {
     *          "id": 3,
     *          "title": "Company Name",
     *          "value": "Lan Systems",
     *          "is_active": true
     *       },
     *       "_links":
     *       {
     *          "put": "/api/v1/task-bundle/system-settings/3",
     *          "delete": "/api/v1/task-bundle/system-settings/3",
     *          "restore": "/api/v1/task-bundle/system-settings/restore/3"
     *        }
     *      }
     *
     * @ApiDoc(
     *  description="Update the System settings Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\SystemSettings"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\SystemSettings"},
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
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function updateAction(int $id, Request $request):Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('system_settings_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $systemSettingEntity = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->find($id);
        if (!$systemSettingEntity instanceof SystemSettings) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'System settings with requested Id does not exist!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateEntity($systemSettingEntity, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Delete System settings Entity",
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
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(int $id):Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('system_settings_inactivate', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $systemSettingEntity = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->find($id);
        if (!$systemSettingEntity instanceof SystemSettings) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'System setting with requested Id does not exist!']));
            return $response;
        }

        $systemSettingEntity->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($systemSettingEntity);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => 'is_active param of Entity was successfully changed to inactive: 0']));
        return $response;
    }

    /**
     * ### Response ###
     *     {
     *       "data":
     *       {
     *          "id": 3,
     *          "title": "Company Name",
     *          "value": "Lan Systems",
     *          "is_active": true
     *       },
     *       "_links":
     *       {
     *          "put": "/api/v1/task-bundle/system-settings/3",
     *          "delete": "/api/v1/task-bundle/system-settings/3",
     *          "restore": "/api/v1/task-bundle/system-settings/restore/3"
     *        }
     *      }
     *
     * @ApiDoc(
     *  description="Restore the System settings Entity",
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
     *  output={"class"="API\TaskBundle\Entity\SystemSettings"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $id
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(int $id):Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('system_settings_restore', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $systemSettingEntity = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->find($id);
        if (!$systemSettingEntity instanceof SystemSettings) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'System Setting with requested Id does not exist!']));
            return $response;
        }

        $systemSettingEntity->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($systemSettingEntity);
        $this->getDoctrine()->getManager()->flush();

        $systemSettingsArray = $this->get('system_settings_service')->getAttributeResponse($id);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($systemSettingsArray));
        return $response;
    }

    /**
     * @param SystemSettings $status
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
    private function updateEntity(SystemSettings $status, $requestData, $create = false, $locationUrl): Response
    {
        $allowedUnitEntityParams = [
            'title',
            'value'
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
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for System-Settings Entity!']));
                    return $response;
                }
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            $errors = $this->get('entity_processor')->processEntity($status, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($status);
                $this->getDoctrine()->getManager()->flush();

                $systemSettingsArray = $this->get('system_settings_service')->getAttributeResponse($status->getId());
                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($systemSettingsArray));
                return $response;
            }else {
                $data = [
                    'errors' => $errors,
                    'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
                ];
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode($data));
            }
        }else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }
        return $response;
    }
}
