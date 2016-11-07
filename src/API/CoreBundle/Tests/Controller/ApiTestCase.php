<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/7/16
 * Time: 11:34 AM
 */

namespace API\CoreBundle\Tests\Controller;

use API\CoreBundle\Tests\Traits\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    use LoginTrait;

    protected $adminToken;
    protected $userToken;


    public function __construct()
    {
        $this->adminToken = $this->loginUserGetToken('admin', 'admin', static::createClient());
        $this->userToken = $this->loginUserGetToken('user', 'user', static::createClient());
        /**
         * token is generated?
         */
        $this->assertNotEquals(false, $this->adminToken);
        $this->assertNotEquals(false, $this->userToken);
    }
}