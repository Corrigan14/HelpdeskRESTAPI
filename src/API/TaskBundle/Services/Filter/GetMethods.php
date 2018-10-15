<?php

namespace API\TaskBundle\Services\Filter;


use API\TaskBundle\Services\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class GetMethods
 *
 * @package API\TaskBundle\Services
 */
class GetMethods
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
     * @param int $loggedUserId
     * @param array $options
     * @return array
     */
    public function getFiltersResponse(int $loggedUserId, array $options): array
    {
        $responseData = $this->em->getRepository('APITaskBundle:Filter')->getAllEntities($loggedUserId, $options);

        $response = [
            'data' => $responseData['array'],
        ];

        $url = $this->router->generate('filter_list');
        $limit = $options['limit'];
        $filters = $this->getParamsForUrl($options);

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $options['page'], $count, $filters);

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
    private function getFilterLinks(int $id):array
    {
        return [
            'update filter' => $this->router->generate('filter_update', ['id' => $id]),
            'inactivate' => $this->router->generate('filter_inactivate', ['id' => $id]),
            'restore' => $this->router->generate('filter_restore', ['id' => $id]),
            'set logged users remembered filter' => $this->router->generate('filter_set_user_remembered', ['id' => $id]),
            'get logged users remembered filter' => $this->router->generate('filter_get_user_remembered'),
            'remove logged users remembered filter' => $this->router->generate('filter_reset_user_remembered'),
        ];
    }

    /**
     * @param array $processedFilterParams
     * @return array
     */
    private function getParamsForUrl(array $processedFilterParams): array
    {
        $order = $processedFilterParams['order'];
        $isActive = $processedFilterParams['isActive'];
        $public = $processedFilterParams['public'];
        $report = $processedFilterParams['report'];
        $project = $processedFilterParams['project'];
        $default = $processedFilterParams['default'];

        $filtersForUrl = [
            'isActive' => '&isActive=' . $isActive,
            'order' => '&order=' . $order,
            'public' => '&public=' . $public,
            'report' => '&report=' . $report,
            'project' => '&project=' . $project,
            'default' => '&default=' . $default
        ];

        return $filtersForUrl;
    }
}