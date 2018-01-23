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
        $responseData = $this->em->getRepository('APICoreBundle:Company')->getAllEntities($page, $options);

        $response = [
            'data' => $responseData['array'],
        ];

        $url = $this->router->generate('company_list');
        $limit = $options['limit'];
        $filters = $options['filtersForUrl'];

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param int $id
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getCompanyResponse(int $id): array
    {
        $entity = $this->em->getRepository('APICoreBundle:Company')->getEntity($id);

        return [
            'data' => $entity,
            '_links' => $this->getEntityLinks($id)
        ];
    }

    /**
     * @param string|bool $term
     * @param int $page
     * @param string|bool $isActive
     * @param array $filtersForUrl
     * @param string $order
     * @param int $limit
     * @return array
     */
    public function getCompaniesSearchResponse($term, int $page, $isActive, array $filtersForUrl, string $order, int $limit): array
    {
        $responseData = $this->em->getRepository('APICoreBundle:Company')->getCompaniesSearch($term, $page, $isActive, $order, $limit);

        $response = [
            'data' => $responseData['array'],
        ];

        $url = $this->router->generate('company_search');
        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filtersForUrl);

        return array_merge($response, $pagination);
    }

    /**
     * @return array
     */
    public function getListOfAllCompanies(): array
    {
        return $this->em->getRepository('APICoreBundle:Company')->getAllCompanyEntitiesWithIdAndTitle();
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getEntityLinks(int $id): array
    {
        return [
            'put' => $this->router->generate('company_update', ['id' => $id]),
            'delete' => $this->router->generate('company_delete', ['id' => $id]),
        ];
    }
}