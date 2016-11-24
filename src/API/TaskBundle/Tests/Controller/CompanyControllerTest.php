<?php

namespace API\TaskBundle\Tests\Controller;

use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class CompanyControllerTest - extension to core CompanyController
 *
 * @package API\TaskBundle\Tests\Controller
 */
class CompanyControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/company-extension';

    /**
     * GET LIST - success
     *
     * @return array
     */
    public function testListSuccess()
    {
    }

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
    }

    /**
     * GET SINGLE - success
     */
    public function testGetSingleSuccess()
    {
    }

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
    }

    /**
     * POST SINGLE - success
     */
    public function testPostSingleSuccess()
    {
        $this->getClient(true)->request('POST',$this->getBaseUrl(),
            ['title'=>'Extended company', 'company_data'=>['1'=>'value 1', '2'=>'value 2']],[],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE,$this->getClient()->getResponse()->getStatusCode());

        // Response should contain some company_data
        $response = json_decode($this->getClient()->getResponse()->getContent() , true);
        $responseCompanyData = $response['company_data'];
        $this->assertEquals(!null,$responseCompanyData);

    }

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
    }

    /**
     * UPDATE SINGLE - success
     */
    public function testUpdateSingleSuccess()
    {
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
    }

    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess()
    {
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
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
        // TODO: Implement findOneEntity() method.
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
        // TODO: Implement returnPostTestData() method.
    }

    /**
     * Return Update data
     *
     * @return array
     */
    public function returnUpdateTestData()
    {
        // TODO: Implement returnUpdateTestData() method.
    }
}
