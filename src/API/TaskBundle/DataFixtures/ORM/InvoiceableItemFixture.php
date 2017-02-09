<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\InvoiceableItem;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\Unit;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InvoiceableItemFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class InvoiceableItemFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $ksUnit = $manager->getRepository('APITaskBundle:Unit')->findOneBy([
            'title' => 'Kus'
        ]);

        $kgUnit = $manager->getRepository('APITaskBundle:Unit')->findOneBy([
            'title' => 'Kilogram'
        ]);

        $task = $manager->getRepository('APITaskBundle:Task')->findOneBy([
            'title' => 'Task 1'
        ]);

        if ($task instanceof Task && $ksUnit instanceof Unit) {
            $invoiceableItem = new InvoiceableItem();
            $invoiceableItem->setTitle('Keyboard');
            $invoiceableItem->setAmount(2);
            $invoiceableItem->setUnitPrice(50);
            $invoiceableItem->setTask($task);
            $invoiceableItem->setUnit($ksUnit);

            $manager->persist($invoiceableItem);

            $invoiceableItem = new InvoiceableItem();
            $invoiceableItem->setTitle('Mouse');
            $invoiceableItem->setAmount(5);
            $invoiceableItem->setUnitPrice(10);
            $invoiceableItem->setTask($task);
            $invoiceableItem->setUnit($ksUnit);

            $manager->persist($invoiceableItem);
        }

        if ($task instanceof Task && $kgUnit instanceof Unit) {
            $invoiceableItem = new InvoiceableItem();
            $invoiceableItem->setTitle('Flour');
            $invoiceableItem->setAmount(25);
            $invoiceableItem->setUnitPrice(1);
            $invoiceableItem->setTask($task);
            $invoiceableItem->setUnit($kgUnit);

            $manager->persist($invoiceableItem);
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
        return 15;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}