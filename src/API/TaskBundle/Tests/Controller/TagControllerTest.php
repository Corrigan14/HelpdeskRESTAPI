<?php

namespace API\TaskBundle\Tests\Controller;

use API\CoreBundle\Tests\Controller\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/tags';

    /**
     * GET LIST - success
     */
    public function testListSuccess()
    {
        parent::testListSuccess();
    }

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
        parent::testListErrors();
    }

    /**
     * GET SINGLE - success
     */
    public function testGetSingleSuccess()
    {
        // TODO: Implement testGetSingleSuccess() method.
    }

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        // TODO: Implement testGetSingleErrors() method.
    }

    /**
     * POST SINGLE - success
     */
    public function testPostSingleSuccess()
    {
        // TODO: Implement testPostSingleSuccess() method.
    }

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors()
    {
        // TODO: Implement testPostSingleErrors() method.
    }

    /**
     * UPDATE SINGLE - success
     */
    public function testUpdateSingleSuccess()
    {
        // TODO: Implement testUpdateSingleSuccess() method.
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        // TODO: Implement testUpdateSingleErrors() method.
    }

    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess()
    {
        // TODO: Implement testDeleteSingleSuccess() method.
    }

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors()
    {
        // TODO: Implement testDeleteSingleErrors() method.
    }

    /**
     * Return Base URL
     */
    public function getBaseUrl()
    {
        return self::BASE_URL;
    }
}
