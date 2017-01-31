<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Services\FilterAttributeOptions;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class FilterControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class FilterControllerTest extends ApiTestCase
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

        $publicData = $this->returnPublicPostTestData();
        $invalidData = $this->returnInvalidData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Try to add Entity with ROLE_USER which hasn't permission to create PUBLIc filter this action
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $publicData, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Entity with invalid parameters - not existed Filter attribute
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $invalidData, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * POST SINGLE FILTER FOR PROJECT - success
     */
    public function testCreateFilterForProjectSuccess()
    {
        $data = $this->returnPostProjectTestData();
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Create Entity (as admin)
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $project->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));
    }

    /**
     *  POST SINGLE FILTER FOR PROJECT - errors
     */
    public function testCreateFilterForProjectErrors()
    {
        $data = $this->returnPostProjectTestData();
        $invalidData = $this->returnInvalidData();
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Try to create test Entity, without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $project->getId(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Entity with not existed project Id
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/1254' . $project->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Entity with invalid parameters - not existed Filter attribute
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $project->getId(), $invalidData, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        parent::testUpdateSingleErrors();

        $data = $this->returnUpdateTestData();
        $invalidData = $this->returnInvalidData();
        $filter = $this->findOneEntity();

        // Try to update Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $filter->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Entity with invalid parameters - not existed Filter attribute
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $filter->getId(), $invalidData, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }


    /**
     * UPDATE SINGLE PROJECT FILTER - success
     */
    public function testUpdateProjectFilterSuccess()
    {
        $data = $this->returnUpdateTestData();
        /** @var Filter $filter */
        $filter = $this->findOneEntity();
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        // Update Entity: POST method (as admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $filter->getId() . '/project/' . $project->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }


    /**
     * UPDATE SINGLE PROJECT FILTER - errors
     */
    public function testUpdateProjectFilterErrors()
    {
        $data = $this->returnUpdateTestData();
        $invalidData = $this->returnInvalidData();
        /** @var Filter $filter */
        $filter = $this->findOneEntity();
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        // Try to update test Entity without authorization header
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $filter->getId() . '/project/' . $project->getId(),
            $data, [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update not existed filter Entity
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/1254' . $filter->getId() . '/project/' . $project->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed project ID
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $filter->getId() . '/project/1254' . $project->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $filter->getId() . '/project/' . $project->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Entity with invalid parameters - not existed Filter attribute
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $filter->getId() . '/project/' . $project->getId(),
            $invalidData, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
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
        $admin = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        $filterData = [
            FilterAttributeOptions::CREATOR => 'current-user'
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
        $filter->setIconClass('&#xE858;');

        $this->em->persist($filter);
        $this->em->flush();

        return $filter;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeFilter('Admins PRIVATE Filter for TEST');
        $this->removeFilter('Post test filter');
        $this->removeFilter('Post PROJECT test filter');
        $this->removeFilter('Update test filter');
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

        $filter = [
            FilterAttributeOptions::STATUS => $status->getId(),
            FilterAttributeOptions::CREATOR => $user->getId() . ',' . $admin->getId()
        ];

        return [
            'title' => 'Post test filter',
            'filter' => $filter,
            'public' => false,
            'report' => false,
            'default' => false,
            'icon_class' => '&#xE858;'
        ];
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPublicPostTestData()
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

        $filter = [
            FilterAttributeOptions::STATUS => $status->getId(),
            FilterAttributeOptions::CREATOR => $user->getId() . ',' . $admin->getId()
        ];

        return [
            'title' => 'Post test filter',
            'filter' => $filter,
            'public' => true,
            'report' => false,
            'default' => false,
            'icon_class' => '&#xE858;'
        ];
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostProjectTestData()
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

        $filter = [
            FilterAttributeOptions::STATUS => $status->getId(),
            FilterAttributeOptions::CREATOR => $user->getId() . ',' . $admin->getId()
        ];

        return [
            'title' => 'Post PROJECT test filter',
            'filter' => $filter,
            'public' => false,
            'report' => false,
            'default' => false,
            'icon_class' => '&#xE858;'
        ];
    }


    /**
     * Return Post data
     *
     * @return array
     */
    public function returnInvalidData()
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

        $filter = [
            FilterAttributeOptions::STATUS => $status->getId(),
            FilterAttributeOptions::CREATOR => $user->getId() . ',' . $admin->getId(),
            'test' => 'test'
        ];

        return [
            'title' => 'Post test filter',
            'filter' => $filter,
            'public' => false,
            'report' => false,
            'default' => false,
            'icon_class' => '&#xE858;'
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
            'title' => 'Update test filter',
            'report' => false,
            'default' => false
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
