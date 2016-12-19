<?php

namespace API\TaskBundle\Tests\Controller\Task;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use Igsem\APIBundle\Services\StatusCodesHelper;
use API\TaskBundle\Security\StatusOptions;

/**
 * Class AssignControllerTest
 *
 * @package API\TaskBundle\Tests\Controller\Task
 */
class AssignControllerTest extends TaskTestCase
{
    /**
     * ASSIGN USER TO TASK - success
     */
    public function testCreateAssignUserToTaskSuccess()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var User $user */
        $user = $task->getCreatedBy();

        // Remove assigned admin user
        $this->removeTaskHasAssignedUser($task, $user);

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ASSIGN USER TO TASK - errors
     */
    public function testCreateAssignUserToTaskErrors()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var User $user */
        $user = $task->getCreatedBy();

        // Remove assigned admin user
        $this->removeTaskHasAssignedUser($task, $user);

        // Try to create Entity without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to assign not Existed User to not Existed Task
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/1254' . $task->getId() . '/assign-user/2598' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to assign User to task with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to assign User to task with wrong data: status date could be Datetime Type
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            ['status_date' => '12.5.2016'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to assign User to task two times
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::BAD_REQUEST_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ASSIGN USER TO TASK - success
     */
    public function testUpdateAssignUserToTaskSuccess()
    {
        // Create or find User assigned to task
        $data = $this->findOrCreateTaskHasAssignedEntity();

        /** @var Task $task */
        $task = $data['task'];
        /** @var User $user */
        $user = $data['user'];

        $status = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => StatusOptions::IN_PROGRESS]);

        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId() . '/status/' . $status->getId(),
            ['time_spent' => 100], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ASSIGN USER TO TASK - errors
     */
    public function testUpdateAssignUserToTaskErrors()
    {
        // Create or find User assigned to task
        $data = $this->findOrCreateTaskHasAssignedEntity();

        /** @var Task $task */
        $task = $data['task'];
        /** @var User $user */
        $user = $data['user'];

        $status = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => StatusOptions::IN_PROGRESS]);

        // Try to update Entity without authorization header
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId() . '/status/' . $status->getId(),
            ['time_spent' => 100], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Entity with not existed Status
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId() . '/status/125874',
            ['time_spent' => 100], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Entity with not existed Task
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/125789' . '/assign-user/' . $user->getId() . '/status/' . $status->getId(),
            ['time_spent' => 100], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId() . '/status/' . $status->getId(),
            ['time_spent' => 100], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Entity with wrong data: status date could be Datetime Type
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId() . '/status/' . $status->getId(),
            ['time_spent' => 100, 'status_date' => '12.5.1254'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE ASSIGN USER FROM TASK - success
     */
    public function testRemoveAssignUserFromTaskSuccess()
    {
        $data = $this->findOrCreateTaskHasAssignedEntity();

        /** @var Task $task */
        $task = $data['task'];
        /** @var User $user */
        $user = $data['user'];

        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::DELETED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE ASSIGN USER FROM TASK - errors
     */
    public function testRemoveAssignUserFromTaskErrors()
    {
        $data = $this->findOrCreateTaskHasAssignedEntity();

        /** @var Task $task */
        $task = $data['task'];
        /** @var User $user */
        $user = $data['user'];

        // Try to delete Entity without authorization header
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to delete Entity with not existed Task
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/1257' . $task->getId() . '/assign-user/12547' . $user->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @param Task $task
     * @param User $user
     */
    private function removeTaskHasAssignedUser(Task $task, User $user)
    {
        $taskHasAssignedUser = $this->em->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy([
            'user' => $user,
            'task' => $task
        ]);

        if ($taskHasAssignedUser instanceof TaskHasAssignedUser) {
            $task->removeTaskHasAssignedUser($taskHasAssignedUser);
            $user->removeTaskHasAssignedUser($taskHasAssignedUser);
            $this->em->remove($taskHasAssignedUser);
            $this->em->persist($task);
            $this->em->persist($user);
            $this->em->flush();
        }
    }

    /**
     * @return array
     */
    private function findOrCreateTaskHasAssignedEntity(): array
    {
        // Create or find User assigned to task
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var User $user */
        $user = $task->getCreatedBy();

        /** @var TaskHasAssignedUser $task */
        $taskHasAssignedUser = $this->em->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy([
            'task' => $task,
            'user' => $user,
        ]);

        if ($taskHasAssignedUser instanceof TaskHasAssignedUser) {
            $task = $taskHasAssignedUser->getTask();
            $user = $taskHasAssignedUser->getUser();
        } else {
            $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/assign-user/' . $user->getId(),
                [], [],
                ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
            $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());
        }

        return [
            'task' => $task,
            'user' => $user
        ];
    }
}