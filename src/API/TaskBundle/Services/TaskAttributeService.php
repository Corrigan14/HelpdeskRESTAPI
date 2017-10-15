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
        $responseData = $this->em->getRepository('APITaskBundle:TaskAttribute')->getAllEntities($page, $options);

        $response = [
            'data' => $responseData['array'],
        ];

        $url = $this->router->generate('task_attribute_list');
        $limit = $options['limit'];
        $filters = $options['filtersForUrl'];

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = count($responseData['array']);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filters);

        return array_merge($response, $pagination);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getTaskAttributeResponse(int $id): array
    {
        $entity = $this->em->getRepository('APITaskBundle:TaskAttribute')->getEntity($id);

        return [
            'data' => $entity,
            '_links' => $this->getLinks($id),
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