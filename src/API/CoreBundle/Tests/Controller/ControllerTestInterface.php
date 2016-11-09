<?php
namespace API\CoreBundle\Tests\Controller;

interface ControllerTestInterface
{

//    1. GET List
    /**
     * GET LIST - success
     */
    public function testListSuccess();

    /**
     * GET LIST - errors
     */
    public function testListErrors();

//   2. GET Single
    /**
     * GET SINGLE - success
     */
    public function testGetSingleSuccess();

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors();

//    3 POST Single
    /**
     * POST SINGLE - success
     */
    public function testPostSingleSuccess();

    /**
     *  POST SINGLE - errors
     */
    public function testPostSingleErrors();

//    4 UPDATE (PUT, PATCH) Single
    /**
     * UPDATE SINGLE - success
     */
    public function testUpdateSingleSuccess();

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors();

//    5 DELETE Single
    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess();

    /**
     * DELETE SINGLE - errors
     */
    public function testDeleteSingleErrors();

    // OTHER
    /**
     * Return Client
     */
    public function getClient();

    /**
     * Return Base URL
     */
    public function getBaseUrl();
}