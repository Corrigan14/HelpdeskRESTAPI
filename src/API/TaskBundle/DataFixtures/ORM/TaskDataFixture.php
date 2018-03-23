<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\Task;
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

        $taskAttributeInteger = $manager->getRepository('APITaskBundle:TaskAttribute')->findOneBy([
            'title' => 'integer number task additional attribute'
        ]);

        $taskAttributeBoolean = $manager->getRepository('APITaskBundle:TaskAttribute')->findOneBy([
            'title' => 'boolean task additional attribute'
        ]);

        $taskAttributeDate = $manager->getRepository('APITaskBundle:TaskAttribute')->findOneBy([
            'title' => 'date task additional attribute'
        ]);

        $usersTask = $manager->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 1'
        ]);

        if ($usersTask instanceof Task) {

            if ($taskAttributeInput instanceof TaskAttribute) {
                $td = new TaskData();
                $td->setTaskAttribute($taskAttributeInput);
                $td->setValue('input');
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

            if ($taskAttributeInteger instanceof TaskAttribute) {
                $td = new TaskData();
                $td->setTaskAttribute($taskAttributeInteger);
                $td->setValue(546);
                $td->setTask($usersTask);
                $manager->persist($td);
            }

            if ($taskAttributeBoolean instanceof TaskAttribute) {
                $td = new TaskData();
                $td->setTaskAttribute($taskAttributeBoolean);
                $td->setBoolValue(false);
                $td->setValue(null);
                $td->setTask($usersTask);
                $manager->persist($td);
            }

            if ($taskAttributeDate instanceof TaskAttribute) {
                $td = new TaskData();
                $td->setTaskAttribute($taskAttributeDate);
                $td->setDateValue(1519185224);
                $td->setValue(null);
                $td->setTask($usersTask);
                $manager->persist($td);
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
        return 10;
    }
}