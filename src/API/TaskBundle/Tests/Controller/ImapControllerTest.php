<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Imap;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class ImapControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class ImapControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/imap';

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        parent::testGetSingleErrors();

        $entity = $this->findOneEntity();

        // Try to load entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  POST SINGLE - success
     */
    public function testPostSingleSuccess()
    {
        $data = $this->returnPostTestData();
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        // Load list of data of Entity (as Admin)
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $project->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
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
        $errorData = $this->returnErrorPostTestData();
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        // Try to create test Entity, without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $project->getId(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $project->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Email
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/project/' . $project->getId(), $errorData, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * UPDATE SINGLE - success
     */
    public function testUpdateSingleSuccess()
    {
        parent::testUpdateSingleSuccess();

        $entity = $this->findOneEntity();
        $data = $this->returnUpdateTestData();
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 2'
        ]);

        // Update Entity, update Project of entity
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $project->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        parent::testUpdateSingleErrors();
        $data = $this->returnUpdateTestData();
        $errorData = $this->returnErrorPostTestData();
        $entity = $this->findOneEntity();
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 2'
        ]);

        // Try to update test Entity without authorization header
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $project->getId(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/1254' . $project->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with invalid data
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/project/' . $project->getId(), $errorData, [],
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
        $imap = $this->em->getRepository('APITaskBundle:Imap')->findOneBy([
            'host' => 'test.lanhelpdesk.com'
        ]);

        if ($imap instanceof Imap) {
            return $imap;
        }

        return $this->createEntity();
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $userProject = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        $imap = new Imap();
        $imap->setHost('test.lanhelpdesk.com');
        $imap->setPort(143);
        $imap->setName('test@lanhelpdesk.com');
        $imap->setPassword('jjkjkhgf@18');
        $imap->setSsl(false);
        $imap->setInboxEmail('test@lanhelpdesk.com');
        $imap->setMoveEmail('done@done.sk');
        $imap->setIgnoreCertificate(true);
        $imap->setProject($userProject);

        $this->em->persist($imap);
        $this->em->flush();

        return $imap;
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
            'host' => 'testpost.lanhelpdesk.com',
            'port' => 124,
            'name' => 'test',
            'password' => 'test',
            'ssl' => false,
            'inbox_email' => 'email@email.com',
            'move_email' => 'email@email.com',
            'ignore_certificate' => true
        ];
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnErrorPostTestData()
    {
        return [
            'host' => 'testpost.lanhelpdesk.com',
            'port' => 124,
            'name' => 'test',
            'password' => 'test',
            'ssl' => false,
            'inbox_email' => 'emailemail.com',
            'move_email' => 'emailemail.com',
            'ignore_certificate' => true
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
            'host' => 'testupdate.lanhelpdesk.com',
            'port' => 25,
            'name' => 'test',
            'password' => 'test',
            'ssl' => false,
            'inbox_email' => 'email@email.com',
            'move_email' => 'email@email.com',
            'ignore_certificate' => true
        ];
    }
}
