<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\SystemSettings;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class SystemSettingsControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class SystemSettingsControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/system-settings';

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
        $systemSetting = $this->em->getRepository('APITaskBundle:SystemSettings')->findOneBy([
            'title' => 'Test System setting'
        ]);

        if ($systemSetting instanceof SystemSettings) {
            return $systemSetting;
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
        $systemSetting = new SystemSettings();
        $systemSetting->setTitle('Test System setting');
        $systemSetting->setValue('Value');
        $systemSetting->setIsActive(true);

        $this->em->persist($systemSetting);
        $this->em->flush();

        return $systemSetting;
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
            'title' => 'Test CREATE System setting',
            'value' => 'Value',
            'is_active' => true
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
            'title' => 'Test UPDATE System setting',
            'value' => 'Value',
            'is_active' => true
        ];
    }
}
