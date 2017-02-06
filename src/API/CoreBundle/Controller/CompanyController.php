<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\Company;
use API\TaskBundle\Entity\CompanyAttribute;
use API\TaskBundle\Entity\CompanyData;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CompanyController
 *
 * @package API\CoreBundle\Controller
 */
class CompanyController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *            "id": "1",
     *            "title": "Web-Solutions",
     *            "ico": "1102587",
     *            "dic": "12587459644",
     *            "ic_dph": "12587459644",
     *            "street": "Cesta 125",
     *            "city": "Bratislava",
     *            "zip": "02587",
     *            "country": "SR",
     *            "is_active": true,
     *            "companyData":
     *            {
     *             {
     *               "id": 44,
     *               "value": "data val",
     *               "companyAttribute":
     *               {
     *                 "id": 1,
     *                 "title": "input company additional attribute",
     *                 "type": "input",
     *                 "is_active": true
     *               }
     *             },
     *             {
     *               "id": 45,
     *               "value": "data valluesgyda gfg",
     *               "companyAttribute":
     *               {
     *                 "id": 2,
     *                 "title": "select company additional attribute",
     *                 "type": "simple_select",
     *                 "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                 "is_active": true
     *               }
     *             }
     *          },
     *          {
     *             "id": 42,
     *             "title": "LanSystems",
     *             "ico": "110258782",
     *             "dic": "12587458996244",
     *             "ic_dph": null,
     *             "street": "Ina cesta 125",
     *             "city": "Bratislava",
     *             "zip": "021478",
     *             "country": "Slovenska Republika",
     *             "is_active": true,
     *             "companyData": []
     *           }
     *       ],
     *       "_links":
     *       {
     *           "self": "api/v1/core-bundle/companies?page=1",
     *           "first": "api/v1/core-bundle/companies?page=1",
     *           "prev": false,
     *           "next": "api/v1/core-bundle/companies?page=2",
     *           "last": "api/v1/core-bundle/companies?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Company Entities",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE users if this param is TRUE, only INACTIVE users if param is FALSE"
     *     },
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
     *      401 ="Unauthorized request"
     *  }
     * )
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request)
    {
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

        return $this->json($this->get('api_company.service')->getCompaniesResponse($page, $options), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "Web-Solutions",
     *            "ico": "1102587",
     *            "dic": "12587459644",
     *            "ic_dph": "12587459644",
     *            "street": "Cesta 125",
     *            "city": "Bratislava",
     *            "zip": "02587",
     *            "country": "SR",
     *            "is_active": true,
     *            "companyData":
     *            {
     *             {
     *               "id": 44,
     *               "value": "data val",
     *               "companyAttribute":
     *               {
     *                 "id": 1,
     *                 "title": "input company additional attribute",
     *                 "type": "input",
     *                 "is_active": true
     *               }
     *             },
     *             {
     *               "id": 45,
     *               "value": "data valluesgyda gfg",
     *               "companyAttribute":
     *               {
     *                 "id": 2,
     *                 "title": "select company additional attribute",
     *                 "type": "simple_select",
     *                 "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                 "is_active": true
     *               }
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/id",
     *           "patch": "/api/v1/core-bundle/companies/id",
     *           "delete": "/api/v1/core-bundle/companies/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Company Entity",
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
     *  output="API\CoreBundle\Entity\Company",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $id
     * @return Response|JsonResponse
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        $companyArray = $this->get('api_company.service')->getCompanyResponse($id);
        return $this->json($companyArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "Web-Solutions",
     *            "ico": "1102587",
     *            "dic": "12587459644",
     *            "ic_dph": "12587459644",
     *            "street": "Cesta 125",
     *            "city": "Bratislava",
     *            "zip": "02587",
     *            "country": "SR",
     *            "is_active": true,
     *            "companyData":
     *            {
     *             {
     *               "id": 44,
     *               "value": "data val",
     *               "companyAttribute":
     *               {
     *                 "id": 1,
     *                 "title": "input company additional attribute",
     *                 "type": "input",
     *                 "is_active": true
     *               }
     *             },
     *             {
     *               "id": 45,
     *               "value": "data valluesgyda gfg",
     *               "companyAttribute":
     *               {
     *                 "id": 2,
     *                 "title": "select company additional attribute",
     *                 "type": "simple_select",
     *                 "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                 "is_active": true
     *               }
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/2",
     *           "patch": "/api/v1/core-bundle/companies/2",
     *           "delete": "/api/v1/core-bundle/companies/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Company Entity with extra Company Data.
     *  This can be added by attributes: company_data[company_attribute_id] = value,
     *  attributes must be defined in the CompanyAttribute Entity",
     *  input={"class"="API\CoreBundle\Entity\Company"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\Company"},
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
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $company = new Company();

        return $this->updateCompany($company, $requestData, true);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "Web-Solutions",
     *            "ico": "1102587",
     *            "dic": "12587459644",
     *            "ic_dph": "12587459644",
     *            "street": "Cesta 125",
     *            "city": "Bratislava",
     *            "zip": "02587",
     *            "country": "SR",
     *            "is_active": true,
     *            "companyData":
     *            {
     *             {
     *               "id": 44,
     *               "value": "data val",
     *               "companyAttribute":
     *               {
     *                 "id": 1,
     *                 "title": "input company additional attribute",
     *                 "type": "input",
     *                 "is_active": true
     *               }
     *             },
     *             {
     *               "id": 45,
     *               "value": "data valluesgyda gfg",
     *               "companyAttribute":
     *               {
     *                 "id": 2,
     *                 "title": "select company additional attribute",
     *                 "type": "simple_select",
     *                 "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                 "is_active": true
     *               }
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/2",
     *           "patch": "/api/v1/core-bundle/companies/2",
     *           "delete": "/api/v1/core-bundle/companies/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update a Company Entity with extra Company Data.
     *  This can be edited by attributes: company_data[company_attribute_id] = value,
     *  attributes must be defined in the CompanyAttribute Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\CoreBundle\Entity\Company"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\Company"},
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
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updateAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);


        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();

        return $this->updateCompany($company, $requestData);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "Web-Solutions",
     *            "ico": "1102587",
     *            "dic": "12587459644",
     *            "ic_dph": "12587459644",
     *            "street": "Cesta 125",
     *            "city": "Bratislava",
     *            "zip": "02587",
     *            "country": "SR",
     *            "is_active": true,
     *            "companyData":
     *            {
     *             {
     *               "id": 44,
     *               "value": "data val",
     *               "companyAttribute":
     *               {
     *                 "id": 1,
     *                 "title": "input company additional attribute",
     *                 "type": "input",
     *                 "is_active": true
     *               }
     *             },
     *             {
     *               "id": 45,
     *               "value": "data valluesgyda gfg",
     *               "companyAttribute":
     *               {
     *                 "id": 2,
     *                 "title": "select company additional attribute",
     *                 "type": "simple_select",
     *                 "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                 "is_active": true
     *               }
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/2",
     *           "patch": "/api/v1/core-bundle/companies/2",
     *           "delete": "/api/v1/core-bundle/companies/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update a Company Entity with extra Company Data.
     *  This can be edited by attributes: company_data[company_attribute_id] = value,
     *  attributes must be defined in the CompanyAttribute Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\CoreBundle\Entity\Company"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\Company"},
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
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();

        return $this->updateCompany($company, $requestData);
    }

    /**
     * @ApiDoc(
     *  description="Delete Company Entity",
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
     *      200 ="The is_active Status of Entity was successfully changed to inactive",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Company",
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
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        $company->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($company);
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
     *            "title": "Web-Solutions",
     *            "ico": "1102587",
     *            "dic": "12587459644",
     *            "ic_dph": "12587459644",
     *            "street": "Cesta 125",
     *            "city": "Bratislava",
     *            "zip": "02587",
     *            "country": "SR",
     *            "is_active": true,
     *            "companyData":
     *            {
     *             {
     *               "id": 44,
     *               "value": "data val",
     *               "companyAttribute":
     *               {
     *                 "id": 1,
     *                 "title": "input company additional attribute",
     *                 "type": "input",
     *                 "is_active": true
     *               }
     *             },
     *             {
     *               "id": 45,
     *               "value": "data valluesgyda gfg",
     *               "companyAttribute":
     *               {
     *                 "id": 2,
     *                 "title": "select company additional attribute",
     *                 "type": "simple_select",
     *                 "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                 "is_active": true
     *               }
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/2",
     *           "patch": "/api/v1/core-bundle/companies/2",
     *           "delete": "/api/v1/core-bundle/companies/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Restore Company Entity",
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
     *      404 ="Not found company",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     * @throws \LogicException
     */
    public function restoreAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        $company->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($company);
        $this->getDoctrine()->getManager()->flush();

        $companyArray = $this->get('api_company.service')->getCompanyResponse($company->getId());
        return $this->json($companyArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param mixed $company
     * @param array $requestData
     * @param bool $create
     *
     * @return Response|JsonResponse
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    private function updateCompany($company, array $requestData, $create = false)
    {
        $allowedCompanyEntityParams = [
            'title',
            'ico',
            'dic',
            'ic_dph',
            'street',
            'city',
            'zip',
            'country'
        ];

        $requestDetailData = [];
        if (isset($requestData['company_data']) && count($requestData['company_data']) > 0) {
            $requestDetailData = $requestData['company_data'];
            unset($requestData['company_data']);
        }

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!in_array($key, $allowedCompanyEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for Company Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        if (null === $company || !$company instanceof Company) {
            return $this->notFoundResponse();
        }
        dump($requestData);
        dump($requestDetailData);
        $errors = $this->get('entity_processor')->processEntity($company, $requestData);

        dump($errors);
        dump($company);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($company);
            $this->getDoctrine()->getManager()->flush();

            /**
             * Fill CompanyData Entity if some its parameters were sent
             */
            if ($requestDetailData) {
                /** @var array $companyData */
                $companyData = $requestDetailData;
                foreach ($companyData as $key => $value) {
                    $companyAttribute = $this->getDoctrine()->getRepository('APITaskBundle:CompanyAttribute')->find($key);

                    if ($companyAttribute instanceof CompanyAttribute) {
                        $cd = $this->getDoctrine()->getRepository('APITaskBundle:CompanyData')->findOneBy([
                            'companyAttribute' => $companyAttribute,
                            'company' => $company,
                        ]);

                        if (!$cd instanceof CompanyData) {
                            $cd = new CompanyData();
                            $cd->setCompany($company);
                            $cd->setCompanyAttribute($companyAttribute);
                        }

                        $cdErrors = $this->get('entity_processor')->processEntity($cd, ['value' => $value]);
                        if (false === $cdErrors) {
                            $company->addCompanyDatum($cd);
                            $this->getDoctrine()->getManager()->persist($company);
                            $this->getDoctrine()->getManager()->persist($cd);
                            $this->getDoctrine()->getManager()->flush();
                        } else {
                            $this->createApiResponse([
                                'message' => 'The value of company_data with key: ' . $key . ' is invalid',
                            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        }
                    } else {
                        return $this->createApiResponse([
                            'message' => 'The key: ' . $key . ' of Company Attribute is not valid (Company Attribute with this ID doesn\'t exist)',
                        ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    }
                }
            }

            $companyArray = $this->get('api_company.service')->getCompanyResponse($company->getId());
            return $this->json($companyArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
