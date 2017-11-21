<?php

namespace API\TaskBundle\Services;


use API\TaskBundle\Entity\Filter;
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getFiltersResponse(int $page, array $options): array
    {
        $responseData = $this->em->getRepository('APITaskBundle:Filter')->getAllEntities($page, $options);

        $response = [
            'data' => $responseData['array'],
        ];

        $url = $this->router->generate('filter_list');
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
    public function getFilterResponse(int $id): array
    {
        $filter = $this->em->getRepository('APITaskBundle:Filter')->getFilterEntity($id);

        return [
            'data' => $filter,
            '_links' => $this->getFilterLinks($id),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getFilterLinks(int $id)
    {
        return [
            'put' => $this->router->generate('filter_update', ['id' => $id]),
            'patch' => $this->router->generate('filter_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('filter_delete', ['id' => $id]),
        ];
    }
}