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
        $entity = $this->findOneEntity();

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $adminEntity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Title (title is required): method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), ['description' => 'New'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $adminEntity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Title (title has to be unique): method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), ['description' => 'New'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
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
