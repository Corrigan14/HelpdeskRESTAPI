<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Status;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class StatusControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class StatusControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/status';

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
        $status = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => 'New',
        ]);

        if ($status instanceof Status) {
            return $status;
        }

        $statusArray = $this->createEntity();

        return $this->em->getRepository('APITaskBundle:Status')->find($statusArray['id']);
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['title' => 'Created Status'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(201, $this->getClient()->getResponse()->getStatusCode());

        // Check if Entity was created
        $createdStatus = json_decode($this->getClient()->getResponse()->getContent(), true);
        $createdStatus = $createdStatus['data'];
        $this->assertTrue(array_key_exists('id', $createdStatus));

        return $createdStatus;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeStatusEntity('Created Status');
        $this->removeStatusEntity('Updated Status');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'title' => 'Created Status'
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
            'title' => 'Updated Status'
        ];
    }


    /**
     * @param string $title
     */
    private function removeStatusEntity(string $title)
    {
        // If test Status exists, remove
        $status = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => $title,
        ]);

        if ($status instanceof Status) {
            $this->em->remove($status);
            $this->em->flush();
        }

        $status = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => $title,
        ]);

        $this->assertEquals(null, $status);
    }
}
