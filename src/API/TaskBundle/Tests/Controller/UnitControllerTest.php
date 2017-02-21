<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Unit;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class UnitControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class UnitControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/units';

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
        parent::testListErrors();

        // Try to load entity with ROLE_USER which hasn't permission to this action
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

        // Try to load Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
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

        // Try to load Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Entity with not valid params
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['title' => 'Only title'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  POST SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        parent::testUpdateSingleErrors();

        $entity = $this->findOneEntity();
        $data = $this->returnUpdateTestData();

        // Try to load Entity with ROLE_USER which hasn't permission to this action:PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess()
    {
        $entity = $this->findOneEntity();

        // Delete Entity - set is_active param to 0
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * RESTORE SINGLE - success
     */
    public function testRestoreSingleSuccess()
    {
        $entity = $this->findOneEntity();

        // Restore Entity - set is_active param to 1
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/restore/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * RESTORE SINGLE - errors
     */
    public function testRestoreSingleErrors()
    {
        $entity = $this->findOneEntity();

        // Try to update test Entity without authorization header
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/restore/' . $entity->getId(),
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/restore/12544' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/restore/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
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
        $unit = $this->em->getRepository('APITaskBundle:Unit')->findOneBy([
            'title' => 'New unit'
        ]);

        if ($unit instanceof Unit) {
            return $unit;
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
        $unit = new Unit();
        $unit->setTitle('New unit');
        $unit->setShortcut('Nu');
        $unit->setIsActive(true);

        $this->em->persist($unit);
        $this->em->flush();

        return $unit;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {

    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'title' => 'New POST Unit',
            'shortcut' => 'NPU',
            'is_active' => true
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
            'title' => 'New UPDATE Unit',
            'shortcut' => 'NPU',
            'is_active' => true
        ];
    }
}