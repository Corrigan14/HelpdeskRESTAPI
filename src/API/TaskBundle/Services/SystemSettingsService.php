<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Entity\SystemSettings;
use API\TaskBundle\Repository\SystemSettingsRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class SystemSettingsService
 *
 * @package API\TaskBundle\Services
 */
class SystemSettingsService
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
        $attributes = $this->em->getRepository('APITaskBundle:SystemSettings')->getAllEntities($page, $options);
        $count = $this->em->getRepository('APITaskBundle:SystemSettings')->countEntities($options);

        $response = [
            'data' => $attributes,
        ];

        $url = $this->router->generate('status_list');
        $limit = SystemSettingsRepository::LIMIT;
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
        $entity = $this->em->getRepository('APITaskBundle:SystemSettings')->getEntity($id);

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
            'put' => $this->router->generate('system_settings_update', ['id' => $id]),
            'patch' => $this->router->generate('system_settings_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('system_settings_delete', ['id' => $id]),
            'restore' => $this->router->generate('system_settings_restore', ['id' => $id]),
        ];
    }
}