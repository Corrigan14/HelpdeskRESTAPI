<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Services\VariableHelper;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskAttributeController
 *
 * @package API\TaskBundle\Controller
 */
class TaskAttributeController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 142,
     *             "title": "input task additional attribute",
     *             "type": "input",
     *             "options": null,
     *             "is_active": true
     *           },
     *           {
     *              "id": 143,
     *              "title": "select task additional attribute",
     *              "type": "simple_select",
     *              "options":
     *              {
     *                 "select1": "select1",
     *                 "select2": "select2",
     *                 "select3": "select3"
     *              },
     *              "is_active": true
     *           },
     *           {
     *              "id": 144,
     *              "title": "integer number task additional attribute",
     *              "type": "integer_number",
     *              "options": null,
     *              "is_active": true
     *            }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/task-attributes?page=1",
     *           "first": "/api/v1/task-bundle/task-attributes?page=1",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/task-attributes?page=2",
     *           "last": "/api/v1/task-bundle/task-attributes?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Entities",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE attributes if this param is TRUE, only INACTIVE attributes if param is FALSE"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Title"
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
     * @return JsonResponse|Response
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::TASK_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $pageNum = $request->get('page');
        $pageNum = (int)$pageNum;
        $page = ($pageNum === 0) ? 1 : $pageNum;

        $orderString = $request->get('order');
        $orderString = strtolower($orderString);
        $order = ($orderString === 'asc' || $orderString === 'desc') ? $orderString : 'ASC';

        $isActive = $request->get('isActive');
        $filtersForUrl = [];
        if (null !== $isActive) {
            $filtersForUrl['isActive'] = '&isActive=' . $isActive;
        }

        $options = [
            'loggedUserId' => $this->getUser()->getId(),
            'isActive' => strtolower($isActive),
            'order' => $order,
            'filtersForUrl' => array_merge($filtersForUrl, ['order' => '&order=' . $order])
        ];

        return $this->json($this->get('task_attribute_service')->getTaskAttributesResponse($page, $options), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": 142,
     *            "title": "input task additional attribute",
     *            "type": "input",
     *            "options":
     *             {
     *                "select1": "select1",
     *                "select2": "select2",
     *                "select3": "select3"
     *             },
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns the Task Attribute Entity",
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
     *  output="API\TaskBundle\Entity\TaskAttribute",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     * @return JsonResponse|Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::TASK_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $ta = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($id);

        if (!$ta instanceof TaskAttribute) {
            return $this->notFoundResponse();
        }

        $caArray = $this->get('task_attribute_service')->getTaskAttributeResponse($id);
        return $this->json($caArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options":
     *            {
     *               "select1": "select1",
     *               "select2": "select2",
     *               "select3": "select3"
     *            },
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Task Attribute Entity",
     *  input={"class"="API\TaskBundle\Entity\TaskAttribute"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\TaskAttribute"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::TASK_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();
        $taskAttribute = new TaskAttribute();
        $taskAttribute->setIsActive(true);

        return $this->updateEntity($requestData, $taskAttribute, true);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options":
     *            {
     *               "select1": "select1",
     *               "select2": "select2",
     *               "select3": "select3"
     *            },
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the Task Attribute Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\TaskAttribute"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\TaskAttribute"},
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
     * @return JsonResponse|Response
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updateAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::TASK_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($id);

        if (!$taskAttribute instanceof TaskAttribute) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();

        return $this->updateEntity($requestData, $taskAttribute);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options":
     *            {
     *               "select1": "select1",
     *               "select2": "select2",
     *               "select3": "select3"
     *            },
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the Task Attribute Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\TaskAttribute"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\TaskAttribute"},
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
     * @return JsonResponse|Response
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::TASK_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($id);

        if (!$taskAttribute instanceof TaskAttribute) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();

        return $this->updateEntity($requestData, $taskAttribute);
    }

    /**
     * @ApiDoc(
     *  description="Delete the Task Attribute Entity",
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
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function deleteAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::TASK_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($id);

        if (!$taskAttribute instanceof TaskAttribute) {
            return $this->notFoundResponse();
        }

        $taskAttribute->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($taskAttribute);
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
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options":
     *            {
     *               "select1": "select1",
     *               "select2": "select2",
     *               "select3": "select3"
     *            },
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Restore the Task Attribute Entity",
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
     *  output={"class"="API\TaskBundle\Entity\TaskAttribute"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Task Attribute Entity",
     *  }
     * )
     *
     * @param int $id
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function restoreAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::TASK_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($id);

        if (!$taskAttribute instanceof TaskAttribute) {
            return $this->notFoundResponse();
        }

        $taskAttribute->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($taskAttribute);
        $this->getDoctrine()->getManager()->flush();

        $caArray = $this->get('task_attribute_service')->getTaskAttributeResponse($taskAttribute->getId());
        return $this->json($caArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param array $requestData
     * @param TaskAttribute $taskAttribute
     * @param bool $create
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse|Response
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    private function updateEntity(array $requestData, TaskAttribute $taskAttribute, $create = false)
    {
        $allowedUnitEntityParams = [
            'title',
            'type',
            'options',
            'is_active',
            'description'
        ];

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!in_array($key, $allowedUnitEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for Task Attribute Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        // Check if selected Type is allowed
        if (array_key_exists('type', $requestData)) {
            $type = $requestData['type'];
            $allowedTypes = VariableHelper::getConstants();

            if (!in_array($type, $allowedTypes, true)) {
                return $this->createApiResponse([
                    'message' => $type . ' Type is not allowed!',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }

        $errors = $this->get('entity_processor')->processEntity($taskAttribute, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($taskAttribute);
            $this->getDoctrine()->getManager()->flush();

            $caArray = $this->get('task_attribute_service')->getTaskAttributeResponse($taskAttribute->getId());
            return $this->json($caArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
