<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Security\VoteOptions;
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
     *            "title": "Web-Solutions"
     *            "ico": "1102587"
     *            "dic": "12587459644"
     *            "ic_dph": "12587459644"
     *            "street": "Cesta 125"
     *            "city": "Bratislava"
     *            "zip": "02587"
     *            "country": "SR"
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "api/v1/core-bundle/companies?page=1",
     *           "first": "api/v1/core-bundle/companies?page=1",
     *           "prev": false,
     *           "next": "api/v1/core-bundle/companies?page=2",
     *            "last": "api/v1/core-bundle/companies?page=3"
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
     */
    public function listAction(Request $request)
    {
        if (!$this->get('company_voter')->isGranted(VoteOptions::LIST_COMPANIES)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;

        return $this->json($this->get('api_base.service')->getEntitiesResponse($this->getDoctrine()->getRepository('APICoreBundle:Company'), $page, 'company_list'), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
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
     *           "put": "/api/v1/core-bundle/companies/id",
     *           "patch": "/api/v1/core-bundle/companies/id",
     *           "delete": "/api/v1/core-bundle/companies/id"
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
     *  output="API\CoreBundle\Entity\Company",
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
     */
    public function getAction(int $id)
    {
        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        if (!$this->get('company_voter')->isGranted(VoteOptions::SHOW_COMPANY, $company)) {
            return $this->accessDeniedResponse();
        }

        return $this->createApiResponse($this->get('api_base.service')->getEntityResponse($company, 'company'), StatusCodesHelper::SUCCESSFUL_CODE);
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
     *  description="Create a new Entity (POST)",
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
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/2",
     *           "patch": "/api/v1/core-bundle/companies/2",
     *           "delete": "/api/v1/core-bundle/companies/2"
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
     */
    public function updateAction(int $id, Request $request)
    {
        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        if (!$this->get('company_voter')->isGranted(VoteOptions::UPDATE_COMPANY, $company)) {
            return $this->accessDeniedResponse();
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
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/2",
     *           "patch": "/api/v1/core-bundle/companies/2",
     *           "delete": "/api/v1/core-bundle/companies/2"
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
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        if (!$this->get('company_voter')->isGranted(VoteOptions::UPDATE_COMPANY, $company)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateCompany($company, $requestData);
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
     *      200 ="The is_active Status of Entity was successfully changed to inactive",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     */
    public function deleteAction(int $id)
    {
        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            return $this->notFoundResponse();
        }

        if (!$this->get('company_voter')->isGranted(VoteOptions::DELETE_COMPANY, $id)) {
            return $this->accessDeniedResponse();
        }

        $company->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($company);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::UNACITVATE_MESSAGE,
        ], StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param mixed $company
     * @param array $requestData
     * @param bool $create
     *
     * @return Response|JsonResponse
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

            $entityResponse = $this->get('api_base.service')->getEntityResponse($company, 'company');
            return $this->createApiResponse($entityResponse, $statusCode);
        }

        return $this->invalidParametersResponse();
    }
}
