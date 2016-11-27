<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Project;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

class ProjectControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/projects';

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
        // TODO: Implement removeTestEntity() method.
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
}
