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
    const BASE_URL = '/api/v1/tasks/tags';

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        parent::testGetSingleErrors();

        // Try to load Entity of another user: 403 ACCESS DENIED
    }

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        parent::testPostSingleErrors();

        // Try to create PUBLIC Tag with ROLE_USER [code 403]

        // Try to create Tag with invalid parameter TITLE (title has to be uniqe) [code 409]

        // Try to create Tag with invalid parameter COLOR (color is required) [code 409]
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        parent::testUpdateSingleErrors();

        // Try to update Tag to PUBLIC with ROLE_USER [code 403]

        // Try to update Tag with not unique parameter TITLE (title has to be uniqe) [code 409]
    }

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
        /** @var Tag $tag */
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Test Public Tag'
        ]);

        if (null !== $tag) {
            return $tag;
        }

        $tagArray = $this->createEntity();

        return $this->em->getRepository('APITaskBundle:Tag')->find($tagArray['id']);
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return object
     */
    public function createEntity()
    {
        $this->getClient(true)->request('POST', $this->getBaseUrl(),
            ['title' => 'Test Public Tag', 'color' => '111111', 'public' => true],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(201, $this->getClient()->getResponse()->getStatusCode());

        // Check if Entity was created
        $createdTag = json_decode($this->getClient()->getResponse()->getContent(), true);
        $createdTag = $createdTag['data'];
        $this->assertTrue(array_key_exists('id', $createdTag));

        return $createdTag;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeTestTag('Test Public Tag in POST');
        $this->removeTestTag('Test Public Tag in UPDATE');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'title' => 'Test Public Tag in POST', 'color' => '000000', 'public' => true
        ];
    }

    /**
     * Return Update data
     *
     * @return array
     */
    public function returnUpdateTestData()
    {
        return [
            'title' => 'Test Public Tag in UPDATE'
        ];
    }

    /**
     * @param $title
     */
    private function removeTestTag($title)
    {
        // If test Tag exists, remove
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy(['title' => $title]);
        if (null !== $tag) {
            $this->em->remove($tag);
            $this->em->flush();
        }

        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy(['title' => $title]);

        $this->assertEquals(null, $tag);
    }
}
