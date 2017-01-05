<?php

namespace API\TaskBundle\Services;


use API\TaskBundle\Repository\FilterRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class FilterService
 *
 * @package API\TaskBundle\Services
 */
class FilterService
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;


    /**
     * ProjectService constructor.
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
     * Return Filters Response which includes Data and Links and Pagination
     *
     * @param int $page
     * @param array $options
     * @return array
     */
    public function getFiltersResponse(int $page, array $options):array
    {
        $filters = $this->em->getRepository('APITaskBundle:Filter')->getAllEntities($page, $options);
        $count = $this->em->getRepository('APITaskBundle:Filter')->countEntities($options);

        $response = [
            'data' => $filters,
        ];

        $url = $this->router->generate('filter_list');
        $limit = FilterRepository::LIMIT;
        $filters = $options['filtersForUrl'];

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }
}