<?php

namespace API\CoreBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class ApiBaseService
 *
 * @package API\CoreBundle\Services
 */
class ApiBaseService
{
    const PAGINATION_LIMIT = 10;
    /**
     * @var EntityManager
     */
    protected $em;

    /** @var Router */
    protected $router;

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
     * Return all Entities which includes Data and Links
     *
     * @param array $options
     * @param int $page
     * @param string $entityRepository
     * @param string $routeName
     * @return array
     */
    public function getEntitiesResponse($options, int $page, string $entityRepository, string $routeName)
    {
        $entityRepository = $this->em->getRepository($entityRepository);
        $entities = $entityRepository->getAllEntities($options, $page);

        $response = [
            'data' => $entities
        ];

        $pagination = HateoasHelper::getPagination(
            $this->router->generate($routeName),
            $page,
            $entityRepository->countEntities(),
            self::PAGINATION_LIMIT
        );

        return array_merge($response,$pagination);
    }

    /**
     * Return Entity Response which includes all data about Entity and Links to update/partialUpdate/delete
     *
     * @param $entity
     * @param string $entityName
     *
     * @return array
     */
    public function getEntityResponse($entity, string $entityName)
    {
        return [
            'data' => $entity,
            '_links' => $this->getEntityLinks($entity->getId(), $entityName),
        ];
    }

    /**
     * @param int $id
     *
     * @param string $entityName
     * @return array
     */
    private function getEntityLinks(int $id, string $entityName)
    {
        return [
            'put' => $this->router->generate($entityName.'_update', ['id' => $id]),
            'patch' => $this->router->generate($entityName.'_partial_update', ['id' => $id]),
            'delete' => $this->router->generate($entityName.'_delete', ['id' => $id]),
        ];
    }

}