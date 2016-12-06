<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskAttributeController
 *
 * @package API\TaskBundle\Controller
 */
class TaskAttributeController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *            "id": "1",
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options": null
     *            "is_active": true
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/task-attributes?page=1",
     *           "first": "/api/v1/task-bundle/task-attributes?page=1",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/task-attributes?page=2",
     *           "last": "/api/v1/task-bundle/task-attributes?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Entities",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE attributes if this param is TRUE, only INACTIVE attributes if param is FALSE"
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request)
    {
        if (!$this->get('task_attribute_voter')->isGranted(VoteOptions::LIST_TASK_ATTRIBUTES)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;
        $isActive = $request->get('isActive') ?: 'all';

        $options['isActive'] = $isActive;

        return $this->json($this->get('task_attribute_service')->getTaskAttributesResponse($page, $options), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options": null
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns the Task Attribute Entity",
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
     *  output="API\TaskBundle\Entity\TaskAttribute",
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
     *            "id": "1",
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options": null
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Task Attribute Entity",
     *  input={"class"="API\TaskBundle\Entity\TaskAttribute"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\TaskAttribute"},
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
     *            "id": "1",
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options": null
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the Task Attribute Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\TaskAttribute"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\TaskAttribute"},
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
     *            "id": "1",
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options": null
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the Task Attribute Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\TaskAttribute"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\TaskAttribute"},
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
     *  description="Delete the Task Attribute Entity",
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

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "1",
     *            "title": "Input task additional attribute",
     *            "type": "input"
     *            "options": null
     *            "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task-attributes/id",
     *           "patch": "/api/v1/task-bundle/task-attributes/id",
     *           "delete": "/api/v1/task-bundle/task-attributes/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Restore the Task Attribute Entity",
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
     *  output={"class"="API\TaskBundle\Entity\TaskAttribute"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Task Attribute Entity",
     *  }
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function restoreAction(int $id, Request $request)
    {
        // TODO: Implement updatePartialAction() method.
    }
}
