<?php

namespace API\CoreBundle\Services;

use API\CoreBundle\Repository\CompanyRepository;
use API\TaskBundle\Services\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class CompanyService
 * @package API\CoreBundle\Services
 */
class CompanyService
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
    public function getCompaniesResponse(int $page, array $options): array
    {
        $companies = $this->em->getRepository('APICoreBundle:Company')->getAllEntities($page, $options);
        $count = $this->em->getRepository('APICoreBundle:Company')->countEntities($options);

        $response = [
            'data' => $companies,
        ];

        $url = $this->router->generate('company_list');
        $limit = CompanyRepository::LIMIT;
        $filters = $options['filtersForUrl'];

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getCompanyResponse(int $id): array
    {
        $entity = $this->em->getRepository('APICoreBundle:Company')->getEntity($id);

        return [
            'data' => $entity[0],
            '_links' => $this->getEntityLinks($id),
        ];
    }

    /**
     * @return array
     */
    public function getListOfAllCompanies():array
    {
        return $this->em->getRepository('APICoreBundle:Company')->getAllCompanyEntitiesWithIdAndTitle();
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getEntityLinks(int $id)
    {
        return [
            'put' => $this->router->generate('company_update', ['id' => $id]),
            'patch' => $this->router->generate('company_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('company_delete', ['id' => $id]),
        ];
    }
}