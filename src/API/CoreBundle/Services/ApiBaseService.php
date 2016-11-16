<?php

namespace API\CoreBundle\Services;

use API\CoreBundle\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class ApiBaseService
 *
 * @package API\CoreBundle\Services
 */
class ApiBaseService
{
    const PAGINATION_LIMIT = 10;


    /** @var Router */
    protected $router;

    /**
     * ApiBaseService constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Return all Entities which includes Data and Links
     *
     * @param RepositoryInterface $entityRepository
     * @param int $page
     * @param string $routeName
     * @param array $options
     *
     * @return array
     */
    public function getEntitiesResponse(RepositoryInterface $entityRepository, int $page, string $routeName, array $options = [])
    {
        $entities = $entityRepository->getAllEntities($page, $options);

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
     * @param object $entity
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