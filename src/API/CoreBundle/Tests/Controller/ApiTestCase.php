<?php

namespace API\CoreBundle\Tests\Controller;

use API\CoreBundle\Services\StatusCodesHelper;
use API\CoreBundle\Tests\Traits\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ApiTestCase
 *
 * @package API\CoreBundle\Tests\Controller
 */
abstract class ApiTestCase extends WebTestCase implements ControllerTestInterface
{
    use LoginTrait;

    /** @var bool|string  */
    protected $adminToken;

    /** @var bool|string  */
    protected $userToken;

    /** @var bool  */
    protected $client = false;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /**
     * ApiTestCase constructor.
     */
    public function __construct()
    {
        parent::__construct();
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->adminToken = $this->loginUserGetToken('admin', 'admin', static::createClient());
        $this->userToken = $this->loginUserGetToken('testuser2', 'testuser', static::createClient());

        // Token is generated?
        $this->assertNotEquals(false, $this->adminToken);
        $this->assertNotEquals(false, $this->userToken);
    }

    /**
     * GET LIST - success
     *
     * @return array
     */
    public function testListSuccess()
    {
        // Load list of data of Entity (as Admin)
        $this->getClient(true)->request('GET', $this->getBaseUrl(), [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $keys = array_keys($response['data'][0]);
        $this->assertTrue(array_key_exists('_links', $response));

        return $keys;
    }

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
        // Try to load list of entities without authorization header
        $this->getClient(true)->request('GET', $this->getBaseUrl());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        $this->assertContains('Auth header required', $this->getClient()->getResponse()->getContent());
    }

    /**
     * GET SINGLE - success
     */
    public function testGetSingleSuccess()
    {
        $entity = $this->findOneEntity();

        // Load one Entity with posted ID (as Admin)
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $entity->getId(), [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));
    }

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        $entity = $this->findOneEntity();

        // Try to load Entity without authorization header
        $this->getClient(true)->request('GET',  $this->getBaseUrl() . '/' . $entity->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to load Entity with not existed ID (as Admin)
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/73333448', [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::RESOURCE_NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to load Entity with bad ID (as Admin)
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/ebuf', [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to load Entity if we have invalid JWT token
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $entity->getId(), [], [], ['Authorization' => 'Bearer ' . $this->adminToken . 1, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken . 1]);
        $this->assertEquals(StatusCodesHelper::INVALID_JWT_TOKEN_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * POST SINGLE - success
     */
    public function testPostSingleSuccess()
    {
        $data = $this->returnPostTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Create Entity (as admin)
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));
    }

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        $data = $this->returnPostTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Try to create test Entity, without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * UPDATE SINGLE - success
     */
    public function testUpdateSingleSuccess()
    {
        $data = $this->returnUpdateTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $entity = $this->findOneEntity();

        // Update Entity: POST method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Entity: PATCH method (as admin)
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        $data = $this->returnUpdateTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $entity = $this->findOneEntity();

        // Try to update test Entity without authorization header: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity without authorization header: method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID: method PUT (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/1125874', [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID: method PATCH (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/1125874', [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess()
    {
        $entity = $this->findOneEntity();

        // Delete Entity
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::DELETED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Check if Entity was deleted
        $this->getClient()->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
        $entity = $this->findOneEntity();

        // Try to delete Entity without authorization header
        $this->getClient()->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(), [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to delete Entity with not existed ID (as Admin)
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/1125874', [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @param bool $new
     *
     * @return bool|\Symfony\Bundle\FrameworkBundle\Client
     */
    public function getClient($new = false)
    {
        if ($this->client && false === $new) {
            return $this->client;
        }

        $this->client = static::createClient();

        return $this->client;
    }

    /**
     * Get the url for requests
     *
     * @return string
     */
    abstract public function getBaseUrl();

    /**
     * Return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    abstract public function findOneEntity();

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    abstract public function createEntity();

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    abstract public function removeTestEntity();

    /**
     * Return Post data
     *
     * @return array
     */
    abstract public function returnPostTestData();

    /**
     * Return Update data
     *
     * @return array
     */
    abstract public function returnUpdateTestData();
}