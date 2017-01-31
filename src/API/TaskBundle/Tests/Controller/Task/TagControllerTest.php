<?php

namespace API\TaskBundle\Tests\Controller\Task;

use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use Igsem\APIBundle\Services\StatusCodesHelper;

/**
 * Class TagControllerTest
 *
 * @package API\TaskBundle\Tests\Controller\Task
 */
class TagControllerTest extends TaskTestCase
{
    /**
     * LIST OF TASKS TAGS - success
     */
    public function testListOfTasksTagsSuccess()
    {
        $task = $this->findOneAdminEntity();

        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/tag', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());

        // We expect at least one Entity, response has to include array with data and _links param
        $response = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('_links', $response));
        $this->assertTrue(array_key_exists('data', $response));
    }

    /**
     * LIST OF TASKS TAGS - errors
     */
    public function testListOfTasksTagsErrors()
    {
        $task = $this->findOneAdminEntity();

        // Try to call function without authorization header
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/tag',
            [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function to not existed Task
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/1254' . $task->getId() . '/tag', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to call function with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $task->getId() . '/tag', [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ADD TAG TO TASK - success
     */
    public function testAddTagToTaskSuccess()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var Tag $tag */
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Another Admin Public Tag'
        ]);

        //Remove tag from task if it was added before
        if ($tag instanceof Tag) {
            $this->removeTagFromTask($task, $tag);
        }

        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * ADD TAG TO TASK - errors
     */
    public function testAddTagToTaskErrors()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var Tag $tag */
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Another Admin Public Tag'
        ]);

        //Remove tag from task if it was added before
        if ($tag instanceof Tag) {
            $this->removeTagFromTask($task, $tag);
        }

        // Try to add Tag without authorization header
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Tag to not existed Task
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/125874' . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Tag with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }


    /**
     * REMOVE TAG FROM TASK - success
     */
    public function testRemoveTagFromTaskSuccess()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var Tag $tag */
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Another Admin Public Tag'
        ]);
        if ($tag instanceof Tag) {
            $this->addTagToTask($task, $tag);
        }

        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * REMOVE TAG FROM TASK - errors
     */
    public function testRemoveTagFromTaskErrors()
    {
        /** @var Task $task */
        $task = $this->findOneAdminEntity();

        /** @var Tag $tag */
        $tag = $this->em->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Another Admin Public Tag'
        ]);

        if ($tag instanceof Tag) {
            $this->addTagToTask($task, $tag);
        }

        // Try to remove Tag without authorization header
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId());
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to remove Tag to not existed Task
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/125874' . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to add Tag with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $task->getId() . '/tag/' . $tag->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @param Task $task
     * @param Tag $tag
     * @return bool
     */
    private function addTagToTask(Task $task, Tag $tag): bool
    {
        $tags = $task->getTags();

        if (in_array($tag, $tags->toArray())) {
            return true;
        }

        $task->addTag($tag);
        $this->em->persist($task);
        $this->em->flush();

        return true;
    }

    /**
     * @param Task $task
     * @param Tag $tag
     * @return bool
     */
    private function removeTagFromTask(Task $task, Tag $tag): bool
    {
        $tags = $task->getTags();

        if (!in_array($tag, $tags->toArray())) {
            return true;
        }

        $task->removeTag($tag);
        $this->em->persist($task);
        $this->em->flush();

        return true;
    }
}