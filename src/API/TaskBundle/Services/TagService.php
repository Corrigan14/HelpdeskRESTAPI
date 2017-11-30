<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Repository\TagRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class TagService
 *
 * @package API\TaskBundle\Services
 */
class TagService
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
     * Return Tags Response which includes Data and Links and Pagination
     *
     * @param int $page
     * @param array $options
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getAttributesResponse(int $page, array $options): array
    {
        $responseData = $this->em->getRepository('APITaskBundle:Tag')->getAllEntities($page, $options);

        $response = [
            'data' => $responseData['array'],
        ];

        $url = $this->router->generate('tag_list');
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
     */
    public function getAttributeResponse(int $id): array
    {
        $entity = $this->em->getRepository('APITaskBundle:Tag')->getEntity($id);

        return [
            'data' => $entity,
            '_links' => $this->getEntityLinks($id),
        ];
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getListOfUsersTags(int $userId):array
    {
        return $this->em->getRepository('APITaskBundle:Tag')->getAllTagEntitiesWithIdAndTitleAndColor($userId);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getEntityLinks(int $id)
    {
        return [
            'put' => $this->router->generate('tag_update', ['id' => $id]),
            'patch' => $this->router->generate('tag_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('tag_delete', ['id' => $id])
        ];
    }
}