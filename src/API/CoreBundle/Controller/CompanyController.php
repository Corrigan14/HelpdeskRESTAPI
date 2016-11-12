<?php

namespace API\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
     *           "self": "api/v1/companies?page=1",
     *           "first": "api/v1/companies?page=1",
     *           "prev": false,
     *           "next": "api/v1/companies?page=2",
     *            "last": "api/v1/companies?page=3"
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
     *  }
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        // TODO: Implement listAction() method.
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
     *           "put": "/api/v1/companies/id",
     *           "patch": "/api/v1/companies/id",
     *           "delete": "/api/v1/companies/id"
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
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getAction(int $id)
    {
        // TODO: Implement getAction() method.
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
     *           "put": "/api/v1/companies/2",
     *           "patch": "/api/v1/companies/2",
     *           "delete": "/api/v1/companies/2"
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
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        // TODO: Implement createAction() method.
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
     *           "put": "/api/v1/companies/2",
     *           "patch": "/api/v1/companies/2",
     *           "delete": "/api/v1/companies/2"
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
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAction(int $id, Request $request)
    {
        // TODO: Implement updateAction() method.
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
     *           "put": "/api/v1/companies/2",
     *           "patch": "/api/v1/companies/2",
     *           "delete": "/api/v1/companies/2"
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
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePartialAction(int $id, Request $request)
    {
        // TODO: Implement updatePartialAction() method.
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
     *      204 ="The Entity was successfully deleted",
     *      401 ="Unauthorized request",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteAction(int $id)
    {
        // TODO: Implement deleteAction() method.
    }
}
