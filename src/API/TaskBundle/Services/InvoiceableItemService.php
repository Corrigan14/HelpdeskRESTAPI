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
     * @param int $id
     * @return array
     */
    public function getAttributeResponse(int $id): array
    {
        $entity = $this->em->getRepository('APITaskBundle:Unit')->getEntity($id);

        return [
            'data' => $entity[0],
            '_links' => $this->getEntityLinks($id),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getEntityLinks(int $id)
    {
        return [
            'put' => $this->router->generate('unit_update', ['id' => $id]),
            'patch' => $this->router->generate('unit_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('unit_delete', ['id' => $id]),
            'restore' => $this->router->generate('unit_restore', ['id' => $id]),
        ];
    }
}