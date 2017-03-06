<?php

namespace API\TaskBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class SmtpService
 * @package API\TaskBundle\Services
 */
class SmtpService
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
     */
    public function getAttributesResponse(string $order): array
    {
        $attributes = $this->em->getRepository('APITaskBundle:Smtp')->getAllEntities($order);
        $count = $this->em->getRepository('APITaskBundle:Smtp')->countEntities();

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
        $entity = $this->em->getRepository('APITaskBundle:Smtp')->getEntity($id);

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
            'put' => $this->router->generate('smtp_update', ['id' => $id]),
            'patch' => $this->router->generate('smtp_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('smtp_delete', ['id' => $id])
        ];
    }
}