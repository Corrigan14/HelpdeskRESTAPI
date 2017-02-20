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
class UserRoleControllerTest
{
    const BASE_URL = '/api/v1/task-bundle/user-roles';

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
            'title' => 'TEST USER ROLE'
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
        $userRole->setOrder(2);

        $this->em->persist($userRole);
        $this->em->flush();

        return $userRole;
    }

    /**
     * Should remove the entity which will be used in further Post or Update request
     */
    public function removeTestEntity()
    {
        $this->removeEntity('TEST UPDATE USER ROLE 1');
        $this->removeEntity('TEST UPDATE USER ROLE 2');
        $this->removeEntity('TEST UPDATE USER ROLE 3');
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
            'title' => 'TEST UPDATE USER ROLE 1',
            'homepage' => '/',
            'acl' => $acl,
            'order' => 5
        ];
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnBadOrderedPostTestData()
    {
        $acl = [
            UserRoleAclOptions::LOGIN_TO_SYSTEM,
            UserRoleAclOptions::SHARE_FILTERS,
            UserRoleAclOptions::PROJECT_SHARED_FILTERS,
            UserRoleAclOptions::REPORT_FILTERS
        ];

        return [
            'title' => 'TEST UPDATE USER ROLE 2',
            'homepage' => '/',
            'acl' => $acl,
            'order' => 1
        ];
    }

    /**
     * Return Post data
     *
     * @return array
     */
    public function returnBadAclPostTestData()
    {
        $acl = [
            UserRoleAclOptions::LOGIN_TO_SYSTEM,
            UserRoleAclOptions::SHARE_FILTERS,
            UserRoleAclOptions::PROJECT_SHARED_FILTERS,
            'test'
        ];

        return [
            'title' => 'TEST UPDATE USER ROLE 3',
            'homepage' => '/',
            'acl' => $acl,
            'order' => 5
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

    /**
     * @param string $title
     */
    private function removeEntity(string $title)
    {
        $userRole = $this->em->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => $title
        ]);

        if ($userRole instanceof UserRole) {
            $this->em->remove($userRole);
            $this->em->flush();
        }
    }
}
