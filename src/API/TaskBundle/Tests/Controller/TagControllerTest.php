<?php

namespace API\TaskBundle\Tests\Controller;

use API\CoreBundle\Tests\Controller\ApiTestCase;
use API\TaskBundle\Entity\Tag;

/**
 * Class TagControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class TagControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/tags';

    /**
     * Return Base URL
     */
    public function getBaseUrl()
    {
        return self::BASE_URL;
    }

    /**
     * Return a single entity from db for testing CRUD
     *
     * @return object
     */
    public function findOneEntity()
    {
        return $this->em->getRepository('APITaskBundle:Tag')->findOneBy([]);
    }

    /**
     * Create and return a single entity from db for testing CRUD
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

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        // TODO: Implement removeTestEntity() method.
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        // TODO: Implement returnPostTestData() method.
    }

    /**
     * Return Update data
     *
     * @return array
     */
    public function returnUpdateTestData()
    {
        // TODO: Implement returnUpdateTestData() method.
    }
}
