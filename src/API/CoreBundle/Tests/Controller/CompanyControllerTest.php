<?php

namespace API\CoreBundle\Tests\Controller;

use API\CoreBundle\Entity\Company;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class CompanyControllerTest
 *
 * @package API\CoreBundle\Tests\Controller
 */
class CompanyControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/core-bundle/companies';

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

        // Try to create a company with USER_ROLE without ACL permission to create companies
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create user as admin, invalid param. ICO (ico has to be unique)
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['title' => 'Dalsia spolocnost s nejakym nazvom', 'ico' => '1102587'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        parent::testUpdateSingleErrors();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $entity = $this->findOneEntity();

        // Try to update a company with USER_ROLE without ACL permission to create companies: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update a company with USER_ROLE without ACL permission to create companies: method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update a company with invalid param. ICO (ico has to be unique): method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $entity->getId(),
            ['ico' => '1102587'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update a company with invalid param. ICO (ico has to be unique): method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $entity->getId(),
            ['ico' => '1102587'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - success
     *
     * We are not using Base test because User Entity is not removed, just is_active param is set to 0
     */
    public function testDeleteSingleSuccess()
    {
        $entity = $this->findOneEntity();

        // Delete Entity
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
        parent::testDeleteSingleErrors();

        $entity = $this->findOneEntity();

        // Try to delete a company with USER_ROLE without ACL permission to delete companies
        $this->getClient()->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * RESTORE SINGLE - success
     *
     * We are not using Base test because User Entity is not removed, just is_active param is set to 0
     */
    public function testRestoreSingleSuccess()
    {
        $entity = $this->findOneEntity();

        // Restore Entity
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/restore/' . $entity->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * RESTORE SINGLE - errors
     */
    public function testRestoreSingleErrors()
    {
        $entity = $this->findOneEntity();

        // Try to restore Entity without authorization header
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/restore/' . $entity->getId(), [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to restore Entity with not existed ID (as Admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/restore/12548700',
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to restore a company with USER_ROLE without ACL permission to restore companies
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/restore/' . $entity->getId(), [], [],
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
            'title' => 'Test Company'
        ]);

        if ($company instanceof Company) {
            return $company;
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
        $createdCompany = new Company();
        $createdCompany->setTitle('Test Company')
            ->setIco('11025848744')
            ->setDic('1258745968444')
            ->setStreet('Cesta 125')
            ->setZip('021478')
            ->setCity('Bratislava')
            ->setCountry('Slovenska Republika');

        $this->em->persist($createdCompany);
        $this->em->flush();

        return $createdCompany;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeCompanyEntity('Test Company CREATE');
        $this->removeCompanyEntity('Test Company UPDATE');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'title' => 'Test Company CREATE',
            'ico' => '11025877',
            'ic_dph' => '11025872587225',
            'dic' => '110258725877',
            'street' => 'test street',
            'city' => 'test city',
            'zip' => '25897',
            'country' => 'test country'
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
            'title' => 'Test Company UPDATE',
            'ico' => '110258771',
            'ic_dph' => '110258172587225',
            'dic' => '1102587258177',
            'street' => 'test street',
            'city' => 'test city',
            'zip' => '25897',
            'country' => 'test country'
        ];
    }

    /**
     * @param string $title
     */
    private function removeCompanyEntity(string $title)
    {
        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => $title,
        ]);

        if (null !== $company) {
            $this->em->remove($company);
            $this->em->flush();
        }

        $company = $this->em->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => $title,
        ]);

        $this->assertEquals(null, $company);
    }
}
