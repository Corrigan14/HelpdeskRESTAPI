<?php

namespace API\TaskBundle\DataFixtures\ORMDEVELOPMENT;


use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\RepeatingTask\IntervalOptions;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RepeatingTaskFixture
 * @package API\TaskBundle\DataFixtures\ORM
 */
class RepeatingTaskFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $task = $manager->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 1'
        ]);

        if ($task instanceof Task) {
            $repeatingTask = new RepeatingTask();
            $repeatingTask->setTitle('Daily repeating task');
            $repeatingTask->setInterval(IntervalOptions::DAY);
            $repeatingTask->setIntervalLength(2);
            $repeatingTask->setRepeatsNumber(10);
            $repeatingTask->setStartAt(new \DateTime());
            $repeatingTask->setTask($task);
            $manager->persist($repeatingTask);

            $repeatingTask = new RepeatingTask();
            $repeatingTask->setTitle('Weekly repeating task');
            $repeatingTask->setInterval(IntervalOptions::WEEK);
            $repeatingTask->setIntervalLength(1);
            $repeatingTask->setStartAt(new \DateTime());
            $repeatingTask->setTask($task);
            $manager->persist($repeatingTask);

            $repeatingTask = new RepeatingTask();
            $repeatingTask->setTitle('Monthly repeating task');
            $repeatingTask->setInterval(IntervalOptions::MONTH);
            $repeatingTask->setIntervalLength(3);
            $repeatingTask->setStartAt(new \DateTime());
            $repeatingTask->setTask($task);
            $manager->persist($repeatingTask);
        }
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder():int
    {
        return 9;
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