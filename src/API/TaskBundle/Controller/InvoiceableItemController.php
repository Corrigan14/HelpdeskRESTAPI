<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\InvoiceableItem;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\Unit;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InvoiceableItemController
 *
 * @package API\TaskBundle\Controller
 */
class InvoiceableItemController extends ApiBaseController
{

    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *         {
     *            "id": 6,
     *            "title": "Flour",
     *            "amount": "25.00",
     *            "unit_price": "1.00",
     *            "unit":
     *            {
     *               "id": 21,
     *               "title": "Kilogram",
     *               "shortcut": "Kg",
     *               "is_active": true
     *            }
     *         },
     *         {
     *            "id": 4,
     *            "title": "Keyboard",
     *            "amount": "2.00",
     *            "unit_price": "50.00",
     *            "unit":
     *            {
     *               "id": 22,
     *               "title": "Kus",
     *               "shortcut": "Ks",
     *               "is_active": true
     *            }
     *         },
     *         {
     *            "id": 5,
     *            "title": "Mouse",
     *            "amount": "5.00",
     *            "unit_price": "10.00",
     *            "unit":
     *            {
     *               "id": 22,
     *               "title": "Kus",
     *               "shortcut": "Ks",
     *               "is_active": true
     *            }
     *          }
     *        ]
     *     }
     *
     * @ApiDoc(
     *  description="Returns a list of Invoiceable item Entities For selected Task",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of Task"
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
     * @param int $taskId
     * @return Response
     * @internal param Request $request
     */
    public function listAction(int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $invoiceableItemsArray = $this->get('invoiceable_item_service')->getAttributesResponse($task);
        return $this->json($invoiceableItemsArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 4,
     *           "title": "Keyboard",
     *           "amount": "2.00",
     *           "unit_price": "50.00",
     *           "unit":
     *           {
     *              "id": 22,
     *              "title": "Kus",
     *              "shortcut": "Ks",
     *              "is_active": true
     *           }
     *        },
     *        "_links":
     *        {
     *           "put: all entity with unit": "/api/v1/task-bundle/task/38038/invoiceable-items/4/unit/22",
     *           "put: entity": "/api/v1/task-bundle/task/38038/invoiceable-items/4?unitId=22",
     *           "patch: all entity with unit": "/api/v1/task-bundle/task/38038/invoiceable-items/4?unitId=22",
     *           "patch: entity": "/api/v1/task-bundle/task/38038/invoiceable-items/4?unitId=22",
     *           "delete": "/api/v1/task-bundle/task/38038/invoiceable-items/4"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns an Invoiceable item Entity",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of Task"
     *     },
     *     {
     *       "name"="invoiceableItemId",
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
     *  output="API\TaskBundle\Entity\InvoiceableItem",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $invoiceableItemId
     * @return Response
     */
    public function getAction(int $taskId, int $invoiceableItemId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $invoiceableItem = $this->getDoctrine()->getRepository('APITaskBundle:InvoiceableItem')->find($invoiceableItemId);

        if (!$invoiceableItem instanceof InvoiceableItem) {
            return $this->createApiResponse([
                'message' => 'Invoiceable item with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $invoiceableItemArray = $this->get('invoiceable_item_service')->getAttributeResponse($taskId, $invoiceableItemId, $invoiceableItem->getUnit()->getId());
        return $this->json($invoiceableItemArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ],
     *           "invoiceableItems":
     *           [
     *              {
     *                 "id": 4,
     *                 "title": "Keyboard",
     *                 "amount": "2.00",
     *                 "unit_price": "50.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                 "id": 5,
     *                 "title": "Mouse",
     *                 "amount": "5.00",
     *                 "unit_price": "10.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *               },
     *            ],
     *            "canEdit": true,
     *            "follow": false
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Invoiceable item Entity. Returns Task Entity.",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of Task"
     *     },
     *     {
     *       "name"="unitId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of Unit"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\InvoiceableItem"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\InvoiceableItem"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $taskId
     * @param int $unitId
     * @return Response
     */
    public function createAction(Request $request, int $taskId, int $unitId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($unitId);
        if (!$unit instanceof Unit) {
            return $this->createApiResponse([
                'message' => 'Unit with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $requestData = $request->request->all();

        $invoiceableItem = new InvoiceableItem();
        $invoiceableItem->setTask($task);
        $invoiceableItem->setUnit($unit);

        return $this->updateInvoiceableItem($invoiceableItem, $requestData, true);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ],
     *           "invoiceableItems":
     *           [
     *              {
     *                 "id": 4,
     *                 "title": "Keyboard",
     *                 "amount": "2.00",
     *                 "unit_price": "50.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                 "id": 5,
     *                 "title": "Mouse",
     *                 "amount": "5.00",
     *                 "unit_price": "10.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *               },
     *            ],
     *            "canEdit": true,
     *            "follow": false
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  description="Update the Invoiceable item Entity. Returns Task Entity.",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of Task"
     *     },
     *     {
     *       "name"="invoiceableItemId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\InvoiceableItem"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\InvoiceableItem"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $invoiceableItemId
     * @param int|bool $unitId
     * @param Request $request
     * @return Response
     */
    public function updateAction(int $taskId, int $invoiceableItemId, Request $request, $unitId = false)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $invoiceableItem = $this->getDoctrine()->getRepository('APITaskBundle:InvoiceableItem')->find($invoiceableItemId);
        if (!$invoiceableItem instanceof InvoiceableItem) {
            return $this->createApiResponse([
                'message' => 'Invoiceable item with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if ($unitId) {
            $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($unitId);
            if (!$unit instanceof Unit) {
                return $this->createApiResponse([
                    'message' => 'Unit item with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $invoiceableItem->setUnit($unit);
        }

        $requestData = $request->request->all();
        return $this->updateInvoiceableItem($invoiceableItem, $requestData, false);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ],
     *           "invoiceableItems":
     *           [
     *              {
     *                 "id": 4,
     *                 "title": "Keyboard",
     *                 "amount": "2.00",
     *                 "unit_price": "50.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *              },
     *              {
     *                 "id": 5,
     *                 "title": "Mouse",
     *                 "amount": "5.00",
     *                 "unit_price": "10.00",
     *                 "unit":
     *                 {
     *                    "id": 22,
     *                    "title": "Kus",
     *                    "shortcut": "Ks",
     *                    "is_active": true
     *                  }
     *               },
     *            ],
     *            "canEdit": true,
     *            "follow": false
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  description="Partially update the Invoiceable item Entity. Returns Task Entity.",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of Task"
     *     },
     *     {
     *       "name"="invoiceableItemId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\InvoiceableItem"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\InvoiceableItem"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $taskId
     * @param int $invoiceableItemId
     * @param int|boolean $unitId
     * @param Request $request
     * @return Response
     */
    public function updatePartialAction(int $taskId, int $invoiceableItemId, Request $request, $unitId = false)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $invoiceableItem = $this->getDoctrine()->getRepository('APITaskBundle:InvoiceableItem')->find($invoiceableItemId);
        if (!$invoiceableItem instanceof InvoiceableItem) {
            return $this->createApiResponse([
                'message' => 'Invoiceable item with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if ($unitId) {
            $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($unitId);
            if (!$unit instanceof Unit) {
                return $this->createApiResponse([
                    'message' => 'Unit item with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $invoiceableItem->setUnit($unit);
        }

        $requestData = $request->request->all();
        return $this->updateInvoiceableItem($invoiceableItem, $requestData, false);
    }

    /**
     * @ApiDoc(
     *  description="Delete Invoiceable item Entity",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of Task"
     *     },
     *     {
     *       "name"="invoiceableItemId",
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
     * @param int $taskId
     * @param int $invoiceableItemId
     * @return Response
     */
    public function deleteAction(int $taskId, int $invoiceableItemId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $invoiceableItem = $this->getDoctrine()->getRepository('APITaskBundle:InvoiceableItem')->find($invoiceableItemId);
        if (!$invoiceableItem instanceof InvoiceableItem) {
            return $this->createApiResponse([
                'message' => 'Invoiceable item with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $this->getDoctrine()->getManager()->remove($invoiceableItem);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param InvoiceableItem $invoiceableItem
     * @param array $requestData
     * @param bool $create
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    private function updateInvoiceableItem(InvoiceableItem $invoiceableItem, $requestData, $create = false)
    {
        $allowedInvoiceableItemEntityParams = [
            'title',
            'amount',
            'unit_price'
        ];

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!in_array($key, $allowedInvoiceableItemEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for Invoiceable Item Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($invoiceableItem, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($invoiceableItem);
            $this->getDoctrine()->getManager()->flush();

            // Return task entity
            $task = $invoiceableItem->getTask();

            // Check if user can update selected task
            if ($this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
                $canEdit = true;
            } else {
                $canEdit = false;
            }

            $fullTaskEntity = $this->get('task_service')->getFullTaskEntity($task, $canEdit, $this->getUser()->getId());
            return $this->json($fullTaskEntity, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
