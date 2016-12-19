<?php

namespace API\TaskBundle\Tests\Controller\Task;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Task;
use Igsem\APIBundle\Services\StatusCodesHelper;

/**
 * Class FollowerControllerTest
 *
 * @package API\TaskBundle\Tests\Controller\Task
 */
class FollowerControllerTest extends TaskTestCase
{
    /**
     * ADD FOLLOWER TO TASK - success
     */
    public function testAddFollowerToTaskSuccess()
    {
        $task = $this->findOneAdminEntity();
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/follower/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // At least one follower could be returned
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertNotNull(count($response));
    }

    /**
     * ADD FOLLOWER TO TASK - errors
     */
    public function testAddFollowerToTaskErrors()
    {
        $task = $this->findOneAdminEntity();
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);

        // Try to add Follower without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/follower/' . $userUser->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Follower to not existed Task
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/1245680' . '/follower/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to delete Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/follower/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE FOLLOWER FROM TASK - success
     */
    public function testRemoveFollowerFromTaskSuccess()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        // Add follower to task
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);
        $this->addFollowerToTask($task, $userUser);

        // Remove follower
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/follower/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE FOLLOWER FROM TASK - errors
     */
    public function testRemoveFollowerFromTaskErrors()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        // Add follower to task
        $userUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'testuser2'
        ]);
        $this->addFollowerToTask($task, $userUser);

        // Try to remove Follower without authorization header
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/follower/' . $userUser->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to remove Follower from not existed Task
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/125874' . '/follower/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to remove Follower with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/follower/' . $userUser->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @param Task $task
     * @param User $follower
     * @return bool
     */
    private function addFollowerToTask(Task $task, User $follower)
    {
        $followers = $task->getFollowers();
        $followers->toArray();

        foreach ($followers as $foll) {
            if ($foll === $follower) {
                return true;
            }
        }
        $task->addFollower($follower);
        $this->em->persist($task);
        $this->em->flush();

        return true;
    }

}