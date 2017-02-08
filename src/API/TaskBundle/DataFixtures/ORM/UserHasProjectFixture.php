<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Security\VoteOptions;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserHasProjectFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class UserHasProjectFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userUser = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $userAdmin = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $usersProject = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        $usersProject2 = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 2'
        ]);

        $adminsProject = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        if ($userUser instanceof User && $adminsProject instanceof Project) {
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

            $userHasProject = new UserHasProject();
            $userHasProject->setUser($userUser);
            $userHasProject->setProject($adminsProject);
            $userHasProject->setAcl($acl);

            $manager->persist($userHasProject);
            $manager->flush();
        }

        if ($userAdmin instanceof User && $usersProject instanceof Project) {
            $acl = [];
            $acl[] = ProjectAclOptions::EDIT_PROJECT;
            $acl[] = ProjectAclOptions::CREATE_TASK;
            $acl[] = ProjectAclOptions::RESOLVE_TASK;
            $acl[] = ProjectAclOptions::DELETE_TASK;
            $acl[] = ProjectAclOptions::VIEW_INTERNAL_NOTE;
            $acl[] = ProjectAclOptions::VIEW_ALL_TASKS;
            $acl[] = ProjectAclOptions::VIEW_OWN_TASKS;
            $acl[] = ProjectAclOptions::VIEW_TASKS_FROM_USERS_COMPANY;

            $userHasProject = new UserHasProject();
            $userHasProject->setUser($userAdmin);
            $userHasProject->setProject($usersProject);
            $userHasProject->setAcl($acl);

            $manager->persist($userHasProject);
            $manager->flush();
        }

        if ($userAdmin instanceof User && $usersProject2 instanceof Project) {
            $acl = [];
            $acl[] = ProjectAclOptions::VIEW_ALL_TASKS;
            $userHasProject = new UserHasProject();
            $userHasProject->setUser($userAdmin);
            $userHasProject->setProject($usersProject2);
            $userHasProject->setAcl($acl);

            $manager->persist($userHasProject);
            $manager->flush();
        }

    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 7;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}