<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Repository\TaskAttributeRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class TaskAttributeService
 *
 * @package API\TaskBundle\Services
 */
class TaskAttributeService
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;


    /**
     * TaskAttributeService constructor.
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
     * Return TaskAttribute Response  which includes Data and Links and Pagination
     *
     * @param int $page
     *
     * @param array $options
     * @return array
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getTaskAttributesResponse(int $page, array $options):array
    {
        $attributes = $this->em->getRepository('APITaskBundle:TaskAttribute')->getAllEntities($page, $options);
        $count = $this->em->getRepository('APITaskBundle:TaskAttribute')->countEntities($options);

        $response = [
            'data' => $attributes,
        ];

        $url = $this->router->generate('task_attribute_list');
        $limit = TaskAttributeRepository::LIMIT;
        $filters = $options['filtersForUrl'];

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * Return TaskAttribute Response which includes all data about TaskAttribute Entity and Links to update/partialUpdate/delete
     *
     * @param TaskAttribute $taskAttribute
     * @return array
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function getTaskAttributeResponse(TaskAttribute $taskAttribute):array
    {
        return [
            'data' => $taskAttribute,
            '_links' => $this->getLinks($taskAttribute->getId()),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    private function getLinks(int $id):array
    {
        return [
            'put' => $this->router->generate('task_attribute_update', ['id' => $id]),
            'patch' => $this->router->generate('task_attribute_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('task_attribute_delete', ['id' => $id]),
        ];
    }
}