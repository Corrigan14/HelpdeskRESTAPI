<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Security\VoteOptions;
use API\TaskBundle\Entity\CompanyAttribute;
use API\TaskBundle\Entity\CompanyData;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class CompanyController extend's Base Company Controller in CoreBundle
 *
 * @package API\TaskBundle\Controller
 */
class CompanyController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Web-Solutions"
     *           "ico": "1102587"
     *           "dic": "12587459644"
     *           "ic_dph": "12587459644"
     *           "street": "Cesta 125"
     *           "city": "Bratislava"
     *           "zip": "02587"
     *           "country": "SR"
     *        },
     *       "company_data": {
     *           "0":
     *           {
     *             "id": 44,
     *             "value": "data val",
     *             "company_attribute":
     *             {
     *               "id": 1,
     *               "title": "input company additional attribute",
     *               "type": "input",
     *               "is_active": true
     *             }
     *           },
     *           "1":
     *           {
     *             "id": 45,
     *             "value": "data valluesgyda gfg",
     *             "company_attribute":
     *             {
     *               "id": 2,
     *               "title": "select company additional attribute",
     *               "type": "simple_select",
     *               "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *               "is_active": true
     *             }
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-extend/2",
     *           "patch": "/api/v1/task-bundle/company-extend/2",
     *           "delete": "/api/v1/task-bundle/company-extend/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns full Company Entity including extra Company Data",
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
     *  output={"class"="API\CoreBundle\Entity\Company"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $id
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getAction(int $id)
    {
        $company = $this->get('company_service')->getFullCompany($id);

        if (!$this->get('company_voter')->isGranted(VoteOptions::SHOW_COMPANY, $company)) {
            return $this->accessDeniedResponse();
        }

        if (!$company instanceof Company) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);
        }

        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        $entityResponse = $this->get('company_service')->getEntityResponse($company, 'company_extension');

        return $this->createApiResponse($entityResponse, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Web-Solutions"
     *           "ico": "1102587"
     *           "dic": "12587459644"
     *           "ic_dph": "12587459644"
     *           "street": "Cesta 125"
     *           "city": "Bratislava"
     *           "zip": "02587"
     *           "country": "SR"
     *        },
     *       "company_data": {
     *           "0":
     *           {
     *             "id": 44,
     *             "value": "data val",
     *             "company_attribute":
     *             {
     *               "id": 1,
     *               "title": "input company additional attribute",
     *               "type": "input",
     *               "is_active": true
     *             }
     *           },
     *           "1":
     *           {
     *             "id": 45,
     *             "value": "data valluesgyda gfg",
     *             "company_attribute":
     *             {
     *               "id": 2,
     *               "title": "select company additional attribute",
     *               "type": "simple_select",
     *               "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *               "is_active": true
     *             }
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-extend/2",
     *           "patch": "/api/v1/task-bundle/company-extend/2",
     *           "delete": "/api/v1/task-bundle/company-extend/2"
     *         }
     *      }
     *
     * @ApiDoc(
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
     * @return JsonResponse|Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request)
    {
        if (!$this->get('company_voter')->isGranted(VoteOptions::CREATE_COMPANY)) {
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
     *           "id": "2",
     *           "title": "Web-Solutions"
     *           "ico": "1102587"
     *           "dic": "12587459644"
     *           "ic_dph": "12587459644"
     *           "street": "Cesta 125"
     *           "city": "Bratislava"
     *           "zip": "02587"
     *           "country": "SR"
     *        },
     *       "company_data": {
     *           "0":
     *           {
     *             "id": 44,
     *             "value": "data val",
     *             "company_attribute":
     *             {
     *               "id": 1,
     *               "title": "input company additional attribute",
     *               "type": "input",
     *               "is_active": true
     *             }
     *           },
     *           "1":
     *           {
     *             "id": 45,
     *             "value": "data valluesgyda gfg",
     *             "company_attribute":
     *             {
     *               "id": 2,
     *               "title": "select company additional attribute",
     *               "type": "simple_select",
     *               "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *               "is_active": true
     *             }
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-extend/2",
     *           "patch": "/api/v1/task-bundle/company-extend/2",
     *           "delete": "/api/v1/task-bundle/company-extend/2"
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
     *      200 ="The entity was successfully updated",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse|Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updateAction(Request $request, int $id)
    {
        $company = $this->get('company_service')->getFullCompany($id);

        if (!$this->get('company_voter')->isGranted(VoteOptions::UPDATE_COMPANY, $company)) {
            return $this->accessDeniedResponse();
        }

        if (!$company instanceof Company) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);
        }

        $requestData = $request->request->all();

        return $this->updateCompany($company, $requestData);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Web-Solutions"
     *           "ico": "1102587"
     *           "dic": "12587459644"
     *           "ic_dph": "12587459644"
     *           "street": "Cesta 125"
     *           "city": "Bratislava"
     *           "zip": "02587"
     *           "country": "SR"
     *        },
     *       "company_data": {
     *           "0":
     *           {
     *             "id": 44,
     *             "value": "data val",
     *             "company_attribute":
     *             {
     *               "id": 1,
     *               "title": "input company additional attribute",
     *               "type": "input",
     *               "is_active": true
     *             }
     *           },
     *           "1":
     *           {
     *             "id": 45,
     *             "value": "data valluesgyda gfg",
     *             "company_attribute":
     *             {
     *               "id": 2,
     *               "title": "select company additional attribute",
     *               "type": "simple_select",
     *               "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *               "is_active": true
     *             }
     *           }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-extend/2",
     *           "patch": "/api/v1/task-bundle/company-extend/2",
     *           "delete": "/api/v1/task-bundle/company-extend/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update partially a Company Entity with extra Company Data.
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
     *      200 ="The entity was successfully updated",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse|Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updatePartiallyAction(Request $request, int $id)
    {
        $company = $this->get('company_service')->getFullCompany($id);

        if (!$this->get('company_voter')->isGranted(VoteOptions::UPDATE_COMPANY, $company)) {
            return $this->accessDeniedResponse();
        }

        if (!$company instanceof Company) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);
        }

        $requestData = $request->request->all();

        return $this->updateCompany($company, $requestData);
    }

    /**
     * @param mixed $company
     * @param array $requestData
     * @param bool $create
     *
     * @return Response|JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function updateCompany($company, array $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        if (null === $company || !$company instanceof Company) {
            return $this->notFoundResponse();
        }

        $errors = $this->get('entity_processor')->processEntity($company, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($company);
            $this->getDoctrine()->getManager()->flush();

            /**
             * Fill CompanyData Entity if some its parameters were sent
             */
            if (isset($requestData['company_data']) && count($requestData['company_data']) > 0) {
                /** @var array $companyData */
                $companyData = $requestData['company_data'];
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

            $fullCompanyEntity = $this->get('company_service')->getFullCompany($company->getId());
//            $fullCompanyEntity = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($company->getId());
            $entityResponse = $this->get('company_service')->getEntityResponse($fullCompanyEntity, 'company_extension');
            return $this->createApiResponse($entityResponse, $statusCode);
        }

        return $this->invalidParametersResponse();
    }
}
