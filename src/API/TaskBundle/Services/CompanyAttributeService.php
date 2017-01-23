<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Repository\CompanyAttributeRepository;
use API\TaskBundle\Services\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class CompanyAttributeService
 * @package API\TaskBundle\Services
 */
class CompanyAttributeService
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
     * Return Companies Response which includes Data and Links and Pagination
     *
     * @param int $page
     * @param array $options
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getAttributesResponse(int $page, array $options): array
    {
        $attributes = $this->em->getRepository('APITaskBundle:CompanyAttribute')->getAllEntities($page, $options);
        $count = $this->em->getRepository('APITaskBundle:CompanyAttribute')->countEntities($options);

        $response = [
            'data' => $attributes,
        ];

        $url = $this->router->generate('company_attribute_list');
        $limit = CompanyAttributeRepository::LIMIT;
        $filters = $options['filtersForUrl'];

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getAttributeResponse(int $id): array
    {
        $entity = $this->em->getRepository('APITaskBundle:CompanyAttribute')->getEntity($id);

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
            'put' => $this->router->generate('company_attribute_update', ['id' => $id]),
            'patch' => $this->router->generate('company_attribute_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('company_attribute_delete', ['id' => $id]),
        ];
    }
}