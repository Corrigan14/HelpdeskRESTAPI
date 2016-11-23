<?php

namespace API\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CompanyController extend's Base Company Controller in CoreBundle
 *
 * @package API\TaskBundle\Controller
 */
class CompanyController extends Controller
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *            "id": "1",
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/task-bundle/company?page=1&company=12&company-attribute=1",
     *           "first": "/task-bundle/company-data?page=1&company=12&company-attribute=1",
     *           "prev": false,
     *           "next": "/task-bundle/company-data?page=2&company=12&company-attribute=1",
     *            "last": "/task-bundle/company-data?page=3&company=12&company-attribute=1"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Entities, this list can be based on Company or on Company Attribute (GET)",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="company",
     *       "description"="Company ID"
     *     },
     *     {
     *       "name"="company-attribute",
     *       "description"="Company Attribute ID"
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
     */
    public function createAction(Request $request)
    {

    }
}
