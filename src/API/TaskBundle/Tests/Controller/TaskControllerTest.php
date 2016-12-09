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
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        // TODO: Implement removeTestEntity() method.
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        return [
            'title' => 'Task TEST POST - user is creator',
            'description' => 'desc',
            'createdBy' => $userUser
        ];
    }

    /**
     * Return Update data
     *
     * @return array
     */
    public function returnUpdateTestData()
    {
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        return [
            'title' => 'Task TEST PUT PATCH - user is creator',
            'description' => 'desc',
            'createdBy' => $userUser
        ];
    }
}
