<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Task;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaskFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class TaskFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $userUser = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $adminUser = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $userProject = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        $tagFreeTime = $manager->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Free Time',
        ]);

        $tagWork = $manager->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Work',
        ]);

        $tagHome = $manager->getRepository('APITaskBundle:Tag')->findOneBy([
            'title' => 'Home',
        ]);

        if ($userUser instanceof User && $adminUser instanceof User) {
            $task = new Task();
            $task->setTitle('Task 1 - user is creator, user is requested');
            $task->setDescription('Description of Task 1');
            $task->setImportant(false);
            $task->setCreatedBy($userUser);
            $task->setRequestedBy($userUser);
            $task->setProject($userProject);
            $task->addTag($tagWork);
            $task->addTag($tagHome);
            $task->addTag($tagFreeTime);
            $task->addFollower($adminUser);
            $manager->persist($task);

            $task = new Task();
            $task->setTitle('Task 2 - user is creator, admin is requested');
            $task->setDescription('Description of Task 2');
            $task->setImportant(false);
            $task->setCreatedBy($userUser);
            $task->setRequestedBy($adminUser);
            $task->setProject($userProject);
            $task->addTag($tagHome);
            $task->addTag($tagFreeTime);
            $manager->persist($task);

            $task = new Task();
            $task->setTitle('Task 3 - admin is creator, admin is requested');
            $task->setDescription('Description of Task 3');
            $task->setImportant(true);
            $task->setCreatedBy($adminUser);
            $task->setRequestedBy($adminUser);
            $manager->persist($task);

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
        return 9;
    }
}