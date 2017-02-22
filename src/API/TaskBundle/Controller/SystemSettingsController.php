<?php

namespace API\TaskBundle\Controller;

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

        $page = $request->get('page') ?: 1;
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
     *        "data":
     *        {
     *           "id": "2",
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
        // TODO: Implement getAction() method.
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
        // TODO: Implement updateAction() method.
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
        // TODO: Implement updatePartialAction() method.
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
        // TODO: Implement deleteAction() method.
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
     *  description="Restore the System settings Entity",
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
    public function restoreAction(int $id, Request $request)
    {
        // TODO: Implement updatePartialAction() method.
    }
}
