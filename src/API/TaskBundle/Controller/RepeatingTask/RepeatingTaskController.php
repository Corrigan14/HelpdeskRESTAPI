<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RepeatingTaskController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class RepeatingTaskController extends ApiBaseController implements ControllerInterface
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
     *          "self": "/api/v1/task-bundle/projects?page=2&limit=10&page=2&limit=10&order=DESC&isActive=true",
     *          "first": "/api/v1/task-bundle/projects?page=1&limit=10&page=2&limit=10&order=DESC&isActive=true",
     *          "prev": "/api/v1/task-bundle/projects?page=1&limit=10&page=2&limit=10&order=DESC&isActive=true",
     *          "next": false,
     *          "last": "/api/v1/task-bundle/projects?page=1&limit=10&page=2&limit=10&order=DESC&isActive=true"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Repeating Task entities.",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE repeating tasks if this param is TRUE, only INACTIVE if param is FALSE"
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Limit for Pagination: 999 - returns all entities, null - returns 10 entities"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Title"
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
     *      200 ="Request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied"
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('repeating_task_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false === $requestBody) {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
            return $response;
        }

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $isAdmin = $this->get('api_base.voter')->isAdmin();
        $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody, false, $loggedUser, $isAdmin);

        $repeatingTaskArray = $this->get('repeating_task_service')->getRepeatingTasks($processedFilterParams);
        $response = $response->setContent(json_encode($repeatingTaskArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
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
     *           "put": "/api/v1/entityName/id",
     *           "patch": "/api/v1/entityName/id",
     *           "delete": "/api/v1/entityName/id"
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
     *  output="API\CoreBundle\Entity\...entityName",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $repeatingTaskId
     * @return Response
     */
    public function getAction(int $repeatingTaskId): Response
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
     *           "put": "/api/v1/entityName/2",
     *           "patch": "/api/v1/entityName/2",
     *           "delete": "/api/v1/entityName/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Entity (POST)",
     *  input={"class"="API\CoreBundle\Entity\...entityName"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\...entityName"},
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
     */
    public function createAction(Request $request): Response
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
     *           "put": "/api/v1/entityName/2",
     *           "patch": "/api/v1/entityName/2",
     *           "delete": "/api/v1/entityName/2"
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
     *  input={"class"="API\CoreBundle\Entity\...entityName"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\...entityName"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $repeatingTaskId
     * @param Request $request
     * @return Response
     */
    public function updateAction(int $repeatingTaskId, Request $request): Response
    {
        // TODO: Implement updateAction() method.
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
     * @param int $repeatingTaskId
     *
     * @return Response
     */
    public function deleteAction(int $repeatingTaskId): Response
    {
        // TODO: Implement deleteAction() method.
    }
}
