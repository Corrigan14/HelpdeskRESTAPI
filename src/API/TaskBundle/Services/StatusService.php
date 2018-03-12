<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Repository\StatusRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class StatusService
 *
 * @package API\TaskBundle\Services
 */
class StatusService
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
        $responseData = $this->em->getRepository('APITaskBundle:Status')->getAllEntities($page, $options);

        $response['data'] = $responseData['array'];

        $url = $this->router->generate('status_list');
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
     * @return array
     */
    public function getListOfExistedStatuses():array
    {
        return $this->em->getRepository('APITaskBundle:Status')->getAllStatusEntitiesWithIdAndTitle();
    }

    /**
     * @param $date
     * @return array
     */
    public function getListOfAllStatuses($date): array
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->em->getRepository('APITaskBundle:Status');
        return $statusRepository->getAllStatusEntitiesForSelectionLists($date);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getAttributeResponse(int $id): array
    {
        $entity = $this->em->getRepository('APITaskBundle:Status')->getEntity($id);

        return [
            'data' => $entity,
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
            'put' => $this->router->generate('status_update', ['id' => $id]),
            'inactivate' => $this->router->generate('status_inactivate', ['id' => $id]),
            'restore' => $this->router->generate('status_restore', ['id' => $id]),
        ];
    }
}