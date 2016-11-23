<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CompanyDataController
 *
 * @package API\TaskBundle\Controller
 */
class CompanyDataController extends ApiBaseController implements ControllerInterface
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
     *           "self": "/task-bundle/company-data?page=1&company=12&company-attribute=1",
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
    public function listAction(Request $request)
    {
        // The access to this action is based on same action for Company Entity
        if (!$this->get('company_voter')->isGranted(VoteOptions::LIST_COMPANIES)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;

        $companyDataRepository = $this->getDoctrine()->getRepository('APITaskBundle:CompanyData');

        $options = [
            'companyId' => $request->get('company'),
            'companyAttributeId' => $request->get('company-attribute'),
        ];

        return $this->json($this->get('api_base.service')->getEntitiesResponse($companyDataRepository, $page, 'company_data_list', $options), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1//task-bundle/company-data/id",
     *           "patch": "/api/v1//task-bundle/company-data/id",
     *           "delete": "/api/v1//task-bundle/company-data/id"
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
     *  output="API\TaskBundle\Entity\CompanyData",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     * @return JsonResponse|Response
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
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-data/2",
     *           "patch": "/api/v1/task-bundle/company-data/2",
     *           "delete": "/api/v1/task-bundle/company-data/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Entity (POST)",
     *  requirements={
     *     {
     *       "name"="companyId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of company to add additional data"
     *     },
     *     {
     *       "name"="companyAttributeId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of company attribute"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\CompanyData"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\CompanyData"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param $companyId
     * @param $companyAttributeId
     * @return JsonResponse|Response
     */
    public function createAction(Request $request, $companyId = false, $companyAttributeId = false)
    {
        // TODO: Implement createAction() method.
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-data/2",
     *           "patch": "/api/v1/task-bundle/company-data/2",
     *           "delete": "/api/v1/task-bundle/company-data/2"
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
     *  input={"class"="API\TaskBundle\Entity\CompanyData"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\CompanyData"},
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
     * @return JsonResponse|Response
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
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/company-data/2",
     *           "patch": "/api/v1/task-bundle/company-data/2",
     *           "delete": "/api/v1/task-bundle/company-data/2"
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
     *  input={"class"="API\TaskBundle\Entity\CompanyData"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\CompanyData"},
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
     * @return JsonResponse|Response
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
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse|Response
     */
    public function deleteAction(int $id)
    {
        // TODO: Implement deleteAction() method.
    }
}
