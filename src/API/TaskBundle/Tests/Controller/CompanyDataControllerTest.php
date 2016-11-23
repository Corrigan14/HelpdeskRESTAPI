<?php

namespace API\TaskBundle\Tests\Controller;

use API\CoreBundle\Entity\Company;
use API\TaskBundle\Entity\CompanyAttribute;
use API\TaskBundle\Entity\CompanyData;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

class CompanyDataControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/company-data';

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
        $data = $this->em->getRepository('APITaskBundle:CompanyData')->findOneBy([
            'value' => 'String DATA'
        ]);

        if ($data instanceof CompanyData) {
            return $data;
        }

        $dataArray = $this->createEntity();

        return $this->em->getRepository('APITaskBundle:CompanyData')->find($dataArray['id']);
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $company = $this->em->getRepository('APITaskBundle:Company')->findOneBy([
            'title' => 'Web-solutions'
        ]);

        $attribute = $this->em->getRepository('APITaskBundle:Company')->findOneBy([
            'title' => 'input company additional attribute'
        ]);

        if (($company instanceof Company) && ($attribute instanceof CompanyAttribute)) {
            $this->getClient(true)->request('POST', $this->getBaseUrl() . '/company/' . $company->getId() . '/company-attribute/' . $attribute->getId(),
                ['value' => 'some value'], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
            $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

            // Check if Entity was created
            $createdData = json_decode($this->getClient()->getResponse()->getContent(), true);
            $createdData = $createdData['data'];
            $this->assertTrue(array_key_exists('id', $createdData));

            return $createdData;
        }
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeCompanyDataEntity('Created some value');
        $this->removeCompanyDataEntity('Updated some value');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'value' => 'Created some value'
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
            'value' => 'Updated some value'
        ];
    }

    private function removeCompanyDataEntity($value)
    {
        $data = $this->em->getRepository('APITaskBundle:CompanyData')->findOneBy([
            'value' => $value
        ]);

        if ($data instanceof CompanyData) {
            $this->em->remove($data);
            $this->em->flush();
        }

        $data = $this->em->getRepository('APITaskBundle:CompanyData')->findOneBy([
            'value' => $value
        ]);

        $this->assertEquals(null, $data);
    }
}
