<?php

namespace API\TaskBundle\Tests\Controller\Task;

use API\TaskBundle\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Igsem\APIBundle\Tests\Traits\LoginTrait;

/**
 * Class TaskTestCase
 *
 * @package API\TaskBundle\Tests\Controller\Task
 */
class TaskTestCase extends WebTestCase
{
    use LoginTrait;

    const BASE_URL = '/api/v1/task-bundle/tasks';

    /** @var bool|string */
    protected $adminToken;

    /** @var bool|string */
    protected $userToken;

    /** @var bool */
    protected $client = false;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /**
     * ApiTestCase constructor.
     */
    public function __construct()
    {
        parent::__construct();
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->adminToken = $this->loginUserGetToken('admin', 'admin', static::createClient());
        $this->userToken = $this->loginUserGetToken('testuser2', 'testuser', static::createClient());

        // Token is generated?
        $this->assertNotEquals(false, $this->adminToken);
        $this->assertNotEquals(false, $this->userToken);
    }

    /**
     * Get The Symfony Client, if Client already exists, return the exiting one
     *
     * @param bool $new
     *
     * @return bool|Client
     */
    protected function getClient($new = false)
    {
        if ($this->client && false === $new) {
            return $this->client;
        }

        $this->client = static::createClient();

        return $this->client;
    }

    /**
     * Get the url for requests
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        return self::BASE_URL;
    }

    /**
     * Return a single entity from db for testing CRUD
     *
     * @return Task
     */
    protected function findOneAdminEntity()
    {
        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task TEST - admin is creator, admin is requested 2'
        ]);

        if ($task instanceof Task) {
            return $task;
        }

        return $this->createAdminsEntity();
    }

    /**
     * Create and return a single entity from db for testing CRUD: Admin's task in Admin's project
     *
     * @return mixed
     */
    protected function createAdminsEntity()
    {
        $adminUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $task = new Task();
        $task->setTitle('Task TEST - admin is creator, admin is requested 2');
        $task->setDescription('Description of Task TEST');
        $task->setImportant(false);
        $task->setCreatedBy($adminUser);
        $task->setRequestedBy($adminUser);

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

}