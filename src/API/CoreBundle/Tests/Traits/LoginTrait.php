<?php

namespace API\CoreBundle\Tests\Traits;

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
     * @return bool
     */
    public function loginUser($username, $password)
    {

        $crawler = $client->request('POST', '/token-authentication', ['username' => $username, 'password' => $password]);
        $content = json_decode($client->getResponse()->getContent(), true);

        if (array_key_exists('token', $content)) {
            return $content['token'];
        }

        return false;
    }



}