<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Entity\Task;
use API\TaskBundle\Repository\UnitRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class InvoiceableItemService
 *
 * @package API\TaskBundle\Services
 */
class InvoiceableItemService
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;


    /**
     * UserService constructor.
     *
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * Return Response which includes Data and Links and Pagination
     *
     * @param Task $task
     * @return array
     */
    public function getAttributesResponse(Task $task): array
    {
        $attributes = $this->em->getRepository('APITaskBundle:InvoiceableItem')->getAllEntities($task);

        $response = [
            'data' => $attributes,
        ];

        $pagination = [
            '_links' => [],
        ];

        return array_merge($response, $pagination);
    }

    /**
     * @param int $taskId
     * @param int $invoiceableItemId
     * @param int $unitId
     * @return array
     */
    public function getAttributeResponse(int $taskId, int $invoiceableItemId, int $unitId): array
    {
        $invoiceableItem = $this->em->getRepository('APITaskBundle:InvoiceableItem')->getEntity($invoiceableItemId);

        return [
            'data' => $invoiceableItem[0],
            '_links' => $this->getEntityLinks($taskId, $invoiceableItemId, $unitId),
        ];
    }

    /**
     * @param int $taskId
     * @param int $invoiceableItemId
     * @param int $unitId
     * @return array
     */
    private function getEntityLinks(int $taskId, int $invoiceableItemId, int $unitId)
    {
        return [
            'put: all entity with unit' => $this->router->generate('invoiceable_item_update_unit', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId, 'unitId' => $unitId]),
            'put: entity' => $this->router->generate('invoiceable_item_update', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId, 'unitId' => $unitId]),
            'patch: all entity with unit' => $this->router->generate('invoiceable_item_partial_update', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId, 'unitId' => $unitId]),
            'patch: entity' => $this->router->generate('invoiceable_item_partial_update', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId, 'unitId' => $unitId]),
            'delete' => $this->router->generate('invoiceable_item_delete', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId]),
        ];
    }
}