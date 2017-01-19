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
     *            "type": "input"
     *            "options": null
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
     *  description="Returns a list of Entities (GET)",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
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

        $companyAttributeRepository = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute');

        return $this->json($this->get('api_base.service')->getEntitiesResponse($companyAttributeRepository, $page, 'company_attribute_list'), StatusCodesHelper::SUCCESSFUL_CODE);
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
     *  description="Returns an Entity (GET)",
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
        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            return $this->notFoundResponse();
        }

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $companyAttributeArray = $this->get('api_base.service')->getEntityResponse($companyAttribute, 'company_attribute');

        return $this->createApiResponse($companyAttributeArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            return $this->notFoundResponse();
        }

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
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
        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            return $this->notFoundResponse();
        }

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateCompanyAttribute($companyAttribute, $requestData);
    }

    /**
     * @ApiDoc(
     *  description="Delete Entity (DELETE)",
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
        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            return $this->notFoundResponse();
        }

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $companyAttribute->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($companyAttribute);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::UNACITVATE_MESSAGE,
        ], StatusCodesHelper::SUCCESSFUL_CODE);
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
    private function updateCompanyAttribute($companyAttribute, $requestData, $create = false)
    {
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

            $companyAttributeArray = $this->get('api_base.service')->getEntityResponse($companyAttribute, 'company_attribute');
            return $this->createApiResponse($companyAttributeArray, $statusCode);
        }

        return $this->createApiResponse($errors, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
