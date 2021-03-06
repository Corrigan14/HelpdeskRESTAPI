<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\ProjectAclOptions;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

class ProjectControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/projects';
    const PROJECT_ACL_URL = '/api/v1/task-bundle';

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        parent::testGetSingleErrors();

        $entity = $this->findOneAdminEntity();

        // Try to load entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * POST SINGLE - success
     */
    public function testPostSingleErrors()
    {
        parent::testPostSingleErrors();

        $data = $this->returnPostTestData();

        // Try to create entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', $this->getBaseUrl(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create entity with invalid parameter Title (title is required)
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['description' => 'Description'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     *  UPDATE SINGLE - errors
     */
    public function testUpdateSingleErrors()
    {
        parent::testUpdateSingleErrors();

        $data = $this->returnUpdateTestData();

        // We need to make sure that the post data doesn't exist in the DB, we expect the remove entity to delete the
        // entity corresponding to the post data
        $this->removeTestEntity();

        $adminEntity = $this->findOneAdminEntity();

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PUT
        $this->getClient(true)->request('PUT', $this->getBaseUrl() . '/' . $adminEntity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to update entity with ROLE_USER which hasn't permission to this action: method PATCH
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/' . $adminEntity->getId(), $data, [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - success
     */
    public function testDeleteSingleSuccess()
    {
        $entity = $this->findOneEntity();

        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * DELETE SINGLE - error
     */
    public function testDeleteSingleErrors()
    {
        parent::testDeleteSingleErrors();

        $entity = $this->findOneAdminEntity();

        // Try to delete entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('DELETE', $this->getBaseUrl() . '/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * RESTORE SINGLE - success
     */
    public function testRestoreSingleSuccess()
    {
        $entity = $this->findOneInActiveEntity();

        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/restore/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::SUCCESSFUL_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * RESTORE SINGLE - error
     */
    public function testRestoreSingleErrors()
    {
        $entity = $this->findOneAdminEntity();

        // Try to restore entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/restore/' . $entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to restore  Entity without authorization header
        $this->getClient()->request('PATCH', $this->getBaseUrl() . '/restore/' . $entity->getId(), [], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to restore  Entity with not existed ID (as Admin)
        $this->getClient(true)->request('PATCH', $this->getBaseUrl() . '/restore/1125874', [], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * test for CREATE UserHasProject Entity - success
     */
    public function testAddUserToProjectSuccess()
    {
        $adminProject = $this->findOneAdminEntity();
        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'manager'
        ]);
        $acl = [];
        $acl[] = ProjectAclOptions::CREATE_TASK;

        $this->getClient(true)->request('POST', self::PROJECT_ACL_URL . '/project/' . $adminProject->getId() . '/user/' . $user->getId(),
            ['acl' => $acl], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * test for CREATE UserHasProject Entity - error
     */
    public function testAddUserToProjectError()
    {
        $adminProject = $this->findOneAdminEntity();
        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);
        $acl = [];
        $acl[] = ProjectAclOptions::CREATE_TASK;

        $errorAcl = [];
        $errorAcl[] = 'test';

        // Try to create Entity without authorization header
        $this->getClient(true)->request('POST', self::PROJECT_ACL_URL . '/project/' . $adminProject->getId() . '/user/' . $user->getId(),
            ['acl' => $acl], [], []);
        $this->assertEquals(StatusCodesHelper::UNAUTHORIZED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('POST', self::PROJECT_ACL_URL . '/project/' . $adminProject->getId() . '/user/' . $user->getId(),
            ['acl' => $acl], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Entity with invalid project Id - project doesn't exist
        $this->getClient(true)->request('POST', self::PROJECT_ACL_URL . '/project/' . 2357841 . '/user/' . $user->getId(),
            ['acl' => $acl], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::NOT_FOUND_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Try to create Entity with invalid ACL data - acl has to be allowed in VoteOptions
        $this->getClient(true)->request('POST', self::PROJECT_ACL_URL . '/project/' . $adminProject->getId() . '/user/' . $user->getId(),
            ['acl' => $errorAcl], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::INVALID_PARAMETERS_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * test for DELETE UserHasProject Entity - success
     */
    public function testRemoveUserFromProjectSuccess()
    {
        $userHasProjectEntity = $this->findUserHasProjectEntity();
        $user = $userHasProjectEntity->getUser();
        $project = $userHasProjectEntity->getProject();

        $this->getClient(true)->request('DELETE', self::PROJECT_ACL_URL . '/project/' . $project->getId() . '/user/' . $user->getId(),
            [], [], ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::DELETED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * Get the url for requests
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return self::BASE_URL;
    }

    /**
     * Return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function findOneEntity()
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'project NEW'
        ]);

        if ($project instanceof Project) {
            return $project;
        }

        $projectArray = $this->createEntity();

        return $this->em->getRepository('APITaskBundle:Project')->find($projectArray['id']);
    }

    /**
     * Return a single ADMIN project entity from db
     *
     * @return Project|bool
     */
    public function findOneAdminEntity()
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        if ($project instanceof Project) {
            return $project;
        }

        return false;
    }

    /**
     * Return a single ADMIN project entity with inactive status from db
     *
     * @return Project|bool
     */
    public function findOneInActiveEntity()
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin 3 - inactive'
        ]);

        if ($project instanceof Project) {
            return $project;
        }

        return $this->createInactiveEntity();
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['title' => 'project NEW'], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Check if Entity was created
        $createdProject = json_decode($this->getClient()->getResponse()->getContent(), true);
        $createdProject = $createdProject['data'];
        $this->assertTrue(array_key_exists('id', $createdProject));

        return $createdProject;
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createInactiveEntity()
    {
        $this->getClient(true)->request('POST', $this->getBaseUrl(), ['title' => 'Project of admin 3 - inactive', 'is_active' => false], [],
            ['Authorization' => 'Bearer ' . $this->adminToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken]);
        $this->assertEquals(StatusCodesHelper::CREATED_CODE, $this->getClient()->getResponse()->getStatusCode());

        // Check if Entity was created
        $createdProject = json_decode($this->getClient()->getResponse()->getContent(), true);
        $createdProject = $createdProject['data'];
        $this->assertTrue(array_key_exists('id', $createdProject));

        return $createdProject;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeProject('project CREATE');
        $this->removeProject('project UPDATE');
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        return [
            'title' => 'project CREATE',
            'description' => 'short description',
        ];
    }

    /**
     * Return Update data
     *
     * @return array
     */
    public function returnUpdateTestData()
    {
        return [
            'title' => 'project UPDATE',
            'description' => 'short description',
        ];
    }

    /**
     * @return UserHasProject
     */
    public function findUserHasProjectEntity()
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);
        $user = $this->em->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $userHasProject = $this->em->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $user,
            'project' => $project,
        ]);

        if ($userHasProject instanceof UserHasProject) {
            return $userHasProject;
        }

        $userHasProject = new UserHasProject();
        $userHasProject->setProject($project);
        $userHasProject->setUser($user);

        $acl = [];
        $acl[] = ProjectAclOptions::EDIT_PROJECT;
        $acl[] = ProjectAclOptions::EDIT_INTERNAL_NOTE;
        $acl[] = ProjectAclOptions::CREATE_TASK;
        $acl[] = ProjectAclOptions::RESOLVE_TASK;
        $acl[] = ProjectAclOptions::DELETE_TASK;
        $acl[] = ProjectAclOptions::VIEW_INTERNAL_NOTE;
        $acl[] = ProjectAclOptions::VIEW_ALL_TASKS;
        $acl[] = ProjectAclOptions::VIEW_OWN_TASKS;
        $acl[] = ProjectAclOptions::VIEW_TASKS_FROM_USERS_COMPANY;

        $userHasProject->setAcl($acl);

        $this->em->persist($userHasProject);
        $this->em->flush();

        return $userHasProject;
    }

    /**
     * @param $title
     */
    private function removeProject($title)
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => $title
        ]);

        if ($project instanceof Project) {
            $this->em->remove($project);
            $this->em->flush();
        }

        // Check if entity was removed
        $project = $this->em->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => $title
        ]);

        $this->assertEquals(null, $project);
    }
}
