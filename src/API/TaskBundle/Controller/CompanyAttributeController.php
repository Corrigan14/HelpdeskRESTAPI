<?php

namespace API\TaskBundle\Controller;

use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
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
     *      200 ="is_active param of Entity was successfully changed to inactive: 0",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Tag",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     */
    public function deleteAction(int $id)
    {
        // TODO: Implement deleteAction() method.
    }
}
