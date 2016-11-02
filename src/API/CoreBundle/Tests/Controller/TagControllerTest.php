<?php

namespace API\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends WebTestCase
{
    public function testListtags()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/tags');
    }

}
