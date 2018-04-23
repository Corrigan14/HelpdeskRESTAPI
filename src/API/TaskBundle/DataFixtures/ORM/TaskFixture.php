<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\StatusOptions;
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

        $usersProject2 = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 2'
        ]);

        $adminsProject = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
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

        $webSolCompany = $manager->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Web-Solutions'
        ]);

        $lanCompany = $manager->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'LanSystems'
        ]);

        $newStatus = $manager->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => StatusOptions::NEW
        ]);

        $inProgresStatus = $manager->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => StatusOptions::IN_PROGRESS
        ]);

        if ($userUser instanceof User && $adminUser instanceof User) {
            $task = new Task();
            $task->setTitle('Task 1');
            $task->setDescription('Description of Task 1');
            $task->setImportant(false);
            $task->setCreatedBy($userUser);
            $task->setRequestedBy($userUser);
            $task->setProject($userProject);
            if ($tagHome instanceof Tag) {
                $task->addTag($tagHome);
            }
            if ($tagWork instanceof Tag) {
                $task->addTag($tagWork);
            }
            $task->addFollower($adminUser);
            $task->setCompany($webSolCompany);
            $task->setStatus($newStatus);
            $manager->persist($task);

            $task = new Task();
            $task->setTitle('Task 2');
            $task->setDescription('Description of Task 2');
            $task->setImportant(false);
            $task->setCreatedBy($userUser);
            $task->setRequestedBy($adminUser);
            $task->setProject($userProject);
            if ($tagHome instanceof Tag) {
                $task->addTag($tagHome);
            }
            if ($tagFreeTime instanceof Tag) {
                $task->addTag($tagFreeTime);
            }
            $task->setCompany($webSolCompany);
            $task->setStatus($inProgresStatus);
            $task->setStartedAt(new \DateTime());
            $manager->persist($task);

            $task = new Task();
            $task->setTitle('Task 3 - admin is creator, admin is requested');
            $task->setDescription('Description of Task 3');
            $task->setImportant(false);
            $task->setCreatedBy($adminUser);
            $task->setRequestedBy($adminUser);
            $task->setProject($userProject);
            if ($tagHome instanceof Tag) {
                $task->addTag($tagHome);
            }
            if ($tagFreeTime instanceof Tag) {
                $task->addTag($tagFreeTime);
            }
            $task->setCompany($webSolCompany);
            $task->setStatus($newStatus);
            $manager->persist($task);

            for ($numberOfTasks = 4; $numberOfTasks < 1000; $numberOfTasks++) {
                $task = new Task();
                $task->setTitle('Task ' . $numberOfTasks);
                $task->setDescription('Description of Admins Task ' . $numberOfTasks);
                $task->setImportant(true);
                $task->setCreatedBy($adminUser);
                $task->setRequestedBy($adminUser);
                $task->setCompany($lanCompany);
                $task->setStatus($newStatus);
                $task->setProject($usersProject2);
                if ($tagHome instanceof Tag) {
                    $task->addTag($tagHome);
                }
                if ($tagFreeTime instanceof Tag) {
                    $task->addTag($tagFreeTime);
                }

                $manager->persist($task);
            }

            for ($numberOfTasks = 2001; $numberOfTasks < 3000; $numberOfTasks++) {
                $task = new Task();
                $task->setTitle('Task ' . $numberOfTasks);
                $task->setDescription('Description of Users Task ' . $numberOfTasks);
                $task->setImportant(true);
                $task->setCreatedBy($userUser);
                $task->setRequestedBy($userUser);
                $task->setCompany($lanCompany);
                $task->setStatus($inProgresStatus);
                $task->setProject($adminsProject);
                $task->setStartedAt(new \DateTime());

                $manager->persist($task);
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