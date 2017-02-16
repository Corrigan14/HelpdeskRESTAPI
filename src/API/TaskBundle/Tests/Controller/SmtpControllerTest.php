<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Smtp;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class SmtpControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class SmtpControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/smtp';

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
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        parent::testPostSingleErrors();

        $data = $this->returnPostTestData();
        $errorData = $this->returnErrorPostTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Try to create entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Email
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $errorData, [],
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
        $errorData = $this->returnErrorPostTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $entity = $this->findOneEntity();

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Email: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), $errorData, [],
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
        $smtp = $this->em->getRepository('APITaskBundle:Smtp')->findOneBy([
            'host' => 'Host'
        ]);

        if ($smtp instanceof Smtp) {
            return $smtp;
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
        $smtp = new Smtp();
        $smtp->setHost('Host');
        $smtp->setPort(3306);
        $smtp->setEmail('mb@web-solutions.sk');
        $smtp->setName('test');
        $smtp->setPassword('test');
        $smtp->setSsl(true);
        $smtp->setTls(false);

        $this->em->persist($smtp);
        $this->em->flush();

        return $smtp;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeSmtpEntity('Host TEST');
        $this->removeSmtpEntity('Host TEST UPDATE');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'host' => 'Host TEST',
            'port' => '3306',
            'email' => 'vb@verts.sk',
            'name' => 'TestName',
            'password' => 'TestPassword',
            'ssl' => true,
            'tls' => false
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
            'host' => 'Host TEST',
            'port' => '3306',
            'email' => 'vbverts.sk',
            'name' => 'TestName',
            'password' => 'TestPassword',
            'ssl' => true,
            'tls' => false
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
            'host' => 'Host TEST UPDATE',
            'port' => '3306',
            'email' => 'vb@verts.sk',
            'name' => 'TestName',
            'password' => 'TestPassword',
            'ssl' => true,
            'tls' => false
        ];
    }

    /**
     * @param $host
     */
    private function removeSmtpEntity($host)
    {
        $smtp = $this->em->getRepository('APITaskBundle:Smtp')->findOneBy([
            'host' => $host
        ]);

        if ($smtp instanceof Smtp) {
            $this->em->remove($smtp);
            $this->em->flush();
        }
    }
}
