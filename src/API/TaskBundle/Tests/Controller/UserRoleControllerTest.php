<?php

namespace API\TaskBundle\Tests\Controller;

use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Tests\Controller\ApiTestCase;

/**
 * Class UserRoleControllerTest
 *
 * @package API\TaskBundle\Tests\Controller
 */
class UserRoleControllerTest extends ApiTestCase
{
    const BASE_URL = '/api/v1/task-bundle/user-roles';

    /**
     * GET LIST - errors
     */
    public function testListErrors()
    {
        parent::testListErrors();

        // Try to load entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * GET SINGLE - errors
     */
    public function testGetSingleErrors()
    {
        parent::testGetSingleErrors();

        $entity = $this->findOneEntity();

        // Try to load Entity with ROLE_USER which hasn't permission to this action
        $this->getClient(true)->request('GET', $this->getBaseUrl().'/'.$entity->getId(), [], [],
            ['Authorization' => 'Bearer ' . $this->userToken, 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken]);
        $this->assertEquals(StatusCodesHelper::ACCESS_DENIED_CODE, $this->getClient()->getResponse()->getStatusCode());
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
        $userRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'ADMIN'
        ]);

        if ($userRole instanceof UserRole) {
            return $userRole;
        }

        return $this->createEntity();
    }

    /**
     * Create and return a single entity from db for testing CRUD
     *
     * @return mixed
     */
    public function createEntity()
    {
        $adminAcl = [
            UserRoleAclOptions::LOGIN_TO_SYSTEM,
            UserRoleAclOptions::SHARE_FILTERS,
            UserRoleAclOptions::PROJECT_SHARED_FILTERS,
            UserRoleAclOptions::REPORT_FILTERS,
            UserRoleAclOptions::SHARE_TAGS,
            UserRoleAclOptions::CREATE_PROJECTS,
            UserRoleAclOptions::SENT_EMAILS_FROM_COMMENTS,
            UserRoleAclOptions::CREATE_TASKS,
            UserRoleAclOptions::CREATE_TASKS_IN_ALL_PROJECTS,
            UserRoleAclOptions::UPDATE_ALL_TASKS,
            UserRoleAclOptions::USER_SETTINGS,
            UserRoleAclOptions::USER_ROLE_SETTINGS,
            UserRoleAclOptions::COMPANY_ATTRIBUTE_SETTINGS,
            UserRoleAclOptions::COMPANY_SETTINGS,
            UserRoleAclOptions::STATUS_SETTINGS,
            UserRoleAclOptions::TASK_ATTRIBUTE_SETTINGS,
            UserRoleAclOptions::UNIT_SETTINGS,
            UserRoleAclOptions::SYSTEM_SETTINGS,
            UserRoleAclOptions::SMTP_SETTINGS,
            UserRoleAclOptions::IMAP_SETTINGS
        ];
        $userRole = new UserRole();
        $userRole->setTitle('TEST USER ROLE');
        $userRole->setDescription('Test user role, has admins acl');
        $userRole->setIsActive(true);
        $userRole->setHomepage(self::BASE_URL);
        $userRole->setAcl($adminAcl);

        $this->em->persist($userRole);
        $this->em->flush();
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $userRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'TEST USER ROLE'
        ]);

        if ($userRole instanceof UserRole) {
            $this->em->remove($userRole);
            $this->em->flush();
        }
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnPostTestData()
    {
        $acl = [
            UserRoleAclOptions::LOGIN_TO_SYSTEM,
            UserRoleAclOptions::SHARE_FILTERS,
            UserRoleAclOptions::PROJECT_SHARED_FILTERS,
            UserRoleAclOptions::REPORT_FILTERS
        ];

        return [
            'title' => 'TEST UPDATE USER ROLE',
            'homepage' => '/',
            'acl' => $acl
        ];
    }

    /**
     * Return Update data
     *
     * @return array
     */
    public function returnUpdateTestData()
    {
        $acl = [
            UserRoleAclOptions::LOGIN_TO_SYSTEM,
            UserRoleAclOptions::SHARE_FILTERS,
            UserRoleAclOptions::PROJECT_SHARED_FILTERS,
            UserRoleAclOptions::REPORT_FILTERS
        ];

        return [
            'title' => 'TEST UPDATE USER ROLE',
            'homepage' => '/',
            'acl' => $acl
        ];
    }
}
