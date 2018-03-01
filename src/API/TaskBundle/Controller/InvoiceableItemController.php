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
     *  description="Returns a list of ALL Invoice-able item Entities For a selected Task",
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
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $taskId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @internal param Request $request
     */
    public function listAction(int $taskId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('invoiceable_item_list', ['taskId' => $taskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        // Check if user can view a selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $invoiceableItemsArray = $this->get('invoiceable_item_service')->getAttributesResponse($task);

        $response = $response->setContent(json_encode($invoiceableItemsArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getAction(int $taskId, int $invoiceableItemId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('invoiceable_item', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        // Check if user can view a selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $invoiceableItem = $this->getDoctrine()->getRepository('APITaskBundle:InvoiceableItem')->find($invoiceableItemId);

        if (!$invoiceableItem instanceof InvoiceableItem) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Invoiceable item with requested Id does not exist!']));
            return $response;
        }

        $invoiceableItemArray = $this->get('invoiceable_item_service')->getAttributeResponse($taskId, $invoiceableItemId, $invoiceableItem->getUnit()->getId());

        $response = $response->setContent(json_encode($invoiceableItemArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
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
     *           "delete": "/api/v1/task-bundle/task/38038/invoiceable-items/4"
     *         }
     *    }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Invoiceable item Entity. Returns Created Entity.",
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function createAction(Request $request, int $taskId, int $unitId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('invoiceable_item_create', ['taskId' => $taskId, 'unitId' => $unitId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($unitId);
        if (!$unit instanceof Unit) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Unit with requested Id does not exist!']));
            return $response;
        }

        $invoiceableItem = new InvoiceableItem();
        $invoiceableItem->setTask($task);
        $invoiceableItem->setUnit($unit);

        $options = [
            'task' => $taskId,
            'unit' => $unitId
        ];

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateInvoiceableItem($invoiceableItem, $requestBody, true, $locationURL, $options);
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
     *           "delete": "/api/v1/task-bundle/task/38038/invoiceable-items/4"
     *         }
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
     *      400 ="Bad request",
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function updateAction(int $taskId, int $invoiceableItemId, Request $request, $unitId = false)
    {
        // JSON API Response - Content type and Location settings
        if ($unitId) {
            $locationURL = $this->generateUrl('invoiceable_item_update_unit', ['taskId' => $taskId, 'unitId' => $unitId, 'invoiceableItemId' => $invoiceableItemId]);
        } else {
            $locationURL = $this->generateUrl('invoiceable_item_update', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId]);
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $invoiceableItem = $this->getDoctrine()->getRepository('APITaskBundle:InvoiceableItem')->find($invoiceableItemId);
        if (!$invoiceableItem instanceof InvoiceableItem) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Invoiceable Item with requested Id does not exist!']));
            return $response;
        }

        if($invoiceableItem->getTask()->getId() !== $taskId){
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Invoiceable Item with requested Id does not belong to the requested Task!']));
            return $response;
        }

        if ($unitId) {
            $unit = $this->getDoctrine()->getRepository('APITaskBundle:Unit')->find($unitId);
            if (!$unit instanceof Unit) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Unit with requested Id does not exist!']));
                return $response;
            }
            $invoiceableItem->setUnit($unit);
            $sentUnit = $unitId;
        } else {
            $sentUnit = $invoiceableItem->getUnit()->getId();
        }

        $options = [
            'task' => $taskId,
            'unit' => $sentUnit
        ];
        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateInvoiceableItem($invoiceableItem, $requestBody, false, $locationURL, $options);
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
     *      400 ="Bad request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $taskId
     * @param int $invoiceableItemId
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(int $taskId, int $invoiceableItemId)
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('invoiceable_item_delete', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);
        if (!$task instanceof Task) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Task with requested Id does not exist!']));
            return $response;
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $invoiceableItem = $this->getDoctrine()->getRepository('APITaskBundle:InvoiceableItem')->find($invoiceableItemId);
        if (!$invoiceableItem instanceof InvoiceableItem) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Invoiceable Item with requested Id does not exist!']));
            return $response;
        }

        if($invoiceableItem->getTask()->getId() !== $taskId){
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Invoiceable Item with requested Id does not belong to the requested Task!']));
            return $response;
        }

        $this->getDoctrine()->getManager()->remove($invoiceableItem);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::DELETED_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::DELETED_MESSAGE]));
        return $response;
    }

    /**
     * @param InvoiceableItem $invoiceableItem
     * @param array $requestData
     * @param bool $create
     * @param $locationURL
     * @param array $options
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    private function updateInvoiceableItem(InvoiceableItem $invoiceableItem, $requestData, $create = false, $locationURL, array $options): Response
    {
        $allowedInvoiceableItemEntityParams = [
            'title',
            'amount',
            'unit_price'
        ];

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        if (false !== $requestData) {
            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
            }

            foreach ($requestData as $key => $value) {
                if (!in_array($key, $allowedInvoiceableItemEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for Invoiceable Item Entity!']));
                    return $response;
                }
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            $errors = $this->get('entity_processor')->processEntity($invoiceableItem, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($invoiceableItem);
                $this->getDoctrine()->getManager()->flush();

                $invoiceableItemArray = $this->get('invoiceable_item_service')->getAttributeResponse($options['task'], $invoiceableItem->getId(), $options['unit']);

                $response = $response->setContent(json_encode($invoiceableItemArray));
                $response = $response->setStatusCode($statusCode);
                return $response;
            }
            $data = [
                'errors' => $errors,
                'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
            ];
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode($data));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;
    }
}
