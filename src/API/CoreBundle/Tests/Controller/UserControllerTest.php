<?php

namespace API\CoreBundle\Tests\Controller;

use API\CoreBundle\Model\UserModel;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use API\CoreBundle\Tests\Traits\LoginTrait;


/**
 * Class UserControllerTest
 *
 * @package API\CoreBundle\Tests\Controller
 */
class UserControllerTest extends WebTestCase
{

    use LoginTrait;


    public function __construct()
    {
        $this->adminToken=$this->loginUserGetToken('admin','admin', static::createClient());
        $this->userToken=$this->loginUserGetToken('user','user', static::createClient());
        /**
         * token is generated?
         */
        $this->assertNotEquals(false , $this->adminToken );
        $this->assertNotEquals(false , $this->userToken );
    }


    public function testUserLogin(){

//        $this->assertEquals(false , $this->loginUserGetToken('a','a', static::createClient()) );
//        $this->assertNotEquals(false , $this->loginUserGetToken('admin','admin', static::createClient()) );

    }

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




        $crawler = $client->request('GET' ,'/api/v1/users');
        /**
         * unauthorized
         */
        $this->assertEquals(401 , $client->getResponse()->getStatusCode());
        $this->assertContains('Auth header required' , $client->getResponse()->getContent());



        $crawler = $client->request('GET' ,'/api/v1/users',[],[],['Authorization'=>'Bearer '.$this->adminToken,'HTTP_AUTHORIZATION' => 'Bearer '.$this->adminToken]);
        /**
         * Expect a list of users
         */
        //$this->assertEquals(200 , $client->getResponse()->getContent());
        $this->assertEquals(200 , $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent() , true);

        /**
         * We expect at least one user and compare the keys to default from UserModel
         */
        $keys = array_keys($response['data'][0]);

        $this->assertEquals(array_merge(UserModel::DEFAULT_FIELDS,['_links']) , $keys);


        $crawler = $client->request('GET' , '/api/v1/users?fields=name',[],[],['Authorization'=>'Bearer '.$this->adminToken,'HTTP_AUTHORIZATION' => 'Bearer '.$this->adminToken]);
        $this->assertEquals(200 , $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent() , true);
        /**
         * We expect at least one user and if we get a response based on custom fields e.g. only name
         */
        $keys = array_keys($response['data'][0]);

        $this->assertEquals(['name','id','_links'] , $keys);
    }

    public function testUserSuccess()
    {
        $client = static::createClient();
        /**
         * @var EntityManager $manager
         */
        $manager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $user = $manager->getRepository('APICoreBundle:User')->findOneBy([]);
        $crawler = $client->request('GET' , '/api/v1/users/' . $user->getId(),[],[],['Authorization'=>'Bearer '.$this->adminToken,'HTTP_AUTHORIZATION' => 'Bearer '.$this->adminToken]);

        $this->assertEquals(200 , $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent() , true);

        $this->assertTrue(array_key_exists('data' , $response));
        $this->assertTrue(array_key_exists('_links' , $response));
    }


    public function testTrait(){

    }

}
