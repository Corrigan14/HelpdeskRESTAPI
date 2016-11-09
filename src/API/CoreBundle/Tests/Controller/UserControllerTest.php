<?php

namespace API\CoreBundle\Tests\Controller;

use API\CoreBundle\DataFixtures\ORM\UserFixture;
use API\CoreBundle\Repository\UserRepository;
use API\CoreBundle\Services\StatusCodesHelper;

/**
 * Class UserControllerTest
 *
 * @package API\CoreBundle\Tests\Controller
 */
class UserControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/users';

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
     * @return mixed
     */
    public function findOneEntity()
    {
        $u = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'tuser',
        ]);

        if (null !== $u) {
            return $u;
        }

        $userArray = $this->createEntity();

        return $this->em->getRepository('APICoreBundle:User')->find($userArray['id']);
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $this->getClient(true)->request('POST' , $this->getBaseUrl() , [
            'username'    => 'tuser' , 'password' => 'userTest22' , 'email' => 'tuser@user.sk' ,
            'detail_data' => ['name' => 'name of user' , 'surname' => 'surname of user' , 'tel' => '1234 25879'] ,
        ] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(201 , $this->getClient()->getResponse()->getStatusCode());

        $createdUser = json_decode($this->getClient()->getResponse()->getContent() , true);
        $createdUser = $createdUser['data'];
        $this->assertTrue(array_key_exists('id' , $createdUser));

        return $createdUser;
    }

    /**
     * GET LIST - success
     */
    public function testListSuccess()
    {
        $keys = parent::testListSuccess();

        $this->assertEquals(UserRepository::DEFAULT_FIELDS , $keys);

        $this->getClient()->request('GET' , '/api/v1/users?fields=name' , [] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(200 , $this->getClient()->getResponse()->getStatusCode());

        $response = json_decode($this->getClient()->getResponse()->getContent() , true);

        // We expect at least one user and if we get a response based on custom fields e.g. only name
        $keys = array_keys($response['data'][0]);
        $this->assertTrue(array_key_exists('_links' , $response));

        $this->assertEquals(['name' , 'id'] , $keys);
    }

    /**
     * POST SINGLE - success
     */
    public function testPostSingleSuccess()
    {
        $this->removeTestUser();

        // Create user as admin, with detail data
        $this->getClient(true)->request('POST' , $this->getBaseUrl() , [
            'username'    => 'testuser' , 'password' => 'password' , 'email' => 'testuser@testuser.com' ,
            'detail_data' => ['name' => 'name' , 'surname' => 'surname' , 'tel' => '1234'] ,
        ] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(201 , $this->getClient()->getResponse()->getStatusCode());

        $createdUser = json_decode($this->getClient()->getResponse()->getContent() , true);
        $createdUser = $createdUser['data'];
        $this->assertTrue(array_key_exists('id' , $createdUser));
    }

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        $this->removeTestUser();

        // create test user, without authorization header
        $this->getClient(true)->request('POST' , $this->getBaseUrl() , [
            'username' => 'testuser' , 'password' => 'password' , 'email' => 'testuser@testuser.com']);
        $this->assertEquals(401 , $this->getClient()->getResponse()->getStatusCode());

        // try to create test user with ROLE_USER
        $this->getClient()->request('POST' , $this->getBaseUrl() , [
            'username' => 'testuser' , 'password' => 'password' , 'email' => 'testuser@testuser.com' ,
        ] , [] , ['Authorization' => 'Bearer ' . $this->userToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(401 , $this->getClient()->getResponse()->getStatusCode());


        //create user as admin, invalid email
        $this->getClient()->request('POST' , $this->getBaseUrl() , [
            'username' => 'testuser' , 'password' => 'password' , 'email' => 'testuser.testuser.com' ,
        ] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(409 , $this->getClient()->getResponse()->getStatusCode());

        //create user as admin, invalid password
        $this->getClient()->request('POST' , $this->getBaseUrl() , [
            'username' => 'testuser' , 'password' => 'short' , 'email' => 'testuser.testuser.com' ,
        ] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(409 , $this->getClient()->getResponse()->getStatusCode());

        //create user as admin, no password
        $this->getClient()->request('POST' , $this->getBaseUrl() , [
            'username' => 'testuser' , 'email' => 'testuser.testuser.com' ,
        ] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(409 , $this->getClient()->getResponse()->getStatusCode());

        //create user as admin, blank username
        $this->getClient()->request('POST' , $this->getBaseUrl() , [
            'username' => '' , 'email' => 'testuser.testuser.com' , 'password' => 'password' ,
        ] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(409 , $this->getClient()->getResponse()->getStatusCode());


        //create user as admin, no username
        $this->getClient()->request('POST' , $this->getBaseUrl() , [
            'email' => 'testuser.testuser.com' , 'password' => 'password' ,
        ] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(409 , $this->getClient()->getResponse()->getStatusCode());


        //create user as admin, with non-existent parameter
        $this->getClient()->request('POST' , $this->getBaseUrl() , [
            'email' => 'testuser.testuser.com' , 'username' => 'testuser' , 'password' => 'password' , 'bulls' => 'hit' ,
        ] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(409 , $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * UPDATE SINGLE - success
     */
    public function testUpdateSingleSuccess()
    {
        $entity = $this->findOneEntity();

        // Change User with username = user
        $this->getClient(true)->request('PUT' , $this->getBaseUrl() . '/' . $entity->getId() ,
            [
                'email'       => 'changed@with.put' , 'username' => 'testuser' ,
                'detail_data' => ['name' => 'patch name' , 'surname' => 'patch surname'] ,
            ] ,
            [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE , $this->getClient()->getResponse()->getStatusCode());

        // Change User with username = user
        $this->getClient()->request('PATCH' , $this->getBaseUrl() . '/' . $entity->getId() ,
            [
                'email'       => 'changed@with2.patch' ,
                'detail_data' => ['name' => 'patch name' , 'surname' => 'patch surname'] ,
            ] ,
            [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE , $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        // TODO: Implement testUpdateSingleErrors() method.
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
        $entity = $this->findOneEntity();
        // Try to delete entity with ROLE_USER
        $this->getClient(true)->request('DELETE' , $this->getBaseUrl() . '/' . $entity->getId() ,
            [] , [] , ['Authorization' => 'Bearer ' . $this->adminToken , 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(401 , $this->getClient()->getResponse()->getStatusCode());


        // Try to delete user without authentication
        $this->getClient()->request('DELETE' , $this->getBaseUrl() . '/' . $entity->getId() ,
            [] , [] , []);
        $this->assertEquals(401 , $this->getClient()->getResponse()->getStatusCode());
    }

    private function removeTestUser()
    {
        // If test user exists, remove
        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy(['username' => 'testuser']);
        if (null !== $user) {
            $this->em->remove($user);
            $this->em->flush();
        }
        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy(['username' => 'testuser']);

        $this->assertEquals(null , $user);
    }
}
