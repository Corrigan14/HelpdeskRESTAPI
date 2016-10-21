<?php

namespace API\CoreBundle\Tests\Controller;

use API\CoreBundle\Model\UserModel;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserControllerTest
 *
 * @package API\CoreBundle\Tests\Controller
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Test all error responses for controller
     */
    public function testUserError()
    {

    }

    /**
     * Test all success request for controller
     */
    public function testUsersSuccess()
    {
        $client = static::createClient();

        $crawler = $client->request('GET' , '/users');
        /**
         * Expect a list of users
         */
        $this->assertEquals(200 , $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent() , true);

        /**
         * We expect at least one user and compare the keys to default from UserModel
         */
        $keys = array_keys($response['data'][0]);

        $this->assertEquals(UserModel::DEFAULT_FIELDS , $keys);

        $crawler = $client->request('GET' , '/users?fields=name');
        $this->assertEquals(200 , $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent() , true);
        /**
         * We expect at least one user and if we get a response based on custom fields e.g. only name
         */
        $keys = array_keys($response['data'][0]);

        $this->assertEquals(['name'] , $keys);
    }

    public function testUserSuccess()
    {
        $client = static::createClient();
        /**
         * @var EntityManager $manager
         */
        $manager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $user = $manager->getRepository('APICoreBundle:User')->findOneBy([]);
        $crawler = $client->request('GET' , '/users/' . $user->getId());

        $this->assertEquals(200 , $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent() , true);

        $this->assertTrue(array_key_exists('data' , $response));
        $this->assertTrue(array_key_exists('_links' , $response));
    }
}
