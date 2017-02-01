<?php

namespace API\CoreBundle\Tests\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\UserRole;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class UserControllerTest
 *
 * @package API\CoreBundle\Tests\Controller
 */
class UserControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/core-bundle/users';

    /**
     * GET LIST - success
     */
    public function testListSuccess()
    {
        parent::testListSuccess();

        // Test List with custom data fields
        $this->getClient()->request('GET', $this->getBaseUrl(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one user and if we get a response based on custom fields e.g. only name
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('_links', $response));

        // Test List with only inActive users
        $this->getClient()->request('GET', $this->getBaseUrl() . '?isActive=false', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect empty list
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $responseData = $response['data'];
        $this->assertEquals(0, count($responseData));
    }

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
        parent::testListErrors();

        // Try to load list of entities user doesn't have permission with USER_ROLE
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

        // Try to load Entity if user doesn't have permission with USER_ROLE
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * POST SINGLE - success
     *
     * Test the adding of company to User
     */
    public function testPostSingleSuccess()
    {
        $data = $this->returnPostTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Web-Solutions'
        ]);

        $customerRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'customer'
        ]);

        // Create Entity and add UserRole to this User
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $customerRole->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        $this->removeTestEntity();

        // Create Entity and add Company to this User
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $customerRole->getId() . '/' . 'company/' . $company->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links and data include's company param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $data = $response['data'];
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('company', $data));
    }

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        $customerRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'customer'
        ]);

        $adminRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'admin'
        ]);

        // Try to create test user without authorization header
        $this->getClient()->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $customerRole->getId(),
            ['username' => 'testuser', 'password' => 'password', 'email' => 'testuser@testuser.com'],
            [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create test user with ROLE_USER if user doesn't have permission
        $this->getClient()->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $adminRole->getId(),
            ['username' => 'testuser', 'password' => 'password', 'email' => 'testuser@testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, invalid email
        $this->getClient()->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $customerRole->getId(),
            ['username' => 'testuser', 'password' => 'password', 'email' => 'testuser.testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, invalid password
        $this->getClient()->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $customerRole->getId(),
            ['username' => 'testuser', 'password' => 'short', 'email' => 'testuser.testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, no password
        $this->getClient()->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $customerRole->getId(),
            ['username' => 'testuser', 'email' => 'testuser.testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, blank username
        $this->getClient()->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $customerRole->getId(),
            ['username' => '', 'email' => 'testuser.testuser.com', 'password' => 'password'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, no username
        $this->getClient()->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $customerRole->getId(),
            ['email' => 'testuser.testuser.com', 'password' => 'password'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, with non-existent parameter
        $this->getClient()->request('POST', $this->getBaseUrl() . '/' . 'user-role/' . $customerRole->getId(),
            ['email' => 'testuser.testuser.com', 'username' => 'testuser', 'password' => 'password', 'bulls' => 'hit'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * Update the company of user Entity
     */
    public function testPutUserWithCompanySuccess()
    {
        $data = $this->returnUpdateTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $entity = $this->findOneEntity();
        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'LanSystems'
        ]);

        // Update Company of User: PUT method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links and data include's company param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $data = $response['data'];

        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('company', $data));
    }

    /**
     * Update the company of user Entity
     */
    public function testPatchUserWithCompanySuccess()
    {
        $data = $this->returnUpdateTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $entity = $this->findOneEntity();

        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'LanSystems'
        ]);
        // Update Company of User: PATCH method (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links and data include's company param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $data = $response['data'];

        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('company', $data));
    }

    /**
     * Update the company of user Entity
     */
    public function testPutUserWithUserRoleSuccess()
    {
        $data = $this->returnUpdateTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $entity = $this->findOneEntity();
        $userRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'customer'
        ]);

        // Update Company of User: PUT method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $userRole->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links and data include's company param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $data = $response['data'];
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('company', $data));
    }

    /**
     * Update the company of user Entity
     */
    public function testPatchUserWithUserRoleSuccess()
    {
        $data = $this->returnUpdateTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $entity = $this->findOneEntity();
        $userRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'customer'
        ]);

        // Update Company of User: PUT method (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $userRole->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links and data include's company param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $data = $response['data'];
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('company', $data));
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        parent::testUpdateSingleErrors();

        $entity = $this->findOneEntity();

        // Try to update test user with ROLE_USER if user doesn't have permission : method PUT
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'testuser225', 'password' => 'password', 'email' => 'testuser@testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test user with ROLE_USER if user doesn't have permission : method PATCH
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), [
            'username' => 'testuser225', 'password' => 'password', 'email' => 'testuser@testuser.com',
        ], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

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
     *  UPDATE USER'S COMPANY SINGLE - errors
     */
    public function testPutUserWithCompanyErrors()
    {
        $this->removeTestUser('testuserchanged');

        $entity = $this->findOneEntity();
        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Web-Solutions'
        ]);

        // Try to update test user with ROLE_USER if user doesn't have permission : method PUT
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            ['username' => 'testuser225', 'password' => 'password', 'email' => 'testuser@testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to Update user as admin, invalid email
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            ['username' => 'testuser225', 'password' => 'password', 'email' => 'testuser.testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to Update user's company to not-existed company
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/company/12547',
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update user as admin, invalid password
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            ['username' => 'testuser226', 'password' => 'pas', 'email' => 'testuser@testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, not uniqe username: method PUT
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            ['username' => 'user', 'password' => 'password', 'email' => 'testuser@tes22tuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE USER'S COMPANY SINGLE - errors
     */
    public function testPatchUserWithCompanyErrors()
    {
        $this->removeTestUser('testuserchanged');

        $entity = $this->findOneEntity();
        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Web-Solutions'
        ]);

        // Try to update test user with ROLE_USER if user doesn't have permission : method PUT
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            ['username' => 'testuser225', 'password' => 'password', 'email' => 'testuser@testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to Update user as admin, invalid email
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            ['username' => 'testuser225', 'password' => 'password', 'email' => 'testuser.testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to Update user's company to not-existed company
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/company/12547',
            [],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update user as admin, invalid password
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            ['username' => 'testuser226', 'password' => 'pas', 'email' => 'testuser@testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, not uniqe username: method PUT
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/company/' . $company->getId(),
            ['username' => 'user', 'password' => 'password', 'email' => 'testuser@tes22tuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE USER'S USER ROLE SINGLE - errors
     */
    public function testPutUserWithUserRoleErrors()
    {
        $this->removeTestUser('testuserchanged');

        $entity = $this->findOneEntity();
        $customerRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'customer'
        ]);

        // Try to update test user with ROLE_USER if user doesn't have permission : method PUT
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $customerRole->getId(),
            ['username' => 'testuser225', 'password' => 'password', 'email' => 'testuser@testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to Update user as admin, invalid email
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $customerRole->getId(),
            ['username' => 'testuser225', 'password' => 'password', 'email' => 'testuser.testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to Update user's company to not-existed company
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/12547',
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update user as admin, invalid password
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $customerRole->getId(),
            ['username' => 'testuser226', 'password' => 'pas', 'email' => 'testuser@testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, not uniqe username: method PUT
        $this->getClient()->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $customerRole->getId(),
            ['username' => 'user', 'password' => 'password', 'email' => 'testuser@tes22tuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE USER'S USER ROLE SINGLE - errors
     */
    public function testPatchUserWithUserRoleErrors()
    {
        $this->removeTestUser('testuserchanged');

        $entity = $this->findOneEntity();
        $customerRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'customer'
        ]);

        // Try to update test user with ROLE_USER if user doesn't have permission : method PUT
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $customerRole->getId(),
            ['username' => 'testuser225', 'password' => 'password', 'email' => 'testuser@testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to Update user as admin, invalid email
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $customerRole->getId(),
            ['username' => 'testuser225', 'password' => 'password', 'email' => 'testuser.testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to Update user's company to not-existed company
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/12547',
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update user as admin, invalid password
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $customerRole->getId(),
            ['username' => 'testuser226', 'password' => 'pas', 'email' => 'testuser@testuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update user as admin, not uniqe username: method PUT
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/user-role/' . $customerRole->getId(),
            ['username' => 'user', 'password' => 'password', 'email' => 'testuser@tes22tuser.com'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - success
     *
     * We are not using Base test because User Entity is not removed, just is_active param is set to 0
     */
    public function testDeleteSingleSuccess()
    {
        $entity = $this->findOneEntity();

        // Delete Entity
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
        parent::testDeleteSingleErrors();

        $entity = $this->findOneEntity();

        // Try to delete User Entity with logged ROLE_USER if user doesn't have permission
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Check if is_active param is 0
        $isActiveParam = $entity->getIsActive();
        $this->assertEquals(false, $isActiveParam);
    }

    /**
     * RESTORE SINGLE - success
     */
    public function testRestoreSingleSuccess()
    {
        // Find entity and Delete it
        $entity = $this->findOneEntity();

        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Check if is_active param is 0 - entity is Deleted
        $isActiveParam = $entity->getIsActive();
        $this->assertEquals(false, $isActiveParam);

        // Restore entity
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/restore',
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertEquals(true, $response['data']['is_active']);
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
            'email' => 'tuser@user.sk'
        ]);

        if ($u instanceof User) {
            return $u;
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
        $adminRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'admin'
        ]);

        if ($adminRole instanceof UserRole) {
            $user = new User();
            $user->setUsername('tuser');
            $user->setEmail('tuser@user.sk');
            $user->setPassword('tuser@userjjkkj');
            $user->setRoles(['ROLE_USER']);
            $user->setUserRole($adminRole);
            $user->setIsActive(true);

            $this->em->persist($user);
            $this->em->flush();

            return $user;
        }
    }


    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeTestUser('testuser');
        $this->removeTestUser('testuserchanged');
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
}
