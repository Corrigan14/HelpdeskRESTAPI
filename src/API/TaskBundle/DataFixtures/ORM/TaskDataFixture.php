<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Entity\TaskData;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaskDataFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class TaskDataFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $taskAttributeInput = $manager->getRepository('APITaskBundle:TaskAttribute')->findOneBy([
            'title' => 'input task additional attribute'
        ]);

        $taskAttributeSelect = $manager->getRepository('APITaskBundle:TaskAttribute')->findOneBy([
            'title' => 'select task additional attribute'
        ]);

        $usersTask = $manager->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 1 - user is creator, user is requested'
        ]);

        if ($taskAttributeInput instanceof TaskAttribute) {
            $td = new TaskData();
            $td->setTaskAttribute($taskAttributeInput);
            $td->setValue('some input');
            $td->setTask($usersTask);
            $manager->persist($td);
        }

        if ($taskAttributeSelect instanceof TaskAttribute) {
            $td = new TaskData();
            $td->setTaskAttribute($taskAttributeSelect);
            $td->setValue('select1');
            $td->setTask($usersTask);
            $manager->persist($td);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 10;
    }
}