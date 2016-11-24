<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class CompanyService
 *
 * @package API\TaskBundle\Services
 */
class CompanyService
{
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
     * Return Entity Response which includes all data about Entity and Links to update/partialUpdate/delete
     *
     * @param Company $entity
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