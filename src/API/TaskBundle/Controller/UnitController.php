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
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE units if this param is TRUE, only INACTIVE units if param is FALSE"
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
            'acl' => UserRoleAclOptions::UNIT_SETTINGS,
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
            'loggedUserId' => $this->getUser()->getId(),
            'isActive' => strtolower($isActive),
            'filtersForUrl' => $filtersForUrl
        ];

        return $this->json($this->get('unit_service')->getAttributesResponse($page, $options), StatusCodesHelper::SUCCESSFUL_CODE);
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
     */
    public function getAction(int $id)
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

        $unitArray = $this->get('unit_service')->getAttributeResponse($id);
        return $this->json($unitArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     */
    public function createAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::UNIT_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $unit = new Unit();
        $unit->setIsActive(true);

        return $this->updateUnit($unit, $requestData, true);
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
     */
    public function updateAction(int $id, Request $request)
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
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    private function updateUnit(Unit $unit, $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($unit, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($unit);
            $this->getDoctrine()->getManager()->flush();

            $unitArray = $this->get('unit_service')->getAttributeResponse($unit->getId());
            return $this->json($unitArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
