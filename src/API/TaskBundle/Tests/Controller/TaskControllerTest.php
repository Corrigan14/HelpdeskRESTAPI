<?php

namespace API\TaskBundle\Tests\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
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

        $creatorUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $usersProject = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1',
        ]);

        // Load list of data of Task Entity as Admin with filter: creator
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&creator=' . $creatorUser->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect two Entities
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $count = 0;
        foreach ($response['data'] as $res) {
            $count++;
        }
        $this->assertEquals(2, $count);

        // Load list of data of Task Entity as Admin with filter: creator, requested
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&creator=' . $creatorUser->getId() . '&requested=' . $creatorUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect two Entities
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $count = 0;
        foreach ($response['data'] as $res) {
            $count++;
        }
        $this->assertEquals(1, $count);

        // Load list of data of Task Entity as User with filter: project (user's project)
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&project=' . $usersProject->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect two Entities
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $count = 0;
        foreach ($response['data'] as $res) {
            $count++;
        }
        $this->assertEquals(2, $count);

        // Load list of data of Task Entity
    }

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
        parent::testListErrors();

        $adminsProject = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin 2',
        ]);

        // Try to load list of entities with filter creator set to not existed entity
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&creator=1257489', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to load list of entities: User can see a list of tasks of projects without access to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '?page=1&project=' . $adminsProject->getId(), [], [],
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
     * ADD FOLLOWER TO TASK - success
     */
    public function testAddFollowerToTaskSuccess()
    {
        $task = $this->findOneAdminEntity();
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/user/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // At least one follower could be returned
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertNotNull(count($response));
    }

    /**
     * ADD FOLLOWER TO TASK - errors
     */
    public function testAddFollowerToTaskErrors()
    {
        $task = $this->findOneAdminEntity();
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);

        // Try to add Follower without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/user/' . $userUser->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Follower to not existed Task
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/1245680' . '/user/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to delete Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/user/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE FOLLOWER FROM TASK - success
     */
    public function testRemoveFollowerFromTaskSuccess()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        // Add follower to task
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);
        $this->addFollowerToTask($task, $userUser);

        // Remove follower
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/user/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE FOLLOWER FROM TASK - errors
     */
    public function testRemoveFollowerFromTaskErrors()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        // Add follower to task
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);
        $this->addFollowerToTask($task, $userUser);

        // Try to remove Follower without authorization header
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/user/' . $userUser->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to remove Follower from not existed Task
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/125874' . '/user/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to remove Follower with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/user/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ADD TAG TO TASK - success
     */
    public function testAddTagToTaskSuccess()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var Tag $tag */
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Another Admin Public Tag'
        ]);

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ADD TAG TO TASK - errors
     */
    public function testAddTagToTaskErrors()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var Tag $tag */
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Another Admin Public Tag'
        ]);

        // Try to add Tag without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Tag to not existed Task
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/125874' . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Tag with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }


    /**
     * REMOVE TAG FROM TASK - success
     */
    public function testRemoveTagFromTaskSuccess()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var Tag $tag */
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Another Admin Public Tag'
        ]);
        $this->addTagToTask($task, $tag);

        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE TAG FROM TASK - errors
     */
    public function testRemoveTagFromTaskErrors()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var Tag $tag */
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Another Admin Public Tag'
        ]);
        $this->addTagToTask($task, $tag);

        // Try to remove Tag without authorization header
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to remove Tag to not existed Task
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/125874' . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Tag with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ASSIGN USER TO TASK - success
     */
    public function testCreateAssignUserToTaskSuccess()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var User $user */
        $user = $task->getCreatedBy();

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ASSIGN USER TO TASK - errors
     */
    public function testCreateAssignUserToTaskErrors()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var User $user */
        $user = $task->getCreatedBy();

        // Remove assigned admin user
        $this->removeTaskHasAssignedUser($task, $user);

        // Try to create Entity without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to assign not Existed User to not Existed Task
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/1254' . $task->getId() . '/assign-user/2598' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to assign User to task with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to assign User to task with wrong data: status date could be Datetime Type
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            ['status_date' => '12.5.2016'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to assign User to task two times
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::BAD_REQUEST_CODE, $this->getClient()->getResponse()->getStatusCode());
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

    /**
     * @param Task $task
     * @param User $follower
     * @return bool
     */
    private function addFollowerToTask(Task $task, User $follower)
    {
        $followers = $task->getFollowers();
        $followers->toArray();

        foreach ($followers as $foll) {
            if ($foll === $follower) {
                return true;
            }
        }
        $task->addFollower($follower);
        $this->em->persist($task);
        $this->em->flush();

        return true;
    }

    /**
     * @param Task $task
     * @param Tag $tag
     * @return bool
     */
    private function addTagToTask(Task $task, Tag $tag):bool
    {
        $tags = $task->getTags();

        if (in_array($tag, $tags->toArray())) {
            return true;
        }

        $task->addTag($tag);
        $this->em->persist($task);
        $this->em->flush();

        return true;
    }

    /**
     * @param Task $task
     * @param User $user
     */
    private function removeTaskHasAssignedUser(Task $task, User $user)
    {
        $taskHasAssignedUser = $this->em->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy([
            'user' => $user,
            'task' => $task
        ]);

        if ($taskHasAssignedUser instanceof TaskHasAssignedUser) {
            $task->removeTaskHasAssignedUser($taskHasAssignedUser);
            $user->removeTaskHasAssignedUser($taskHasAssignedUser);
            $this->em->remove($taskHasAssignedUser);
            $this->em->persist($task);
            $this->em->persist($user);
            $this->em->flush();
        }
    }
}
