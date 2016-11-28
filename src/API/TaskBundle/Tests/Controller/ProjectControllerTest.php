<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Project;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

class ProjectControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/projects';

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        parent::testGetSingleErrors();

        $entity = $this->findOneAdminEntity();

        // Try to load entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * POST SINGLE - success
     */
    public function testPostSingleErrors()
    {
        parent::testPostSingleErrors();

        $data = $this->returnPostTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Try to create entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Title (title is required)
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['description' => 'Description'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        parent::testUpdateSingleErrors();

        $data = $this->returnUpdateTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $adminEntity = $this->findOneAdminEntity();

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $adminEntity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $adminEntity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess()
    {
        $entity = $this->findOneEntity();

        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - error
     */
    public function testDeleteSingleErrors()
    {
        parent::testDeleteSingleErrors();

        $entity = $this->findOneAdminEntity();

        // Try to delete entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * RESTORE SINGLE - success
     */
    public function testRestoreSingleSuccess()
    {
        $entity = $this->findOneInActiveEntity();

        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/restore', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Check if isActive param is set to 0
        $response = json_decode($this->getClient()->getResponse()->getContent() , true);
        $this->assertEquals(true , $response['data']['is_active']);
    }

    /**
     * RESTORE SINGLE - error
     */
    public function testRestoreSingleErrors()
    {
        $entity = $this->findOneAdminEntity();

        // Try to restore entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/restore', [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to restore  Entity without authorization header
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId() . '/restore', [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to restore  Entity with not existed ID (as Admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/1125874' . '/restore', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());
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
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'project NEW'
        ]);

        if ($project instanceof Project) {
            return $project;
        }

        $projectArray = $this->createEntity();

        return $this->em->getRepository('APITaskBundle:Project')->find($projectArray['id']);
    }

    /**
     * Return a single ADMIN project entity from db
     *
     * @return mixed
     */
    public function findOneAdminEntity()
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin 2'
        ]);

        if ($project instanceof Project) {
            return $project;
        }
    }

    /**
     * Return a single ADMIN project entity with inactive status from db
     *
     * @return mixed
     */
    public function findOneInActiveEntity()
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin 3 - inactive'
        ]);

        if ($project instanceof Project) {
            return $project;
        }
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['title' => 'project NEW'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Check if Entity was created
        $createdProject = json_decode($this->getClient()->getResponse()->getContent(), true);
        $createdProject = $createdProject['data'];
        $this->assertTrue(array_key_exists('id', $createdProject));

        return $createdProject;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeProject('project CREATE');
        $this->removeProject('project UPDATE');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'title' => 'project CREATE',
            'description' => 'short description',
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
            'title' => 'project UPDATE',
            'description' => 'short description',
        ];
    }

    /**
     * @param $title
     */
    private function removeProject($title)
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => $title
        ]);

        if ($project instanceof Project) {
            $this->em->remove($project);
            $this->em->flush();
        }

        // Check if entity was removed
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => $title
        ]);

        $this->assertEquals(null, $project);
    }
}
