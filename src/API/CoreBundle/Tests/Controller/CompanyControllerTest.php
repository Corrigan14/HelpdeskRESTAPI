<?php

namespace API\CoreBundle\Tests\Controller;

/**
 * Class CompanyControllerTest
 *
 * @package API\CoreBundle\Tests\Controller
 */
class CompanyControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/core-bundle/companies';

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

        if (null !== $company) {
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
        $this->getClient(true)->request('POST', $this->getBaseUrl(),
            ['title' => 'Test Company', 'ico' => '1102545', 'dic' => '11452', 'street' => 'test street', 'city' => 'test city', 'zip' => '25897', 'country' => 'test country'],
            [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(201, $this->getClient()->getResponse()->getStatusCode());

        // Check if Entity was created
        $createdCompany = json_decode($this->getClient()->getResponse()->getContent(), true);
        $createdCompany = $createdCompany['data'];
        $this->assertTrue(array_key_exists('id', $createdCompany));

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
