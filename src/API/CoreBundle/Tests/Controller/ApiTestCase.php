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

    protected $adminToken;
    protected $userToken;

    protected $client = false;
    /**
     * @var \Doctrine\ORM\EntityManager
     */
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

        $this->adminToken = $this->loginUserGetToken('admin' , 'admin' , static::createClient());
        $this->userToken = $this->loginUserGetToken('user' , 'user' , static::createClient());
        /**
         * token is generated?
         */
        $this->assertNotEquals(false , $this->adminToken);
        $this->assertNotEquals(false , $this->userToken);
    }

    /**
     * Get the url for requests
     *
     * @return string
     */
    abstract public function getBaseUrl();

    /**
     * Return a signle entity from db for testing CRUD
     *
     * @return mixed
     */
    abstract public function findOneEntity();

    /**
     * Create and return a signle entity from db for testing CRUD
     *
     * @return mixed
     */
    abstract public function createEntity();


    /**
     * GET LIST - SUCCESS
     *
     * @return array
     */
    public function testListSuccess()
    {
        $this->getClient(true)->request('GET' , $this->getBaseUrl() , [] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        /**
         * Expect a list of data
         */
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE , $this->getClient()->getResponse()->getStatusCode());
        $response = json_decode($this->getClient()->getResponse()->getContent() , true);

        /**
         * We expect at least one user and compare the keys to default from UserModel
         */
        $keys = array_keys($response['data'][0]);
        $this->assertTrue(array_key_exists('_links' , $response));

        return $keys;
    }

    /**
     * GET LIST - ERRORS
     */
    public function testListErrors()
    {
        $this->getClient(true)->request('GET' , $this->getBaseUrl());
        /**
         * unauthorized
         */
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE , $this->getClient()->getResponse()->getStatusCode());
        $this->assertContains('Auth header required' , $this->getClient()->getResponse()->getContent());
    }

    /**
     * GET SINGLE - success
     */
    public function testGetSingleSuccess()
    {
        $entity = $this->findOneEntity();
        $this->getClient(true)->request('GET' , $this->getBaseUrl() . '/' . $entity->getId() , [] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);

        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE , $this->getClient()->getResponse()->getStatusCode());

        $response = json_decode($this->getClient()->getResponse()->getContent() , true);

        $this->assertTrue(array_key_exists('data' , $response));
        $this->assertTrue(array_key_exists('_links' , $response));
    }

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        $this->getClient(true)->request('GET' , $this->getBaseUrl() . '/73333448' , [] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);

        $this->assertEquals(StatusCodesHelper::RESOURCE_NOT_FOUND_CODE , $this->getClient()->getResponse()->getStatusCode());


        $this->getClient(true)->request('GET' , $this->getBaseUrl() . '/ebuf' , [] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);

        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE , $this->getClient()->getResponse()->getStatusCode());


        $this->getClient(true)->request('GET' , $this->getBaseUrl() . '/7448' , [] , [] , ['Authorization' => 'Bearer ' . $this->adminToken . 1 , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken . 1]);

        $this->assertEquals(StatusCodesHelper::INVALID_JWT_TOKEN_CODE , $this->getClient()->getResponse()->getStatusCode());
    }


    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess()
    {
        $entity = $this->findOneEntity();
        $this->getClient(true)->request('DELETE' , $this->getBaseUrl() . '/' . $entity->getId() ,
            [] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::DELETED_CODE , $this->getClient()->getResponse()->getStatusCode());

        /**
         * check if entity was deleted
         */
        $this->getClient()->request('DELETE' , $this->getBaseUrl() . '/' . $entity->getId() ,
            [] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE , $this->getClient()->getResponse()->getStatusCode());
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
}