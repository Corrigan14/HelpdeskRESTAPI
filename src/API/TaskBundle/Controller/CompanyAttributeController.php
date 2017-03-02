<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\CompanyAttribute;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Services\VariableHelper;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CompanyAttributeController
 *
 * @package API\TaskBundle\Controller
 */
class CompanyAttributeController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *            "id": "1",
     *            "title": "Input company additional attribute",
     *            "type": "input",
     *            "options": null,
     *            "is_active": true
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/company-attributes?page=1",
     *           "first": "/api/v1/task-bundle/company-attributes?page=1",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/company-attributes?page=2",
     *           "last": "/api/v1/task-bundle/company-attributes?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Company attributes Entities",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE company attributes if this param is TRUE, only INACTIVE company attributes if param is FALSE"
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
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

        return $this->json($this->get('company_attribute_service')->getAttributesResponse($page, $options), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Input company additional attribute",
     *           "type": "input"
     *           "options": null
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-attributes/id",
     *           "patch": "/api/v1/task-bundle/company-attributes/id",
     *           "delete": "/api/v1/task-bundle/company-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Company attribute Entity",
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
     *  output="API\TaskBundle\Entity\CompanyAttribute",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     * @return Response|JsonResponse
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            return $this->notFoundResponse();
        }

        $companyAttributeArray = $this->get('company_attribute_service')->getAttributeResponse($id);
        return $this->json($companyAttributeArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Input company additional attribute",
     *           "type": "input"
     *           "options": null
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-attributes/id",
     *           "patch": "/api/v1/task-bundle/company-attributes/id",
     *           "delete": "/api/v1/task-bundle/company-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Entity (POST)",
     *  input={"class"="API\TaskBundle\Entity\CompanyAttribute"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\CompanyAttribute"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return Response|JsonResponse
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $companyAttribute = new CompanyAttribute();

        $requestData = $request->request->all();

        return $this->updateCompanyAttribute($companyAttribute, $requestData, true);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Input company additional attribute",
     *           "type": "input"
     *           "options": null
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-attributes/id",
     *           "patch": "/api/v1/task-bundle/company-attributes/id",
     *           "delete": "/api/v1/task-bundle/company-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the Entity (PUT)",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\CompanyAttribute"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\CompanyAttribute"},
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
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();

        return $this->updateCompanyAttribute($companyAttribute, $requestData);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Input company additional attribute",
     *           "type": "input"
     *           "options": null
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-attributes/id",
     *           "patch": "/api/v1/task-bundle/company-attributes/id",
     *           "delete": "/api/v1/task-bundle/company-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the Entity (PATCH)",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\CompanyAttribute"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\CompanyAttribute"},
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
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();

        return $this->updateCompanyAttribute($companyAttribute, $requestData);
    }

    /**
     * @ApiDoc(
     *  description="Delete Company attribute Entity",
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
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            return $this->notFoundResponse();
        }

        $companyAttribute->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($companyAttribute);
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
     *           "id": "2",
     *           "title": "Input company additional attribute",
     *           "type": "input"
     *           "options": null
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-attributes/id",
     *           "patch": "/api/v1/task-bundle/company-attributes/id",
     *           "delete": "/api/v1/task-bundle/company-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Restore Company attribute Entity",
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
     *  output={"class"="API\TaskBundle\Entity\CompanyAttribute"},
     *  statusCodes={
     *      200 ="is_active param of Entity was successfully changed to active: 1",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found user",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            return $this->notFoundResponse();
        }

        $companyAttribute->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($companyAttribute);
        $this->getDoctrine()->getManager()->flush();

        $companyAttributeArray = $this->get('company_attribute_service')->getAttributeResponse($id);
        return $this->json($companyAttributeArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param $companyAttribute
     * @param $requestData
     * @param bool $create
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function updateCompanyAttribute(CompanyAttribute $companyAttribute, $requestData, $create = false)
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
                    ['message' => $key . ' is not allowed parameter for Tag Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        // Check if type is instance of Variables in VariableHelper
        if (isset($requestData['type'])) {
            $type = $requestData['type'];
            $typeOptions = VariableHelper::getConstants();
            if (!in_array($type, $typeOptions, true)) {
                return $this->createApiResponse([
                    'message' => 'Not allowed Type of company attribute!',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }

        }

        $errors = $this->get('entity_processor')->processEntity($companyAttribute, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($companyAttribute);
            $this->getDoctrine()->getManager()->flush();

            $companyAttributeArray = $this->get('company_attribute_service')->getAttributeResponse($companyAttribute->getId());
            return $this->json($companyAttributeArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
