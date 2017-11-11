<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\Notification;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NotificationFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class NotificationFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

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
        $adminProject = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        $task = $manager->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 1'
        ]);

        $userAdmin = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $userUser = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $notification = new Notification();
        $notification->setTitle('User assigned you a Project');
        $notification->setChecked(false);
        $notification->setCreatedBy($userAdmin);
        $notification->setUser($userUser);
        $notification->setProject($adminProject);
        $manager->persist($notification);

        $notification = new Notification();
        $notification->setTitle('User posted (internal/email) comment to task');
        $notification->setBody('Comment body');
        $notification->setChecked(false);
        $notification->setCreatedBy($userAdmin);
        $notification->setUser($userUser);
        $notification->setTask($task);
        $manager->persist($notification);

        $notification = new Notification();
        $notification->setTitle('User updated task');
        $notification->setBody('following parameters were changed: title (FROM .. TO) + link na task');
        $notification->setChecked(false);
        $notification->setCreatedBy($userUser);
        $notification->setUser($userAdmin);
        $notification->setTask($task);
        $manager->persist($notification);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 20;
    }
}