<?php

namespace API\TaskBundle\Tests\Controller\Task;

use API\TaskBundle\Entity\Task;
use Igsem\APIBundle\Services\StatusCodesHelper;

/**
 * Class AttachmentControllerTest
 *
 * @package API\TaskBundle\Tests\Controller\Task
 */
class AttachmentControllerTest extends TaskTestCase
{
    /**
     * LIST OF TASKS ATTACHMENTS - success
     */
    public function testListOfTasksAttachmentsSuccess()
    {
        $task = $this->findOneAdminEntity();

        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/attachment', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('data', $response));
    }

    /**
     * LIST OF TASKS ATTACHMENTS - errors
     */
    public function testListOfTasksAttachmentsErrors()
    {
        $task = $this->findOneAdminEntity();

        // Try to call function without authorization header
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/attachment',
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function to not existed Task
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/1254' . $task->getId() . '/attachment', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/attachment', [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ADD ATTACHMENT TO TASK - success
     */
    public function testAddAttachmentToTaskSuccess()
    {
        $slug = 'zsskcd-jpg-2016-12-17-15-36';
        $task = $this->findOneAdminEntity();

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/' . $slug, [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ADD ATTACHMENT TO TASK - errors
     */
    public function testAddAttachmentToTaskErrors()
    {
        $slug = 'zsskcd-jpg-2016-12-17-15-36';
        $task = $this->findOneAdminEntity();

        // Try to update Entity without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/' . $slug,
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Attachment to not existed Task
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/125468' . $task->getId() . '/attachment/' . $slug, [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add not existed Attachment
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/slug-fff', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::BAD_REQUEST_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Attachment with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/' . $slug, [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE ATTACHMENT FROM TASK - success
     */
    public function testRemoveAttachmentFromTaskSuccess()
    {
        $data = $this->findOrCreateTaskHasAttachmentEntity();

        $slug = $data['slug'];
        $task = $data['task'];

        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/' . $slug, [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::DELETED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE ATTACHMENT FROM TASK - errors
     */
    public function testRemoveAttachmentFromTaskErrors()
    {
        $data = $this->findOrCreateTaskHasAttachmentEntity();

        $slug = $data['slug'];
        $task = $data['task'];

        // Try to remove Entity without authorization header
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/' . $slug,
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to remove Attachment from not existed Task
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/125468' . $task->getId() . '/attachment/' . $slug, [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to remove not added Attachment from Task
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/slug-fff', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::BAD_REQUEST_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to remove Attachment with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/' . $slug, [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @return array
     */
    private function findOrCreateTaskHasAttachmentEntity(): array
    {
        // Create or find User assigned to task
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var string $slug */
        $slug = 'zsskcd-jpg-2016-12-17-15-36';

        /** @var TaskHasAssignedUser $task */
        $taskHasAttachment = $this->em->getRepository('APITaskBundle:TaskHasAttachment')->findOneBy([
            'task' => $task,
            'slug' => $slug,
        ]);

        if (!$taskHasAttachment instanceof TaskHasAttachment) {
            $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/' . $slug, [], [],
                ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
            $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());
        }

        return [
            'task' => $task,
            'slug' => $slug
        ];
    }
}