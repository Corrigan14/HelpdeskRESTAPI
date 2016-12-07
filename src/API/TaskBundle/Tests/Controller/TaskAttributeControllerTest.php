<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Services\VariableHelper;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

class TaskAttributeControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/task-attributes';

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
        parent::testListErrors();

        // Try to load list of entities with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        parent::testGetSingleErrors();

        $entity = $this->findOneEntity();

        // Try to load list of entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl().'/'.$entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        parent::testPostSingleErrors();

        $data = $this->returnPostTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Try to create Task Attribute Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST' , $this->getBaseUrl() , $data,[],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE , $this->getClient()->getResponse()->getStatusCode());

        // Try to create Task Attribute Entity with invalid parameter Type (Type has to be chosen from allowed options)
        $this->getClient(true)->request('POST' , $this->getBaseUrl() ,
            ['title'=>'test title','type'=>'some not allowed Type'],[],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE , $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * Get the url for requests
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return self::BASE_URL;
    }

    /**
     * Return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function findOneEntity()
    {
        $ta = $this->em->getRepository('APITaskBundle:TaskAttribute')->findOneBy([
            'title' => 'task attribute NEW'
        ]);

        if ($ta instanceof TaskAttribute) {
            return $ta;
        }

        return $this->createEntity();
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $ta = new TaskAttribute();
        $ta->setTitle('task attribute NEW');
        $ta->setType(VariableHelper::INPUT);
        $this->em->persist($ta);
        $this->em->flush();

        return $ta;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeEntity('task attribute CREATE');
        $this->removeEntity('task attribute UPDATE');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'title' => 'task attribute CREATE',
            'type' => VariableHelper::INPUT
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
            'title' => 'task attribute UPDATE',
            'type' => VariableHelper::INPUT
        ];
    }

    /**
     * @param string $title
     */
    private function removeEntity($title)
    {
        $ta = $this->em->getRepository('APITaskBundle:TaskAttribute')->findOneBy([
            'title' => $title
        ]);

        if ($ta instanceof TaskAttribute) {
            $this->em->remove($ta);
            $this->em->flush();
        }
    }
}
