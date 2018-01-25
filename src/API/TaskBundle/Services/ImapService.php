<?php

namespace API\TaskBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class ImapService
 *
 * @package API\TaskBundle\Services
 */
class ImapService
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
     * Return SMTP Response
     *
     * @param string $order
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getAttributesResponse(string $order): array
    {
        $attributes = $this->em->getRepository('APITaskBundle:Imap')->getAllEntities($order);
        $count = $this->em->getRepository('APITaskBundle:Imap')->countEntities();

        $response = [
            'data' => $attributes,
        ];

        $pagination = [
            '_links' => [],
            'total' => $count
        ];

        return array_merge($response, $pagination);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getAttributeResponse(int $id): array
    {
        $entity = $this->em->getRepository('APITaskBundle:Imap')->getEntity($id);

        return [
            'data' => $entity[0],
            '_links' => $this->getEntityLinks($id, $entity[0]['project']['id']),
        ];
    }

    /**
     * @param int $id
     *
     * @param int $projectId
     * @return array
     */
    private function getEntityLinks(int $id, int $projectId):array
    {
        return [
            'put' => $this->router->generate('imap_update', ['id' => $id]),
            'put: project' => $this->router->generate('imap_update_with_project', ['id' => $id, 'projectId' => $projectId]),
            'delete' => $this->router->generate('imap_delete', ['id' => $id])
        ];
    }
}