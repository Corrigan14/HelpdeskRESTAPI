<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Services\FilterAttributeOptions;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class FixtureControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class FixtureControllerTest extends ApiTestCase
{
    const  BASE_URL = '/api/v1/task-bundle/filters';

    /**
     *  GET SINGLE - errors
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
     * POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        parent::testPostSingleErrors();

        $data = $this->returnPostTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Try to add Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Entity with invalid parameters
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['title' => 'test', 'status' => 28], [],
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
        $filter = $this->em->getRepository('APITaskBundle:Filter')->findOneBy([
            'title' => 'Admins PRIVATE Filter for TEST'
        ]);

        if (!$filter instanceof Filter) {
            $filter = $this->createEntity();
        }

        return $filter;
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $status = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => 'new'
        ]);

        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $admin = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        $filterData = [
            FilterAttributeOptions::STATUS => $status->getId(),
            FilterAttributeOptions::CREATOR => $user->getId(), $admin->getId(),
            FilterAttributeOptions::ARCHIVED => TRUE
        ];

        $filter = new Filter();
        $filter->setTitle('Admins PRIVATE Filter for TEST');
        $filter->setFilter($filterData);
        $filter->setPublic(false);
        $filter->setCreatedBy($admin);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(false);
        $filter->setProject($project);

        $this->em->persist($filter);
        $this->em->flush();

        return $filter;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeFilter('Admins PRIVATE Filter where status=new, creator = admin, user, archived = true');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        $status = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => 'new'
        ]);

        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $admin = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        return [
            FilterAttributeOptions::STATUS => $status->getId(),
            FilterAttributeOptions::CREATOR => $user->getId(), $admin->getId(),
            FilterAttributeOptions::ARCHIVED => TRUE
        ];
    }

    /**
     * Return Update data
     *
     * @return array
     */
    public function returnUpdateTestData()
    {
        $status = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => 'new'
        ]);

        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $admin = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        return [
            FilterAttributeOptions::STATUS => $status->getId(),
            FilterAttributeOptions::CREATOR => $user->getId(), $admin->getId(),
            FilterAttributeOptions::ARCHIVED => TRUE
        ];
    }

    /**
     * @param string $title
     */
    private function removeFilter(string $title)
    {
        $filter = $this->em->getRepository('APITaskBundle:Filter')->findOneBy([
            'title' => $title
        ]);

        if ($filter instanceof Filter) {
            $this->em->remove($filter);
            $this->em->flush();
        }
    }
}
