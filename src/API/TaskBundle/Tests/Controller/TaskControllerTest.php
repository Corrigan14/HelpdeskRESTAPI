<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Task;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class TaskControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class TaskControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/tasks';

    /**
     * GET LIST - success
     *
     * @return array
     */
    public function testListSuccess()
    {
        parent::testListSuccess();

        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $status = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => 'Completed'
        ]);

        $userProject = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        $userCompany = $userUser->getCompany();

        // Load list of data of Task Entity as Admin with filter: status
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&status=' . $status->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));

        // Load list of data of Task Entity as Admin with filter: status and project
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&status=' . $status->getId() . '&project=' . $userProject->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));

        // Load list of data of Task Entity as Admin with filter: createdBy
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&creator=' . $userUser->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));

        // Load list of data of Task Entity as Admin with filter: createdBy and requestedBy
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&creator=' . $userUser->getId() . '&requester=' . $userUser->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));

        // Load list of data of Task Entity as Admin with filter: requestedBy and company
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&requester=' . $userUser->getId() . '&company=' . $userCompany->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));

        // Load list of data of Task Entity as Admin with filter: archived (project of task is not active)
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&archived=TRUE',
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Load list of data of Task Entity as Admin with filter: createdTime and startedTime
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&createdTime=2016-12-28+19:50:56,2016-12-30+19:50:56&startedTime=2016-12-28+19:50:56,2016-12-30+19:50:56',
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Load list of data of Task Entity as Admin with filter: tag, follower
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&tag=1,2&follower=12',
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Load list of data of Task Entity as Admin with filter: addedAttribute
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&addedParameters=32=select1',
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * GET LIST OF TASKS BASED ON EXISTED FILTER - success
     *
     * @return array
     */
    public function testListSavedFilterSuccess()
    {
        $filter = $this->em->getRepository('APITaskBundle:Filter')->findOneBy([
            'title' => 'DO IT'
        ]);

        // Load list of data of Task Entity as Admin
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/filter/' . $filter->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));
    }

    /**
     * GET LIST OF TASKS BASED ON EXISTED FILTER - errors
     *
     * @return array
     */
    public function testListSavedFilterErrors()
    {
        $filter = $this->em->getRepository('APITaskBundle:Filter')->findOneBy([
            'title' => 'DO IT'
        ]);

        // Try to Load list of data of Task Entity as Admin without authorization header
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/filter/' . $filter->getId(),
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to Load list of data of Task Entity based on not existed filter
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/filter/12547' . $filter->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());
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
     * POST SINGLE - success
     */
    public function testPostSingleSuccess()
    {
        $data = $this->returnPostTestData();

        $adminsProject = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin 2',
        ]);

        $adminUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        // Create Base Entity without setting of Project or Requested user
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));

        // Create Base Entity with project
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $adminsProject->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));

        // Create Base Entity with requester
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/requester/' . $adminUser->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));

        // Create Base Entity with project and requester
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $adminsProject->getId() . '/requester/' . $adminUser->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
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

        $adminProject = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        // Try to create test Entity, without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Task with ROLE_USER in not allowed Project
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $adminProject->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Task in not existed Project
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/125478', $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Task in not existed Requester
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/requester/125478', $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Task with invalid parameter title (title is required)
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['description' => 'desc'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * UPDATE SINGLE - success
     */
    public function testUpdateSingleSuccess()
    {
        $data = $this->returnUpdateTestData();

        $entity = $this->findOneEntity();

        $adminProject = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);

        // Update Base Task Entity: PUT method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Project of Task Entity: PUT method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $adminProject->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Requester of Task Entity: PUT method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/requester/' . $userUser->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Requested user of Task Entity: PUT method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $adminProject->getId() . '/requester/' . $userUser->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * PARTIAL UPDATE SINGLE - success
     */
    public function testPartialUpdateSingleSuccess()
    {
        $data = $this->returnUpdateTestData();

        $entity = $this->findOneEntity();

        $adminProject = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);

        // Update Base Task Entity: PATCH method (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Project of Task Entity: PATCH method (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $adminProject->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Requester of Task Entity: PATCH method (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/requester/' . $userUser->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Requested user of Task Entity: PATCH method (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $adminProject->getId() . '/requester/' . $userUser->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        $data = $this->returnUpdateTestData();
        $data2 = $this->returnWrongTestData();

        $entity = $this->findOneAdminEntity();

        // Try to update test Entity without authorization header: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID: method PUT (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/1125874',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID of requested Project : method PUT (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/125789',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID of Requester : method PUT (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/requester/125789',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with invalid parameter task_data (not existed type of task attribute)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(),
            $data2, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  PARTIAL UPDATE SINGLE - errors
     */
    public function testPartialUpdateSingleErrors()
    {
        $data = $this->returnUpdateTestData();
        $data2 = $this->returnWrongTestData();

        $entity = $this->findOneAdminEntity();

        // Try to update test Entity without authorization header: method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID: method PATCH (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/1125874',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID of requested Project : method PATCH (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/125789',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID of Requester : method PATCH (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/requester/125789',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with invalid parameter task_data (not existed type of task attribute)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(),
            $data2, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
        parent::testDeleteSingleErrors();

        $entity = $this->findOneAdminEntity();

        // Try to delete Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
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
        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task TEST - admin is creator, admin is requested'
        ]);

        if ($task instanceof Task) {
            return $task;
        }

        return $this->createEntity();
    }

    /**
     * Return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function findOneAdminEntity()
    {
        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task TEST - admin is creator, admin is requested 2'
        ]);

        if ($task instanceof Task) {
            return $task;
        }

        return $this->createAdminsEntity();
    }

    /**
     * Create and return a single entity from db for testing CRUD: Admin's task in Admin's project
     *
     * @return mixed
     */
    public function createEntity()
    {
        $adminUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $adminProject = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);
        $webSolCompany = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Web-Solutions'
        ]);

        $task = new Task();
        $task->setTitle('Task TEST - admin is creator, admin is requested');
        $task->setDescription('Description of Task TEST');
        $task->setImportant(false);
        $task->setCreatedBy($adminUser);
        $task->setRequestedBy($adminUser);
        $task->setProject($adminProject);
        $task->setCompany($webSolCompany);

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

    /**
     * Create and return a single entity from db for testing CRUD: Admin's task in Admin's project
     *
     * @return mixed
     */
    public function createAdminsEntity()
    {
        $adminUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);
        $webSolCompany = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Web-Solutions'
        ]);

        $task = new Task();
        $task->setTitle('Task TEST - admin is creator, admin is requested 2');
        $task->setDescription('Description of Task TEST');
        $task->setImportant(false);
        $task->setCreatedBy($adminUser);
        $task->setRequestedBy($adminUser);
        $task->setCompany($webSolCompany);

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeTask('Task TEST PUT PATCH');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        $taskAttr = $this->em->getRepository('APITaskBundle:TaskAttribute')->findOneBy([
            'title' => 'input task additional attribute',
        ]);
        return [
            'title' => 'Task TEST POST',
            'description' => 'desc',
            'task_data' => [
                $taskAttr->getId() => 'some test value'
            ]
        ];
    }

    /**
     * Return Wrong data
     *
     * @return array
     */
    public function returnWrongTestData()
    {
        return [
            'title' => 'Task TEST WRONG DATA',
            'description' => 'desc',
            'task_data' => [
                25789 => 'some test value'
            ]
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
            'title' => 'Task TEST PUT PATCH',
            'description' => 'desc'
        ];
    }

    /**
     * @param $title
     */
    private function removeTask($title)
    {
        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => $title
        ]);

        if ($task instanceof Task) {
            $this->em->remove($task);
            $this->em->flush();
        }
    }
}
