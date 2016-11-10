<?php

namespace API\CoreBundle\Tests\Controller;

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
     * GET LIST - success
     */
    public function testListSuccess()
    {
        $keys = parent::testListSuccess();

        $this->assertEquals(UserRepository::DEFAULT_FIELDS, $keys);

        // Test List with custom data fields
        $this->getClient()->request('GET', '/api/v1/users?fields=name', [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one user and if we get a response based on custom fields e.g. only name
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $keys = array_keys($response['data'][0]);
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertEquals(['name', 'id'], $keys);
    }

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        parent::testPostSingleErrors();

        // Try to create test user with ROLE_USER
        $this->getClient()->request('POST', $this->getBaseUrl(), [
            'username' => 'testuser', 'password' => 'password', 'email' => 'testuser@testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, invalid email
        $this->getClient()->request('POST', $this->getBaseUrl(), [
            'username' => 'testuser', 'password' => 'password', 'email' => 'testuser.testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, invalid password
        $this->getClient()->request('POST', $this->getBaseUrl(), [
            'username' => 'testuser', 'password' => 'short', 'email' => 'testuser.testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, no password
        $this->getClient()->request('POST', $this->getBaseUrl(), [
            'username' => 'testuser', 'email' => 'testuser.testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, blank username
        $this->getClient()->request('POST', $this->getBaseUrl(), [
            'username' => '', 'email' => 'testuser.testuser.com', 'password' => 'password',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, no username
        $this->getClient()->request('POST', $this->getBaseUrl(), [
            'email' => 'testuser.testuser.com', 'password' => 'password',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, with non-existent parameter
        $this->getClient()->request('POST', $this->getBaseUrl(), [
            'email' => 'testuser.testuser.com', 'username' => 'testuser', 'password' => 'password', 'bulls' => 'hit',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        parent::testUpdateSingleErrors();

        $entity = $this->findOneEntity();

        // Try to update test user with ROLE_USER:method PUT
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'testuser225', 'password' => 'password', 'email' => 'testuser@testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test user with ROLE_USER:method PATCH
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'testuser225', 'password' => 'password', 'email' => 'testuser@testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, invalid email: method PUT
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'testuser225', 'password' => 'password', 'email' => 'testuser.testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, invalid email: method PATCH
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'testuser225', 'password' => 'password', 'email' => 'testuser.testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, invalid password: method PUT
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'testuser226', 'password' => 'pas', 'email' => 'testuser@testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, invalid password: method PATCH
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'testuser226', 'password' => 'pas', 'email' => 'testuser@testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, not uniqe username: method PUT
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'user', 'password' => 'password', 'email' => 'testuser@tes22tuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, not uniqe username: method PATCH
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'user', 'password' => 'password', 'email' => 'testuser@tes22tuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
        parent::testDeleteSingleErrors();

        $entity = $this->findOneEntity();

        // Try to delete User Entity with logged ROLE_USER
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @param string $username
     */
    private function removeTestUser($username)
    {
        // If test user exists, remove
        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy(['username' => $username]);
        if (null !== $user) {
            $this->em->remove($user);
            $this->em->flush();
        }
        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy(['username' => $username]);

        $this->assertEquals(null, $user);
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
        $this->getClient(true)->request('POST', $this->getBaseUrl(), [
            'username' => 'tuser', 'password' => 'userTest22', 'email' => 'tuser@user.sk',
            'detail_data' => ['name' => 'name of user', 'surname' => 'surname of user', 'tel' => '1234 25879'],
        ], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(201, $this->getClient()->getResponse()->getStatusCode());

        $createdUser = json_decode($this->getClient()->getResponse()->getContent(), true);
        $createdUser = $createdUser['data'];
        $this->assertTrue(array_key_exists('id', $createdUser));

        return $createdUser;
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'username' => 'testuser', 'password' => 'password', 'email' => 'testuser@testuser.com',
            'detail_data' => ['name' => 'name', 'surname' => 'surname', 'tel' => '1234'],
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
            'email' => 'changed@with.put', 'username' => 'testuserchanged',
            'detail_data' => ['name' => 'patch name', 'surname' => 'patch surname'],
        ];
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeTestUser('testuser');
        $this->removeTestUser('testuserchanged');
    }
}
