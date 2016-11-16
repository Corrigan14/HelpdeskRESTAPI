<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\CompanyAttribute;
use API\TaskBundle\Services\VariableHelper;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class CompanyAttributeControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class CompanyAttributeControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/company-attributes';

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        parent::testGetSingleErrors();

        $entity = $this->findOneEntity();

        // Try to load entity with ROLE_USER which hasn't permission to this action
        $this->getClient()->request('GET', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
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

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        // Try to create entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Title (title is required)
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['type' => 'input'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Title (title has to be unique)
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['title' => 'select company additional attribute'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Type (Type has to be one of the const in VariableHelper)
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['title' => 'select company additional attribute 255', 'type' => 'nejkay typ'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
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

        $entity = $this->findOneEntity();

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with invalid parameter Title (title has to be unique): method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), ['title' => 'select company additional attribute'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with invalid parameter Type (Type has to be one of the const in VariableHelper): method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), ['title' => 'select company additional attribute 2565', 'type' => 'nejkay typ'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with invalid parameter Title (title has to be unique): method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), ['title' => 'select company additional attribute'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with invalid parameter Type (Type has to be one of the const in VariableHelper): method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), ['title' => 'select company additional attribute 255', 'type' => 'nejkay typ'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }


    /**
     * DELETE SINGLE - success
     *
     * We are not using Base test because Company Attribute Entity is not removed, just is_active param is set to 0
     */
    public function testDeleteSingleSuccess()
    {
        $entity = $this->findOneEntity();

        // Delete Entity
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Check if is_active param is 0
        $isActiveParam = $entity->getIsActive();
//        $this->assertEquals(false,$isActiveParam);
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
        $ca = $this->em->getRepository('APITaskBundle:CompanyAttribute')->findOneBy([
            'title' => 'CREATED company additional attribute',
        ]);

        if ($ca instanceof CompanyAttribute) {
            return $ca;
        }

        $caArray = $this->createEntity();

        return $this->em->getRepository('APITaskBundle:CompanyAttribute')->find($caArray['id']);
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $this->getClient(true)->request('POST', $this->getBaseUrl(),
            ['title' => 'CREATED company additional attribute', 'type' => VariableHelper::TEXT_AREA], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(201, $this->getClient()->getResponse()->getStatusCode());

        // Check if Entity was created
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $createdCa = $response['data'];
        $this->assertTrue(array_key_exists('id', $createdCa));

        return $createdCa;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeCa('POST company additional attribute');
        $this->removeCa('UPDATE company additional attribute');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'title' => 'POST company additional attribute',
            'type' => VariableHelper::SIMPLE_SELECT,
            'options' => [
                'options1' => 'option1',
                'options2' => 'option2',
            ]
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
            'title' => 'UPDATE company additional attribute',
            'type' => VariableHelper::INTEGER_NUMBER
        ];
    }

    /**
     * @param string $title
     */
    private function removeCa($title)
    {
        $ca = $this->em->getRepository('APITaskBundle:CompanyAttribute')->findOneBy([
            'title' => $title,
        ]);

        if (null !== $ca) {
            $this->em->remove($ca);
            $this->em->flush();
        }

        $ca = $this->em->getRepository('APITaskBundle:CompanyAttribute')->findOneBy([
            'title' => $title,
        ]);

        $this->assertEquals(null, $ca);
    }
}
