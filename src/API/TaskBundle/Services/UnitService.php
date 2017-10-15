<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Repository\UnitRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class UnitService
 *
 * @package API\TaskBundle\Services
 */
class UnitService
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
     * @param int $page
     * @param array $options
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getAttributesResponse(int $page, array $options): array
    {
        $responseData = $this->em->getRepository('APITaskBundle:Unit')->getAllEntities($page, $options);

        $response = [
            'data' => $responseData['array'],
        ];

        $url = $this->router->generate('unit_list');
        $limit = $options['limit'];
        $filters = $options['filtersForUrl'];

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = count($responseData['array']);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getAttributeResponse(int $id): array
    {
        $entity = $this->em->getRepository('APITaskBundle:Unit')->getEntity($id);

        return [
            'data' => $entity,
            '_links' => $this->getEntityLinks($id),
        ];
    }

    public function getListOfAllUnits()
    {
        return $this->em->getRepository('APITaskBundle:Unit')->getListOfUnitsWithShortcutAndId();
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