<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Tag;
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
            $task->setTitle('Task 1');
            $task->setDescription('Description of Task 1');
            $task->setImportant(false);
            $task->setCreatedBy($userUser);
            $task->setRequestedBy($userUser);
            if ($userProject instanceof Project) {
                $task->setProject($userProject);
            }
            if ($tagHome instanceof Tag) {
                $task->addTag($tagHome);
            }
            if ($tagWork instanceof Tag) {
                $task->addTag($tagWork);
            }
            $task->addFollower($adminUser);
            $manager->persist($task);

            $task = new Task();
            $task->setTitle('Task 2');
            $task->setDescription('Description of Task 2');
            $task->setImportant(false);
            $task->setCreatedBy($userUser);
            $task->setRequestedBy($adminUser);
            if ($userProject instanceof Project) {
                $task->setProject($userProject);
            }
            if ($tagHome instanceof Tag) {
                $task->addTag($tagHome);
            }
            if ($tagFreeTime instanceof Tag) {
                $task->addTag($tagFreeTime);
            }
            $manager->persist($task);

            for ($numberOfTasks = 4; $numberOfTasks < 1000; $numberOfTasks++) {
                $task = new Task();
                $task->setTitle('Task ' . $numberOfTasks);
                $task->setDescription('Description of Admins Task ' . $numberOfTasks);
                $task->setImportant(true);
                $task->setCreatedBy($adminUser);
                $task->setRequestedBy($adminUser);
                $manager->persist($task);
            }

            for ($numberOfTasks = 4; $numberOfTasks < 1000; $numberOfTasks++) {
                $task = new Task();
                $task->setTitle('Task ' . $numberOfTasks);
                $task->setDescription('Description of Users Task ' . $numberOfTasks);
                $task->setImportant(true);
                $task->setCreatedBy($userUser);
                $task->setRequestedBy($userUser);
                $manager->persist($task);
            }

            if ($userProject instanceof Project) {
                for ($numberOfTasks = 4; $numberOfTasks < 1000; $numberOfTasks++) {
                    $task = new Task();
                    $task->setTitle('Task ' . $numberOfTasks);
                    $task->setDescription('Description of Users Task ' . $numberOfTasks);
                    $task->setImportant(true);
                    $task->setCreatedBy($userUser);
                    $task->setRequestedBy($userUser);
                    $task->setProject($userProject);
                    $manager->persist($task);
                }
            }

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