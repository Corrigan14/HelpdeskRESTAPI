<?php
namespace API\CoreBundle\Tests\Controller;

interface ControllerTestInterface
{
//    1. GET List
    public function testListSuccess();
    public function testListErrors();

//   2. GET Single
    public function testGetSingleSuccess();
    public function testGetSingleErrors();

//    3 POST Single
    public function testPostSingleSuccess();
    public function testPostSingleErrors();

//    4 UPDATE Single
    public function testUpdateSingleSuccess();
    public function testUpdateSingleErrors();

//    5 DELETE Single
    public function testDeleteSingleSuccess();
    public function testDeleteSingleErrors();

    public function getClient();
    public function getBaseUrl();
}