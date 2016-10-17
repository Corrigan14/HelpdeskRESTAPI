<?php

namespace API\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class LoginControllerTest
 *
 * @package API\CoreBundle\Tests\Controller
 */
class LoginControllerTest extends WebTestCase
{
    public function testLoginErrors()
    {
        $client = static::createClient();

        $crawler = $client->request('POST' , '/token-authentication');
        /**
         * Expect User not found as missing credentials
         */
        $this->assertEquals(404 , $client->getResponse()->getStatusCode());
        $this->assertContains('error' , $client->getResponse()->getContent());

        $crawler = $client->request('GET' , '/token-authentication');
        /**
         * Expect Method not allowed
         */
        $this->assertEquals(405 , $client->getResponse()->getStatusCode());


        $crawler = $client->request('POST' , '/token-authentication' , ['username' => 'admin2' , 'password' => 'admin']);
        /**
         * Expect User not found as incorrect username
         */
        $this->assertEquals(404 , $client->getResponse()->getStatusCode());
        $this->assertContains('error' , $client->getResponse()->getContent());


        $crawler = $client->request('POST' , '/token-authentication' , ['username' => 'admin' , 'password' => 'admin2']);
        /**
         * Expect Access Denied as incorrect password
         */
        $this->assertEquals(403 , $client->getResponse()->getStatusCode());
        $this->assertContains('error' , $client->getResponse()->getContent());
    }

    public function testLoginSuccess()
    {
        $client = static::createClient();

        $crawler = $client->request('POST' , '/token-authentication' , ['username' => 'admin' , 'password' => 'admin']);
        /**
         * Expect User not found as incorrect username
         */
        $this->assertEquals(200 , $client->getResponse()->getStatusCode());
        $content = json_decode($client->getResponse()->getContent() , true);
        /**
         * Chceck for Token in response
         */
        $this->assertTrue(array_key_exists('token' , $content));
    }
}
