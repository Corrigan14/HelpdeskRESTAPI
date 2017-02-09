<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\InvoiceableItem;
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
     * ### Response ###
     *      {
     *       "data":
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
     *  resource = true,
     *  description="Create a new Invoiceable item Entity",
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
     * ### Response ###
     *      {
     *       "data":
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
     *  description="Update the Invoiceable item Entity",
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
     * ### Response ###
     *      {
     *       "data":
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
     *  description="Partially update the Invoiceable item Entity",
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

            $invoiceableItemArray = $this->get('invoiceable_item_service')->getAttributeResponse($invoiceableItem->getTask()->getId(), $invoiceableItem->getId(), $invoiceableItem->getUnit()->getId());
            return $this->json($invoiceableItemArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
