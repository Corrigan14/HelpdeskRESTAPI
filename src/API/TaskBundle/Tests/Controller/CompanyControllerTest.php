<?php

namespace API\TaskBundle\Tests\Controller;

use API\CoreBundle\Entity\Company;
use API\TaskBundle\Entity\CompanyAttribute;
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
        return [];
    }

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
        return [];
    }

    /**
     * GET SINGLE - success
     */
    public function testGetSingleSuccess()
    {
        /** @var Company $company */
        $company = $this->findOneEntity();

        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $company->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links and company_data param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('data', $response));
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('company_data', $response));
    }

    /**
     * POST SINGLE - success
     */
    public function testPostSingleSuccess()
    {
        $companyAttribute = $this->em->getRepository('APITaskBundle:CompanyAttribute')->findOneBy([
            'title' => 'input company additional attribute'
        ]);

        if ($companyAttribute instanceof CompanyAttribute) {
            $this->getClient(true)->request('POST', $this->getBaseUrl(),
                ['title' => 'Extended company NEW', 'company_data' => [$companyAttribute->getId() => 'value 1']], [],
                ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
            $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

            // Response should contain some company_data
            $response = json_decode($this->getClient()->getResponse()->getContent(), true);
            $responseCompanyData = $response['company_data'];
            $this->assertCount(1, $responseCompanyData);
        }

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

        // Try to create entity with invalid parameter company_data[attribute id] (attribute has to exists)
        $this->getClient(true)->request('POST', $this->getBaseUrl(),
            ['title' => 'Extended company', 'company_data' => [2258 => 'value 1']], [],
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

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $entity = $this->findOneEntity();

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with invalid parameter company_data[attribute id] (attribute has to exists)
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(),
            ['company_data' => [2258 => 'value 1']], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with invalid parameter company_data[attribute id] (attribute has to exists)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(),
            ['company_data' => [2258 => 'value 1']], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess()
    {
        return [];
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
        return [];
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
        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Extended company CREATE',
        ]);

        if ($company instanceof Company) {
            return $company;
        }

        $companyArray = $this->createEntity();

        return $this->em->getRepository('APICoreBundle:Company')->find($companyArray['id']);
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $companyAttribute = $this->em->getRepository('APITaskBundle:CompanyAttribute')->findOneBy([
            'title' => 'input company additional attribute'
        ]);

        if ($companyAttribute instanceof CompanyAttribute) {
            $this->getClient(true)->request('POST', $this->getBaseUrl(),
                $this->returnPostTestData(), [],
                ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
            $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

            // Check if Entity was created
            $createdCompany = json_decode($this->getClient()->getResponse()->getContent(), true);
            $createdCompany = $createdCompany['data'];
            $this->assertTrue(array_key_exists('id', $createdCompany));

            return $createdCompany;
        } else {
            return $this->em->getRepository('APICoreBundle:Company')->findOneBy([
                'title' => 'Web-solutions'
            ]);
        }
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeCompanyEntity('Extended company CREATE');
        $this->removeCompanyEntity('Extended company UPDATE');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        $companyAttribute = $this->em->getRepository('APITaskBundle:CompanyAttribute')->findOneBy([
            'title' => 'input company additional attribute'
        ]);

        return [
            'title' => 'Extended company CREATE',
            'company_data' => [
                $companyAttribute->getId() => 'value 22'
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
        $companyAttribute = $this->em->getRepository('APITaskBundle:CompanyAttribute')->findOneBy([
            'title' => 'input company additional attribute'
        ]);

        return [
            'title' => 'Extended company UPDATE',
            'company_data' => [
                $companyAttribute->getId() => 'value 33'
            ]
        ];
    }

    /**
     * @param string $title
     */
    private function removeCompanyEntity(string $title)
    {
        // If test Company exists, remove
        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => $title,
        ]);

        if ($company) {
            $this->em->remove($company);
            $this->em->flush();
        }

        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => $title,
        ]);

        $this->assertEquals(null, $company);
    }
}
