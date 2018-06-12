<?php

namespace API\TaskBundle\DataFixtures\ORMDEVELOPMENT;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\TaskSubtask;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaskSubtaskFixture
 *
 * @package API\TaskBundle\DataFixtures\ORMDEVELOPMENT
 */
class TaskSubtaskFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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

        $task = $manager->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 1'
        ]);

        if ($userUser instanceof User && $task instanceof Task) {
            $subtask = new TaskSubtask();
            $subtask->setCreatedBy($userUser);
            $subtask->setTask($task);
            $subtask->setTitle('First Subtask');
            $subtask->setDone(false);
            $manager->persist($subtask);

            $subtask = new TaskSubtask();
            $subtask->setCreatedBy($userUser);
            $subtask->setTask($task);
            $subtask->setTitle('Second Subtask');
            $subtask->setDone(false);
            $manager->persist($subtask);

            $subtask = new TaskSubtask();
            $subtask->setCreatedBy($userUser);
            $subtask->setTask($task);
            $subtask->setTitle('Third Subtask');
            $subtask->setDone(true);
            $manager->persist($subtask);

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
        return 13;
    }
}