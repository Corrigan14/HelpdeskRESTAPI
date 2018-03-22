<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\Company;
use API\TaskBundle\Entity\CompanyAttribute;
use API\TaskBundle\Entity\CompanyData;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Services\VariableHelper;
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
     *            [
     *               {
     *                  "id": 140,
     *                  "value": "10",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 177,
     *                     "title": "integer number company additional attribute"
     *                  }
     *               },
     *               {
     *                  "id": 141,
     *                  "value": "String DATA",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 175,
     *                     "title": "input company additional attribute"
     *                   }
     *                },
     *                {
     *                  "id": 89,
     *                  "value": null,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 31,
     *                      "title": "DATE"
     *                  }
     *               },
     *              {
     *                  "id": 167,
     *                  "value": null,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 34,
     *                      "title": "CHECKBOX"
     *                  }
     *              }
     *            ]
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
     *       "name"="order",
     *       "description"="ASC or DESC order by title"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE users if this param is TRUE, only INACTIVE users if param is FALSE"
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
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

            $companiesArray = $this->get('api_company.service')->getCompaniesResponse($page, $options);
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode($companiesArray));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }

        return $response;
    }

    /**
     * ### Response ###
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
     *           "companyData":
     *            [
     *               {
     *                  "id": 140,
     *                  "value": "10",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 177,
     *                     "title": "integer number company additional attribute"
     *                  }
     *               },
     *               {
     *                  "id": 141,
     *                  "value": "String DATA",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 175,
     *                     "title": "input company additional attribute"
     *                   }
     *                },
     *                {
     *                  "id": 89,
     *                  "value": null,
     *                  "dateValue": 547948800,,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 31,
     *                      "title": "DATE"
     *                  }
     *               },
     *              {
     *                  "id": 167,
     *                  "value": null,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 34,
     *                      "title": "CHECKBOX"
     *                  }
     *              }
     *            ]
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
     *  description="Search in Company Entities",
     *  filters={
     *     {
     *       "name"="term",
     *       "description"="Search term"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE users if this param is TRUE, only INACTIVE users if param is FALSE"
     *     },
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by title"
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
     *      200 ="Entity was successfully found",
     *      401 ="Unauthorized request",
     *      403 ="Access denied"
     *  })
     *
     * @param Request $request
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function searchAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_search');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
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
            $term = $processedFilterParams['term'];

            $filtersForUrl = [
                'isActive' => '&isActive=' . $isActive,
                'order' => '&order=' . $order,
                'term' => '&term=' . $term
            ];

            $companiesArray = $this->get('api_company.service')->getCompaniesSearchResponse($term, $page, $isActive, $filtersForUrl, $order, $limit);
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode($companiesArray));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }

        return $response;
    }

    /**
     * ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 42,
     *             "title": "LanSystems",
     *             "ico": "110258782",
     *             "dic": "12587458996244",
     *             "ic_dph": null,
     *             "is_active": true
     *           },
     *          {
     *             "id": 43,
     *             "title": "LanFast",
     *             "ico": "110258783",
     *             "dic": "12587458996245",
     *             "ic_dph": null,
     *             "is_active": true
     *           }
     *       ]
     *       "date": 1518907522
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of All active Companies",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *  },
     * )
     *
     * @param string|bool $date
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function listOfAllCompaniesAction($date = false): Response
    {
        // JSON API Response - Content type and Location settings
        if (false !== $date && 'false' !== $date) {
            $intDate = (int)$date;
            if (is_int($intDate) && null !== $intDate) {
                $locationURL = $this->generateUrl('company_list_of_all_active_from_date', ['date' => $date]);
                $dateTimeObject = new \DateTime("@$date");
            } else {
                $locationURL = $this->generateUrl('company_list_of_all_active');
                $dateTimeObject = false;
            }
        } else {
            $locationURL = $this->generateUrl('company_list_of_all_active');
            $dateTimeObject = false;
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        if ($date && !($dateTimeObject instanceof \Datetime)) {
            $response = $response->setContent(['message' => 'Date parameter is not in a valid format! Expected format: Timestamp']);
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            return $response;
        }

        $allCompanies = $this->get('api_company.service')->getAllCompaniesForASelectionList($dateTimeObject);
        $currentDate = new \DateTime('UTC');
        $currentDateTimezone = $currentDate->getTimestamp();

        $dataArray = [
            'data' => $allCompanies,
            'date' => $currentDateTimezone
        ];

        $response = $response->setContent(json_encode($dataArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
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
     *            [
     *               {
     *                  "id": 140,
     *                  "value": "10",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 177,
     *                     "title": "integer number company additional attribute"
     *                  }
     *               },
     *               {
     *                  "id": 141,
     *                  "value": "String DATA",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 175,
     *                     "title": "input company additional attribute"
     *                   }
     *                },
     *                {
     *                  "id": 89,
     *                  "value": null,
     *                  "dateValue": 547948800,,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 31,
     *                      "title": "DATE"
     *                  }
     *               },
     *              {
     *                  "id": 167,
     *                  "value": null,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 34,
     *                      "title": "CHECKBOX"
     *                  }
     *              }
     *            ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/id",
     *           "inactivate": "/api/v1/core-bundle/companies/568/inativate",
     *           "restore": "/api/v1/core-bundle/companies/568/restore"
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
     *      403 ="Access denied",
     *      404 ="Not found Entity"
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
        $locationURL = $this->generateUrl('company', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Company with requested Id does not exist!']));
            return $response;
        }

        $companyArray = $this->get('api_company.service')->getCompanyResponse($id);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($companyArray));
        return $response;
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
     *            [
     *               {
     *                  "id": 140,
     *                  "value": "10",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 177,
     *                     "title": "integer number company additional attribute"
     *                  }
     *               },
     *               {
     *                  "id": 141,
     *                  "value": "String DATA",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 175,
     *                     "title": "input company additional attribute"
     *                   }
     *                },
     *                {
     *                  "id": 89,
     *                  "value": null,
     *                  "dateValue": 547948800,,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 31,
     *                      "title": "DATE"
     *                  }
     *               },
     *              {
     *                  "id": 167,
     *                  "value": null,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 34,
     *                      "title": "CHECKBOX"
     *                  }
     *              }
     *            ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/2",
     *           "inactivate": "/api/v1/core-bundle/companies/568/inativate",
     *           "restore": "/api/v1/core-bundle/companies/568/restore"
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
        $locationURL = $this->generateUrl('company_create');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        $company = new Company();
        $company->setIsActive(true);

        return $this->updateCompany($company, $requestBody, true, $locationURL);
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
     *            [
     *               {
     *                  "id": 140,
     *                  "value": "10",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 177,
     *                     "title": "integer number company additional attribute"
     *                  }
     *               },
     *               {
     *                  "id": 141,
     *                  "value": "String DATA",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 175,
     *                     "title": "input company additional attribute"
     *                   }
     *                },
     *                {
     *                  "id": 89,
     *                  "value": null,
     *                  "dateValue": 547948800,,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 31,
     *                      "title": "DATE"
     *                  }
     *               },
     *              {
     *                  "id": 167,
     *                  "value": null,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 34,
     *                      "title": "CHECKBOX"
     *                  }
     *              }
     *            ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/2",
     *           "inactivate": "/api/v1/core-bundle/companies/568/inativate",
     *           "restore": "/api/v1/core-bundle/companies/568/restore"
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updateAction(int $id, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);


        if (!$company instanceof Company) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Company with requested Id does not exist!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        return $this->updateCompany($company, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Inactivate Company Entity",
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
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function deleteAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_delete', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Company with requested Id does not exist!']));
            return $response;
        }

        $company->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($company);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::UNACITVATE_MESSAGE]));
        return $response;
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
     *            [
     *               {
     *                  "id": 140,
     *                  "value": "10",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 177,
     *                     "title": "integer number company additional attribute"
     *                  }
     *               },
     *               {
     *                  "id": 141,
     *                  "value": "String DATA",,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "taskAttribute":
     *                  {
     *                     "id": 175,
     *                     "title": "input company additional attribute"
     *                   }
     *                },
     *                {
     *                  "id": 89,
     *                  "value": null,
     *                  "dateValue": 547948800,,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 31,
     *                      "title": "DATE"
     *                  }
     *               },
     *              {
     *                  "id": 167,
     *                  "value": null,
     *                  "dateValue": null,
     *                  "boolValue": true,
     *                  "companyAttribute":
     *                  {
     *                      "id": 34,
     *                      "title": "CHECKBOX"
     *                  }
     *              }
     *            ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/core-bundle/companies/2",
     *           "inactivate": "/api/v1/core-bundle/companies/568/inativate",
     *           "restore": "/api/v1/core-bundle/companies/568/restore"
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
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function restoreAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('company_restore', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::COMPANY_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($id);

        if (!$company instanceof Company) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Company with requested Id does not exist!']));
            return $response;
        }

        $company->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($company);
        $this->getDoctrine()->getManager()->flush();

        $companyArray = $this->get('api_company.service')->getCompanyResponse($company->getId());
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($companyArray));
        return $response;
    }

    /**
     * @param Company $company
     * @param array $requestData
     * @param bool $create
     * @param $locationURL
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    private function updateCompany(Company $company, array $requestData, $create = false, $locationURL): Response
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

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        if (false !== $requestData) {
            // JSON Array with COMPANY ATTRIBUTE ID as an ID and VALUE as an VALUE in Company Data param is required
            $requestDetailData = false;
            if (isset($requestData['company_data'])) {
                if (\is_array($requestData['company_data'])) {
                    $requestDetailData = $requestData['company_data'];
                } else {
                    $requestDetailData = json_decode($requestData['company_data'], true);
                }
                if (!\is_array($requestDetailData)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Problem with company additional data - not a correct format. Expected: "company_data":"{\"27\":\"INPUT+VALUE\",\"28\":\"text\"']));
                    return $response;
                }
                unset($requestData['company_data']);
            } elseif (isset($requestData['companyData'])) {
                if (is_array($requestData['companyData'])) {
                    $requestDetailData = $requestData['companyData'];
                } else {
                    $requestDetailData = json_decode($requestData['companyData'], true);
                }
                if (!\is_array($requestDetailData)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Problem with company additional data - not a correct format. Expected: "company_data":"{\"27\":\"INPUT+VALUE\",\"28\":\"text\"']));
                    return $response;
                }
                unset($requestData['companyData']);
            }

            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
            }

            //Check allowed Company parameters
            foreach ($requestData as $key => $value) {
                if (!\in_array($key, $allowedCompanyEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for Company Entity!']));
                    return $response;
                }
            }

            // Check if some Company Data are required
            $allExistedCompanyAttributes = $this->getDoctrine()->getRepository('APICoreBundle:CompanyAttribute')->findAll();
            $requiredCompanyAttributeData = [];
            /** @var TaskAttribute|null $attr */
            foreach ($allExistedCompanyAttributes as $attr) {
                if ($attr->getIsActive() && $attr->getRequired()) {
                    $requiredCompanyAttributeData[] = $attr->getId();
                }
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            $errors = $this->get('entity_processor')->processEntity($company, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getConnection()->beginTransaction();
                try {
                    $this->getDoctrine()->getManager()->persist($company);
                    $this->getDoctrine()->getManager()->flush();
                    /**
                     * Fill CompanyData Entity if some of its parameters were sent
                     */
                    if (\is_array($requestDetailData)) {
                        $sentCompanyAttributeKeys = [];
                        foreach ($requestDetailData as $key => $value) {
                            $sentCompanyAttributeKeys[] = $key;
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

                                // If value = 'null' is being sent, data are set to null
                                if (!is_array($value) && 'null' === strtolower($value)) {
                                    $cd->setValue(null);
                                    $cd->setDateValue(null);
                                    $cd->setBoolValue(null);
                                    $company->addCompanyDatum($cd);

                                    $this->getDoctrine()->getManager()->persist($cd);
                                    $this->getDoctrine()->getManager()->persist($company);
                                    $this->getDoctrine()->getManager()->flush();
                                } else {
                                    $cdValueChecker = $this->get('entity_processor')->checkDataValueFormat($companyAttribute, $value);
                                    if (true === $cdValueChecker) {
                                        if ($companyAttribute->getType() === 'checkbox') {
                                            if (\is_string($value)) {
                                                $value = strtolower($value);
                                            }
                                            if ('true' === $value || '1' === $value || 1 === $value) {
                                                $cd->setBoolValue(true);
                                            } else {
                                                $cd->setBoolValue(false);
                                            }
                                        } elseif ($companyAttribute->getType() === 'date') {
                                            $intValue = (int)$value;
                                            $cd->setDateValue($intValue);
                                        } else {
                                            $cd->setValue($value);
                                        }
                                        $company->addCompanyDatum($cd);

                                        $this->getDoctrine()->getManager()->persist($cd);
                                        $this->getDoctrine()->getManager()->persist($company);
                                        $this->getDoctrine()->getManager()->flush();
                                    } else {
                                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                                        $expectation = $this->get('entity_processor')->returnExpectedDataFormat($companyAttribute);
                                        $response = $response->setContent(json_encode(['message' => 'Problem with company additional data (companyData) value format! For Company Attribute with ID: ' . $companyAttribute->getId() . ', ' . $expectation . ' is/are expected.']));
                                        return $response;
                                    }
                                }
                            } else {
                                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                                $response = $response->setContent(json_encode(['message' => 'The key: ' . $key . ' of Company Attribute is not valid (Company Attribute with this ID does not exist)']));
                                return $response;
                            }
                        }
                        // Check if All required Company Attribute Data were sent
                        $intersect = array_diff($requiredCompanyAttributeData, $sentCompanyAttributeKeys);
                        if (count($intersect) > 0) {
                            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                            $response = $response->setContent(json_encode(['message' => 'Company Data with Company Attribute ID: ' . implode(',', $intersect) . ' are also required!']));
                            return $response;
                        }
                    } elseif (count($requiredCompanyAttributeData) > 0) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'Company Data with Company Attribute ID: ' . implode(',', $requiredCompanyAttributeData) . ' are required!']));
                        return $response;
                    }
                    $companyArray = $this->get('api_company.service')->getCompanyResponse($company->getId());
                    $response = $response->setStatusCode($statusCode);
                    $response = $response->setContent(json_encode($companyArray));
                    $this->getDoctrine()->getConnection()->commit();
                    return $response;
                } catch (\Exception $exception) {
                    $this->getDoctrine()->getConnection()->rollBack();
                    $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
                    $response = $response->setContent(json_encode(['message' => $exception->getMessage()]));
                    return $response;
                }
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
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }
        return $response;
    }
}
