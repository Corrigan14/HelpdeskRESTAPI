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
     *  attributes must be defined in the CompanyAttribute Entity.",
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
                        $cd = new CompanyData();
                        $cd->setCompany($company);
                        $cd->setCompanyAttribute($companyAttribute);

                        $cdErrors = $this->get('entity_processor')->processEntity($cd, ['value' => $value]);
                        if (false === $cdErrors) {
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

            $fullCompanyEntity = $this->getDoctrine()->getRepository('APICoreBundle:Company')->getFullCompanyEntity($company);
            dump($fullCompanyEntity);
            $entityResponse = $this->get('api_base.service')->getEntityResponse($fullCompanyEntity, 'company');
            return $this->createApiResponse($entityResponse, $statusCode);
        }

        return $this->invalidParametersResponse();
    }
}
