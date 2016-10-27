<?php

namespace API\CoreBundle\Tests\Traits;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Trait LoginTrait
 *
 * @package API\CoreBundle\Tests\Traits
 */
trait LoginTrait
{


    /**
     * @param $username
     * @param $password
     * @param Client $client
     * @return bool
     */
    public function loginUserGetToken($username, $password, Client $client)
    {

        $crawler = $client->request('POST', 'api/v1/token-authentication', ['username' => $username, 'password' => $password]);
        $content = json_decode($client->getResponse()->getContent(), true);

        if ($client->getResponse()->getStatusCode() == 200 && array_key_exists('token', $content)) {
            return $content['token'];
        }

        return false;
    }


}