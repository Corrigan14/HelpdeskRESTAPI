<?php

namespace API\TaskBundle\Tests\Controller\Task;

use Igsem\APIBundle\Services\StatusCodesHelper;

/**
 * Class CommentControllerTest
 *
 * @package API\TaskBundle\Tests\Controller\Task
 */
class CommentControllerTest extends TaskTestCase
{
    /**
     * LIST OF TASK COMMENTS - success
     */
    public function testListOfTaskCommentsSuccess()
    {
        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 3 - admin is creator, admin is requested'
        ]);

        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/comments', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('data', $response));

        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/comments?internal=false', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('data', $response));
    }

    /**
     * LIST OF TASK COMMENTS - errors
     */
    public function testListOfTaskCommentsErrors()
    {
        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 3 - admin is creator, admin is requested'
        ]);

        // Try to call function without authorization header
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/comments',
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function to not existed Task
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/1254' . $task->getId() . '/comments', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/comments', [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * GET TASKS COMMENT - success
     */
    public function testGetTasksCommentSuccess()
    {
        $comment = $this->em->getRepository('APITaskBundle:Comment')->findOneBy([
            'title' => 'Koment - public'
        ]);

        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/comments/' . $comment->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('data', $response));
    }

    /**
     * GET TASKS COMMENT - errors
     */
    public function testGetTasksCommentErrors()
    {
        $comment = $this->em->getRepository('APITaskBundle:Comment')->findOneBy([
            'title' => 'Koment - public'
        ]);

        // Try to call function without authorization header
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/comments/' . $comment->getId(),
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function to not existed Comment
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/comments/12547' . $comment->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/comments/' . $comment->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * CREATE TASKS COMMENT - success
     */
    public function testCreateTasksCommentSuccess()
    {
        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 3 - admin is creator, admin is requested'
        ]);

        $data = $this->getCommentData();

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/comments', $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('data', $response));
    }

    /**
     * CREATE TASKS COMMENT - errors
     */
    public function testCreateTasksCommentErrors()
    {
        $task = $this->em->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 3 - admin is creator, admin is requested'
        ]);

        $data = $this->getCommentData();

        // Try to call function without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/comments',
            $data, [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function to not existed Task
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/12547' . $task->getId() . '/comments', $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/comments', $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function with invalid parameters
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/comments', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * CREATE COMMENTS COMMENT - success
     */
    public function testCreateCommentsCommentSuccess()
    {
        $comment = $this->em->getRepository('APITaskBundle:Comment')->findOneBy([
            'title' => 'Koment - public'
        ]);

        $data = $this->getCommentData();

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/' . $comment->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('data', $response));
    }

    /**
     * CREATE TASKS COMMENT - errors
     */
    public function testCreateCommentsCommentErrors()
    {
        $comment = $this->em->getRepository('APITaskBundle:Comment')->findOneBy([
            'title' => 'Koment - public'
        ]);

        $data = $this->getCommentData();

        // Try to call function without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/' . $comment->getId(),
            $data, [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function to not existed Task
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/1254' . $comment->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/' . $comment->getId(),
            $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function with invalid parameters
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/comments/' . $comment->getId(),
            [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @return array
     */
    private function getCommentData()
    {
        return [
            'title' => 'Test comment',
            'body' => 'Lorem Ipsum er rett og slett dummytekst fra og for trykkeindustrien.',
            'email' => false,
            'internal' => false
        ];
    }
}
