<?php

namespace API\TaskBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class taskService
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
     * TagService constructor.
     *
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

}