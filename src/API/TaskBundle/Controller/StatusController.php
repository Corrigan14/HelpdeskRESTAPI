<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\Status;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StatusController
 *
 * @package API\TaskBundle\Controller
 */
class StatusController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *            "id": "1",
     *            "title": "New",
     *            "is_active": true
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/status?page=1",
     *           "first": "/api/v1/task-bundle/status?page=1",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/status?page=2",
     *           "last": "/api/v1/task-bundle/status?page=3"
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request)
    {
        if (!$this->get('status_voter')->isGranted(VoteOptions::LIST_STATUSES)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;

        $statusRepository = $this->getDoctrine()->getRepository('APITaskBundle:Status');

        return $this->json($this->get('api_base.service')->getEntitiesResponse($statusRepository, $page, 'status_list'), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "New",
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/status/id",
     *           "patch": "/api/v1/task-bundle/status/id",
     *           "delete": "/api/v1/task-bundle/status/id"
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
     *  output="API\TaskBundle\Entity\Status",
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
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            return $this->notFoundResponse();
        }

        if (!$this->get('status_voter')->isGranted(VoteOptions::SHOW_STATUS, $status)) {
            return $this->accessDeniedResponse();
        }

        $statusArray = $this->get('api_base.service')->getEntityResponse($status, 'status');

        return $this->createApiResponse($statusArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "New",
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/status/id",
     *           "patch": "/api/v1/task-bundle/status/id",
     *           "delete": "/api/v1/task-bundle/status/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Entity (POST)",
     *  input={"class"="API\TaskBundle\Entity\Status"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Status"},
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
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request)
    {
        if (!$this->get('status_voter')->isGranted(VoteOptions::CREATE_STATUS)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $status = new Status();

        return $this->updateStatus($status, $requestData, true);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "New",
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/status/id",
     *           "patch": "/api/v1/task-bundle/status/id",
     *           "delete": "/api/v1/task-bundle/status/id"
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
     *  input={"class"="API\TaskBundle\Entity\Status"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Status"},
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
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request)
    {
        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            return $this->notFoundResponse();
        }

        if (!$this->get('status_voter')->isGranted(VoteOptions::UPDATE_STATUS, $status)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateStatus($status, $requestData);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "New",
     *           "is_active": true
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/status/id",
     *           "patch": "/api/v1/task-bundle/status/id",
     *           "delete": "/api/v1/task-bundle/status/id"
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
     *  input={"class"="API\TaskBundle\Entity\Status"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Status"},
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
     * @throws \LogicException
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            return $this->notFoundResponse();
        }

        if (!$this->get('status_voter')->isGranted(VoteOptions::UPDATE_STATUS, $status)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateStatus($status, $requestData);
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
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     * @throws \LogicException
     */
    public function deleteAction(int $id)
    {
        $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($id);

        if (!$status instanceof Status) {
            return $this->notFoundResponse();
        }

        if (!$this->get('status_voter')->isGranted(VoteOptions::DELETE_STATUS, $status)) {
            return $this->accessDeniedResponse();
        }

        $status->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($status);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::UNACITVATE_MESSAGE,
        ], StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param Status $status
     * @param array $requestData
     * @param bool $create
     *
     * @return Response|JsonResponse
     * @throws \LogicException
     */
    private function updateStatus(Status $status, $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($status, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($status);
            $this->getDoctrine()->getManager()->flush();

            return $this->createApiResponse($this->get('api_base.service')->getEntityResponse($status, 'status'), $statusCode);
        }

        return $this->invalidParametersResponse();
    }
}
