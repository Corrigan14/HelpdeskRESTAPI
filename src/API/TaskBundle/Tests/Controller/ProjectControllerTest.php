<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Project;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

class ProjectControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/projects';

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
            'title' => 'Project of user 1'
        ]);

        if ($project instanceof Project) {
            return $project;
        }

        $projectArray = $this->createEntity();

        return $this->em->getRepository('APITaskBundle:Project')->find($projectArray['id']);
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        // TODO: Implement createEntity() method.
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
