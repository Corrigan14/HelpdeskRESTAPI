<?php

namespace API\TaskBundle\DataFixtures\ORMDEVELOPMENT;

use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Security\UserRoleAclOptions;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserRoleFixture
 *
 * @package API\TaskBundle\DataFixtures\ORMDEVELOPMENT
 */
class UserRoleFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    const BASE_URL = '/';

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
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
        $userRole->setTitle('ADMIN');
        $userRole->setDescription('Admin is a main system role. All ACL are available.');
        $userRole->setIsActive(true);
        $userRole->setHomepage(self::BASE_URL);
        $userRole->setAcl($adminAcl);
        $userRole->setOrder(1);

        $manager->persist($userRole);

        $managerAcl = [
            UserRoleAclOptions::LOGIN_TO_SYSTEM,
            UserRoleAclOptions::CREATE_TASKS,
            UserRoleAclOptions::CREATE_PROJECTS,
            UserRoleAclOptions::COMPANY_SETTINGS,
            UserRoleAclOptions::REPORT_FILTERS,
            UserRoleAclOptions::SENT_EMAILS_FROM_COMMENTS,
            UserRoleAclOptions::UPDATE_ALL_TASKS
        ];
        $userRole = new UserRole();
        $userRole->setTitle('MANAGER');
        $userRole->setIsActive(true);
        $userRole->setHomepage(self::BASE_URL);
        $userRole->setAcl($managerAcl);
        $userRole->setOrder(2);

        $manager->persist($userRole);

        $agentAcl = [
            UserRoleAclOptions::LOGIN_TO_SYSTEM,
            UserRoleAclOptions::CREATE_TASKS,
            UserRoleAclOptions::CREATE_PROJECTS,
            UserRoleAclOptions::COMPANY_SETTINGS,
            UserRoleAclOptions::SENT_EMAILS_FROM_COMMENTS,
        ];
        $userRole = new UserRole();
        $userRole->setTitle('AGENT');
        $userRole->setIsActive(true);
        $userRole->setHomepage(self::BASE_URL);
        $userRole->setAcl($agentAcl);
        $userRole->setOrder(3);

        $manager->persist($userRole);

        $customerAcl = [
            UserRoleAclOptions::LOGIN_TO_SYSTEM,
            UserRoleAclOptions::CREATE_TASKS
        ];
        $userRole = new UserRole();
        $userRole->setTitle('CUSTOMER');
        $userRole->setIsActive(true);
        $userRole->setHomepage(self::BASE_URL);
        $userRole->setAcl($customerAcl);
        $userRole->setOrder(4);

        $manager->persist($userRole);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}