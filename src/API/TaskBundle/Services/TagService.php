<?php

namespace API\TaskBundle\Services;

use API\TaskBundle\Entity\Tag;
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

    /**
     * Return all User's Tags + public Tags
     *
     * @param int $id
     * @return array
     */
    public function getTagsResponse(int $id)
    {
        return[
          'data' => $this->em->getRepository('APITaskBundle:Tag')->getAllTags($id),
        ];
    }

    /**
     * Return Tag Response which includes all data about Tag Entity and Links to update/partialUpdate/delete
     *
     * @param Tag $tag
     *
     * @return array
     */
    public function getTagResponse(Tag $tag)
    {
        return [
            'data'   => $tag ,
            '_links' => $this->getTagLinks($tag->getId()) ,
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getTagLinks(int $id)
    {
        return [
            'put'    => $this->router->generate('tag_update' , ['id' => $id]) ,
            'patch'  => $this->router->generate('tag_partial_update' , ['id' => $id]) ,
            'delete' => $this->router->generate('tag_delete' , ['id' => $id]) ,
        ];
    }

}