<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\CompanyAttribute;
use API\TaskBundle\Services\VariableHelper;
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
            'title' => 'input company additional attribute',
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
