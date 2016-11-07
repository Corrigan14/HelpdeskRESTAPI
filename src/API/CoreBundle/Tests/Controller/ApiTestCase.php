<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/7/16
 * Time: 11:34 AM
 */

namespace API\CoreBundle\Tests\Controller;

use API\CoreBundle\Tests\Traits\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase implements ControllerTestInterface
{
    use LoginTrait;

    protected $adminToken;
    protected $userToken;

    protected $client = false;

    public function __construct()
    {
        $this->adminToken = $this->loginUserGetToken('admin', 'admin', static::createClient());
        $this->userToken = $this->loginUserGetToken('user', 'user', static::createClient());
        /**
         * token is generated?
         */
        $this->assertNotEquals(false, $this->adminToken);
        $this->assertNotEquals(false, $this->userToken);
    }

//    1. GET List
    public function testListSuccess()
    {
        $crawler = $this->getClient(true)->request('GET', $this->getBaseUrl(), [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        /**
         * Expect a list of users
         */
        //$this->assertEquals(200 , $client->getResponse()->getContent());
        $this->assertEquals(200, $this->getClient()->getResponse()->getStatusCode());
    }

    public function testListErrors()
    {
        $crawler = $this->getClient(true)->request('GET', $this->getBaseUrl());
        /**
         * unauthorized
         */
        $this->assertEquals(401, $this->getClient()->getResponse()->getStatusCode());
        $this->assertContains('Auth header required', $this->getClient()->getResponse()->getContent());
    }

    /**
     * @param bool $new
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