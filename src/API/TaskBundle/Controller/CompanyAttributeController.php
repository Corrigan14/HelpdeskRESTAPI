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
     *            "type": "simple_select",
     *            "description": "desc text",
     *            "required": true,
     *            "options":
     *            [
     *               "option1",
     *               "option2",
     *               "option3"
     *            ],
     *            "is_active": true
     *          },
     *          {
     *            "id": 23,
     *            "title": "select 11",
     *            "type": "simple_select",,
     *            "description": "desc text",
     *            "required": true,
     *            "options":
     *            {
     *               "possition 1": "vaue 1"
     *            },
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
     *       "name"="order",
     *       "description"="ASC or DESC order by Created at date"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE company attributes if this param is TRUE, only INACTIVE company attributes if param is FALSE"
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_attribute_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
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
                'order' => '&order=' . $order
            ];

            $options = [
                'loggedUserId' => $this->getUser()->getId(),
                'isActive' => $isActive,
                'filtersForUrl' => $filtersForUrl,
                'order' => $order,
                'limit' => $limit
            ];
            $companyAttributesArray = $this->get('company_attribute_service')->getAttributesResponse($page, $options);
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode($companyAttributesArray));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }

        return $response;


    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 23,
     *           "title": "select 11",
     *           "type": "simple_select",,
     *           "description": "desc text",
     *           "required": true,
     *           "options":
     *           {
     *               "possition 1": "vaue 1"
     *           },
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-attributes/12",
     *           "inactivate": "/api/v1/task-bundle/company-attributes/19/inactivate"
     *           "restore": "/api/v1/task-bundle/company-attributes/19/restore"
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_attributes', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Company Attribute with requested Id does not exist!']));
            return $response;
        }

        $companyAttributeArray = $this->get('company_attribute_service')->getAttributeResponse($id);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($companyAttributeArray));
        return $response;
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 23,
     *           "title": "select 11",
     *           "type": "simple_select",,
     *           "description": "desc text",
     *           "required": true,
     *           "options":
     *           {
     *               "possition 1": "vaue 1"
     *           },
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-attributes/12",
     *           "inactivate": "/api/v1/task-bundle/company-attributes/19/inactivate"
     *           "restore": "/api/v1/task-bundle/company-attributes/19/restore"
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_attribute_create');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $companyAttribute = new CompanyAttribute();
        $companyAttribute->setIsActive(true);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        return $this->updateCompanyAttribute($companyAttribute, $requestBody, true, $locationURL);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 23,
     *           "title": "select 11",
     *           "type": "simple_select",,
     *           "description": "desc text",
     *           "required": true,
     *           "options":
     *           {
     *               "possition 1": "vaue 1"
     *           },
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-attributes/12",
     *           "inactivate": "/api/v1/task-bundle/company-attributes/19/inactivate"
     *           "restore": "/api/v1/task-bundle/company-attributes/19/restore"
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_attribute_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Company Attribute with requested Id does not exist!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        return $this->updateCompanyAttribute($companyAttribute, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Inactivate Company attribute Entity",
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
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function deleteAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_attribute_inactivate', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Company Attribute with requested Id does not exist!']));
            return $response;
        }

        $companyAttribute->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($companyAttribute);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::UNACITVATE_MESSAGE]));
        return $response;
    }

    /**
     * ### Response ###
     *     {
     *        "data":
     *        {
     *           "id": 23,
     *           "title": "select 11",
     *           "type": "simple_select",,
     *           "description": "desc text",
     *           "required": true,
     *           "options":
     *           {
     *               "possition 1": "vaue 1"
     *           },
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-attributes/12",
     *           "inactivate": "/api/v1/task-bundle/company-attributes/19/inactivate"
     *           "restore": "/api/v1/task-bundle/company-attributes/19/restore"
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_attribute_restore', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($id);

        if (!$companyAttribute instanceof CompanyAttribute) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Company Attribute with requested Id does not exist!']));
            return $response;
        }

        $companyAttribute->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($companyAttribute);
        $this->getDoctrine()->getManager()->flush();

        $companyAttributeArray = $this->get('company_attribute_service')->getAttributeResponse($id);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($companyAttributeArray));
        return $response;
    }

    /**
     * @param $companyAttribute
     * @param array $requestData
     * @param bool $create
     * @param $locationURL
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function updateCompanyAttribute(CompanyAttribute $companyAttribute, array $requestData, $create = false, $locationURL): Response
    {
        $allowedUnitEntityParams = [
            'title',
            'type',
            'options',
            'description',
            'required'
        ];

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        if (false !== $requestData) {
            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
            }

            foreach ($requestData as $key => $value) {
                if (!\in_array($key, $allowedUnitEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for Company Attribute Entity!']));
                    return $response;
                }
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            // Check if type is instance of Variables in VariableHelper
            if (isset($requestData['type'])) {
                $type = $requestData['type'];
                $typeOptions = VariableHelper::getConstants();
                if (!\in_array($type, $typeOptions, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Not allowed Type of company attribute! Allowed are: ' . implode(",", $typeOptions)]));
                    return $response;
                }

                // Check and uncode the OPTIONS if the SELECT or MULTI-SELECT Company Attribute Type was chosen
                // JSON or ARRAY is expected
                if (VariableHelper::SIMPLE_SELECT === $type || VariableHelper::MULTI_SELECT === $type) {
                    if (true === $create || (false === $create && isset($requestData['options']))) {
                        if (isset($requestData['options'])) {
                            $optionsData = json_decode($requestData['options'], true);
                            if (!\is_array($optionsData)) {
                                $optionsData = explode(',', $requestData['options']);
                            }
                            $requestData['options'] = $optionsData;
                        } else {
                            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                            $response = $response->setContent(json_encode(['message' => 'For SIMPLE SELECT and MULTI SELECT company attribute type possible OPTIONS have to be defined!']));
                            return $response;
                        }
                    } elseif (false === $create && !isset($requestData['options'])) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'For SIMPLE SELECT and MULTI SELECT task attribute type possible OPTIONS have to be defined!']));
                        return $response;
                    }
                }
            }

            $errors = $this->get('entity_processor')->processEntity($companyAttribute, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($companyAttribute);
                $this->getDoctrine()->getManager()->flush();

                $companyAttributeArray = $this->get('company_attribute_service')->getAttributeResponse($companyAttribute->getId());
                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($companyAttributeArray));
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
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;
    }
}
