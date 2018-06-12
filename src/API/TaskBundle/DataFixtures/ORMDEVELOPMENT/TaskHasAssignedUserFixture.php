<?php

namespace API\TaskBundle\DataFixtures\ORMDEVELOPMENT;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaskHasAssignedUserFixture
 *
 * @package API\TaskBundle\DataFixtures\ORMDEVELOPMENT
 */
class TaskHasAssignedUserFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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

        $status = $manager->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => 'Completed'
        ]);

        if ($userUser instanceof User && $task instanceof Task && $status instanceof Status) {
            $taskHasAssignedUser = new TaskHasAssignedUser();
            $taskHasAssignedUser->setTask($task);
            $taskHasAssignedUser->setStatus($status);
            $taskHasAssignedUser->setUser($userUser);
            $taskHasAssignedUser->setActual(true);
            $manager->persist($taskHasAssignedUser);
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