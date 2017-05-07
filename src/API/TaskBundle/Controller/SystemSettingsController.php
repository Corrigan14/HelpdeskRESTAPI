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
     */
    public function listAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $pageNum = $request->get('page');
        $pageNum = (int)$pageNum;
        $page = ($pageNum === 0) ? 1 : $pageNum;

        $isActive = $request->get('isActive');
        $filtersForUrl = [];
        if (null !== $isActive) {
            $filtersForUrl['isActive'] = '&isActive=' . $isActive;
        }

        $options = [
            'isActive' => strtolower($isActive),
            'filtersForUrl' => $filtersForUrl
        ];

        $systemSettingsArray = $this->get('system_settings_service')->getAttributesResponse($page, $options);
        return $this->json($systemSettingsArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *          "patch": "/api/v1/task-bundle/system-settings/3",
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
     */
    public function getAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $systemSettingEntity = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->find($id);
        if (!$systemSettingEntity instanceof SystemSettings) {
            return $this->createApiResponse([
                'message' => 'System setting with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $systemSettingArray = $this->get('system_settings_service')->getAttributeResponse($id);
        return $this->json($systemSettingArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *          "patch": "/api/v1/task-bundle/system-settings/3",
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
     */
    public function createAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $systemSettings = new SystemSettings();
        $systemSettings->setIsActive(true);

        return $this->updateEntity($systemSettings, $requestData, true);
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
     *          "patch": "/api/v1/task-bundle/system-settings/3",
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
     */
    public function updateAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $systemSettingEntity = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->find($id);
        if (!$systemSettingEntity instanceof SystemSettings) {
            return $this->createApiResponse([
                'message' => 'System setting with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $requestData = $request->request->all();

        return $this->updateEntity($systemSettingEntity, $requestData, false);
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
     *          "patch": "/api/v1/task-bundle/system-settings/3",
     *          "delete": "/api/v1/task-bundle/system-settings/3",
     *          "restore": "/api/v1/task-bundle/system-settings/restore/3"
     *        }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the System settings Entity",
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
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $systemSettingEntity = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->find($id);
        if (!$systemSettingEntity instanceof SystemSettings) {
            return $this->createApiResponse([
                'message' => 'System setting with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $requestData = json_decode($request->getContent(), true);

        return $this->updateEntity($systemSettingEntity, $requestData, false);
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
     */
    public function deleteAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $systemSettingEntity = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->find($id);
        if (!$systemSettingEntity instanceof SystemSettings) {
            return $this->createApiResponse([
                'message' => 'System setting with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $systemSettingEntity->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($systemSettingEntity);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::UNACITVATE_MESSAGE,
        ], StatusCodesHelper::SUCCESSFUL_CODE);
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
     *          "patch": "/api/v1/task-bundle/system-settings/3",
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
     */
    public function restoreAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SYSTEM_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $systemSettingEntity = $this->getDoctrine()->getRepository('APITaskBundle:SystemSettings')->find($id);
        if (!$systemSettingEntity instanceof SystemSettings) {
            return $this->createApiResponse([
                'message' => 'System setting with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $systemSettingEntity->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($systemSettingEntity);
        $this->getDoctrine()->getManager()->flush();

        $systemSettingsArray = $this->get('system_settings_service')->getAttributeResponse($id);
        return $this->json($systemSettingsArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param SystemSettings $status
     * @param array $requestData
     * @param bool $create
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    private function updateEntity(SystemSettings $status, $requestData, $create = false)
    {
        $allowedUnitEntityParams = [
            'title',
            'value',
            'is_active'
        ];

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!in_array($key, $allowedUnitEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for System Setting Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($status, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($status);
            $this->getDoctrine()->getManager()->flush();

            $systemSettingsArray = $this->get('system_settings_service')->getAttributeResponse($status->getId());
            return $this->json($systemSettingsArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
