<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\User;
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
     * @param int $userId
     * @return array
     */
    public function getListOfUsersTags(int $userId):array
    {
        return $this->em->getRepository('APITaskBundle:Tag')->getAllTagEntitiesWithIdAndTitle($userId);
    }
}