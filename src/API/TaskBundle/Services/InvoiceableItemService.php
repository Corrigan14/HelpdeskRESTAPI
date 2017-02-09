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

        return $response;
    }

    /**
     * @param int $taskId
     * @param int $invoiceableItemId
     * @return array
     */
    public function getAttributeResponse(int $taskId, int $invoiceableItemId): array
    {
        $invoiceableItem = $this->em->getRepository('APITaskBundle:Unit')->getEntity($invoiceableItemId);

        return [
            'data' => $invoiceableItem[0],
            '_links' => $this->getEntityLinks($taskId, $invoiceableItemId),
        ];
    }

    /**
     * @param int $taskId
     * @param int $invoiceableItemId
     * @return array
     *
     */
    private function getEntityLinks(int $taskId, int $invoiceableItemId)
    {
        return [
            'put' => $this->router->generate('invoiceable_item_update', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId]),
            'patch' => $this->router->generate('unit_partial_update', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId]),
            'delete' => $this->router->generate('unit_delete', ['taskId' => $taskId, 'invoiceableItemId' => $invoiceableItemId]),
        ];
    }
}