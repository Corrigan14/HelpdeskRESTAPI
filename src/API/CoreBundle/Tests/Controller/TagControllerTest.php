<?php

namespace API\CoreBundle\Tests\Controller;

/**
 * Class TagControllerTest
 * @package API\CoreBundle\Tests\Controller
 */
class TagControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/tags';

//    1. GET List
    public function testListSuccess()
    {
        parent::testListSuccess();
    }

    public function testListErrors()
    {
        parent::testListErrors();
    }

//   2. GET Single
    public function testGetSingleSuccess()
    {

    }

    public function testGetSingleErrors()
    {

    }

//    3 POST Single
    public function testPostSingleSuccess()
    {

    }

    public function testPostSingleErrors()
    {

    }

//    4 UPDATE Single
    public function testUpdateSingleSuccess()
    {

    }

    public function testUpdateSingleErrors()
    {

    }

//    5 DELETE Single
    public function testDeleteSingleSuccess()
    {

    }

    public function testDeleteSingleErrors()
    {

    }

    public function getBaseUrl()
    {
        return self::BASE_URL;
    }
}
