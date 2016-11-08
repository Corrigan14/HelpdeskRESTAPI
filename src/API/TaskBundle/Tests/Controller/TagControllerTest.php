<?php

namespace API\TaskBundle\Tests\Controller;

use API\CoreBundle\Tests\Controller\ApiTestCase;
use API\TaskBundle\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/tags';

    /**
     * GET LIST - success
     */
    public function testListSuccess()
    {
        parent::testListSuccess();
    }

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
        parent::testListErrors();
    }

    /**
     * GET SINGLE - success
     */
    public function testGetSingleSuccess()
    {
        // TODO: Implement testGetSingleSuccess() method.
    }

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        // TODO: Implement testGetSingleErrors() method.
    }

    /**
     * POST SINGLE - success
     */
    public function testPostSingleSuccess()
    {
        // TODO: Implement testPostSingleSuccess() method.
    }

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        // TODO: Implement testPostSingleErrors() method.
    }

    /**
     * UPDATE SINGLE - success
     */
    public function testUpdateSingleSuccess()
    {
        // TODO: Implement testUpdateSingleSuccess() method.
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        // TODO: Implement testUpdateSingleErrors() method.
    }

    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess()
    {
        // TODO: Implement testDeleteSingleSuccess() method.
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
        // TODO: Implement testDeleteSingleErrors() method.
    }

    /**
     * Return Base URL
     */
    public function getBaseUrl()
    {
        return self::BASE_URL;
    }

    /**
     * Return a signle entity from db for testing CRUD
     *
     * @return object
     */
    public function findOneEntity()
    {
        return $this->em->getRepository('APITaskBundle:Tag')->findOneBy([]);
    }

    /**
     * Create and return a signle entity from db for testing CRUD
     *
     * @return object
     */
    public function createEntity()
    {
        $entity = $this->findOneEntity();
        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);
        $admin = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        if(null === $entity)
        {
            $tag = new Tag();
            $tag->setTitle('TestTag');
            $tag->setColor('111111');
            $tag->setPublic(true);
            $tag->setCreatedBy($admin);

            return $tag;
        }
    }
}
