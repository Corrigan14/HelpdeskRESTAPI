<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\InvoiceableItem;
use API\TaskBundle\Entity\Task;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class InvoiceableItemControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class InvoiceableItemControllerTest extends ApiTestCase
{
    /**
     * POST SINGLE - success
     */
    public function testPostSingleSuccess()
    {
        $data = $this->returnPostTestData();
        $ksUnit = $this->em->getRepository('APITaskBundle:Unit')->findOneBy([
            'title' => 'Kus'
        ]);

        // Create Entity (as admin)
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/unit/' . $ksUnit->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
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
        $ksUnit = $this->em->getRepository('APITaskBundle:Unit')->findOneBy([
            'title' => 'Kus'
        ]);

        // Try to create test Entity, without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/unit/' . $ksUnit->getId(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/unit/' . $ksUnit->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid - missed parameters
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/unit/' . $ksUnit->getId(), ['title' => 'test 2'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * UPDATE SINGLE - success
     */
    public function testUpdateSingleSuccess()
    {
        $data = $this->returnUpdateTestData();
        $entity = $this->findOneEntity();
        $kgUnit = $this->em->getRepository('APITaskBundle:Unit')->findOneBy([
            'title' => 'Kilogram'
        ]);

        // Update Entity: POST method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(),
            $data, [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Update Unit of Entity: POST method (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/unit/' . $kgUnit->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        $data = $this->returnUpdateTestData();
        $entity = $this->findOneEntity();
        $kgUnit = $this->em->getRepository('APITaskBundle:Unit')->findOneBy([
            'title' => 'Kilogram'
        ]);

        // Try to update test Entity without authorization header: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Unit of test Entity without authorization header: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/unit/' . $kgUnit->getId(),
            $data);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update test Entity with not existed ID: method PUT (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/1125874', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Unit of Entity with not existed ID: method PUT (as admin)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId() . '/unit/1255478', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Unit of Entity  with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), $data, [],
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
        $invoiceableItem = $this->findOneEntity();
        $task = $invoiceableItem->getTask();

        if ($task instanceof Task) {
            return '/api/v1/task-bundle/tasks/' . $task->getId() . '/invoiceable-items';
        }

        return false;
    }

    /**
     * Return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function findOneEntity()
    {
        $invoiceableItem = $this->em->getRepository('APITaskBundle:InvoiceableItem')->findOneBy([
            'title' => 'Keyboard'
        ]);

        if ($invoiceableItem instanceof InvoiceableItem) {
            return $invoiceableItem;
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
        $ksUnit = $this->em->getRepository('APITaskBundle:Unit')->findOneBy([
            'title' => 'Kus'
        ]);

        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 1'
        ]);

        $invoiceableItem = new InvoiceableItem();
        $invoiceableItem->setTitle('Keyboard');
        $invoiceableItem->setAmount(2);
        $invoiceableItem->setUnitPrice(50);
        $invoiceableItem->setTask($task);
        $invoiceableItem->setUnit($ksUnit);

        $this->em->persist($invoiceableItem);
        $this->em->flush();

        return $invoiceableItem;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {

    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'title' => 'test post invoiceable entity',
            'amount' => 10,
            'unit_price' => 15
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
            'title' => 'test update invoiceable entity',
            'amount' => 10,
            'unit_price' => 15
        ];
    }
}