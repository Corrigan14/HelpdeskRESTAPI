<?php

namespace API\TaskBundle\Tests\Controller\Task;

use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\Task;
use Igsem\APIBundle\Services\StatusCodesHelper;

/**
 * Class CommentAttachmentControllerTest
 *
 * @package API\TaskBundle\Tests\Controller\Task
 */
class CommentAttachmentControllerTest extends TaskTestCase
{
    /**
     * LIST OF COMMENT ATTACHMENTS - success
     */
    public function testListOfCommentAttachmentsSuccess()
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
     * LIST OF COMMENT ATTACHMENTS - errors
     */
    public function testListOfCommentAttachmentsErrors()
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
     * ADD ATTACHMENT TO COMMENT - success
     */
    public function testAddAttachmentToCommentSuccess()
    {
        $slug = 'zsskcd-jpg-2016-12-17-15-36';
        $comment = $this->findOneCommentEntity();

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/' . $comment->getId() . '/attachment/' . $slug,
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ADD ATTACHMENT TO COMMENT - errors
     */
    public function testAddAttachmentToCommentErrors()
    {
        $slug = 'zsskcd-jpg-2016-12-17-15-36';
        $comment = $this->findOneCommentEntity();

        // Try to update Entity without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/' . $comment->getId() . '/attachment/' . $slug,
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Attachment to not existed Comment
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/1254' . $comment->getId() . '/attachment/' . $slug,
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add not existed Attachment
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/' . $comment->getId() . '/attachment/test',
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::BAD_REQUEST_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Attachment with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/' . $comment->getId() . '/attachment/' . $slug,
            [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE ATTACHMENT FROM COMMENT - success
     */
    public function testRemoveAttachmentFromCommentSuccess()
    {
        $data = $this->findOrCreateTaskHasAttachmentEntity();

        $slug = $data['slug'];
        $task = $data['task'];

        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $task->getId() . '/attachment/' . $slug, [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::DELETED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE ATTACHMENT FROM COMMENT - errors
     */
    public function testRemoveAttachmentFromCommentErrors()
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

    /**
     * @return Comment
     */
    private function findOneCommentEntity(): Comment
    {
        $comment = $this->em->getRepository('APITaskBundle:Comment')->findOneBy([
            'title' => 'Koment - public'
        ]);

        $adminsTask = $this->findOneAdminEntity();

        $adminUser = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        if (!$comment instanceof Comment) {
            $comment = new Comment();
            $comment->setTitle('Koment - public');
            $comment->setBody('Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien.');
            $comment->setEmail(false);
            $comment->setInternal(false);
            $comment->setTask($adminsTask);
            $comment->setCreatedBy($adminUser);
            $this->em->persist($comment);
            $this->em->flush();
        }
        return $comment;
    }
}