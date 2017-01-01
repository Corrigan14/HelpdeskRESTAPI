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
     * GET LIST - success with filters: PROJECT, CREATOR, REQUESTED
     *
     * @return array
     */
    public function testListSuccess()
    {
        parent::testListSuccess();

        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 1 - user is creator, user is requested'
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
        $response = json_decode($this->getClient()->getResponse()->getContent() , true);
        $this->assertTrue(array_key_exists('data' , $response));

        // Load list of data of Task Entity as Admin with filter: status and project
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&status=' . $status->getId().'&project='.$userProject->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent() , true);
        $this->assertTrue(array_key_exists('data' , $response));

        // Load list of data of Task Entity as Admin with filter: createdBy
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&creator=' . $userUser->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent() , true);
        $this->assertTrue(array_key_exists('data' , $response));

        // Load list of data of Task Entity as Admin with filter: createdBy and requestedBy
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&creator=' . $userUser->getId().'&requester='.$userUser->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent() , true);
        $this->assertTrue(array_key_exists('data' , $response));

        // Load list of data of Task Entity as Admin with filter: requestedBy and company
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&requester=' . $userUser->getId().'&company='.$userCompany->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent() , true);
        $this->assertTrue(array_key_exists('data' , $response));

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


    }

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {

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
            'username' => 'user'
        ]);

        // Create Base Entity without setting of Project or Requested user
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/all/user/all', $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));

        // Create Base Entity without setting of Project or Requested user
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $adminsProject->getId() . '/user/' . $adminUser->getId(),
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
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/all/user/all', $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/all/user/all', $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Task in not existed Project, Requested to not existed user
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/125478/user/abds', $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Task with ROLE_USER in not allowed Project
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $adminProject->getId() . '/user/all',
            $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Task with invalid parameter title (title is required)
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/all/user/all', ['description' => 'desc'], [],
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

        // Update Base Task Entity: POST method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/all/user/all',
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Project of Task Entity: POST method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $adminProject->getId() . '/user/all',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Requested user of Task Entity: POST method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $adminProject->getId() . '/user/' . $userUser->getId(),
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
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/all/user/all',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Project of Task Entity: POST method (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $adminProject->getId() . '/user/all',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Requested user of Task Entity: POST method (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $adminProject->getId() . '/user/' . $userUser->getId(),
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
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/all/user/all', $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID: method PUT (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/1125874' . '/project/all/user/all',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID of requested Project : method PUT (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/125789/user/all',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/all/user/all',
            $data, [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with invalid parameter task_data (not existed type of task attribute)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/all/user/all',
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

        $entity = $this->findOneEntity();

        // Try to update test Entity without authorization header: method PUT
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/all/user/all', $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID: method PUT (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/1125874' . '/project/all/user/all',
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID of requested Project : method PUT (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/125789/user/all',
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/all/user/all',
            $data, [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with invalid parameter task_data (not existed type of task attribute)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/project/all/user/all',
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

        $task = new Task();
        $task->setTitle('Task TEST - admin is creator, admin is requested');
        $task->setDescription('Description of Task TEST');
        $task->setImportant(false);
        $task->setCreatedBy($adminUser);
        $task->setRequestedBy($adminUser);
        $task->setProject($adminProject);

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

        $task = new Task();
        $task->setTitle('Task TEST - admin is creator, admin is requested 2');
        $task->setDescription('Description of Task TEST');
        $task->setImportant(false);
        $task->setCreatedBy($adminUser);
        $task->setRequestedBy($adminUser);

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
