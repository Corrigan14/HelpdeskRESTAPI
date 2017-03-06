<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\Status;
use API\TaskBundle\Security\StatusOptions;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class StatusFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class StatusFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $status = new Status();
        $status->setTitle(StatusOptions::NEW);
        $status->setDescription('New task');
        $status->setColor('#1E90FF');
        $status->setOrder(1);
        $manager->persist($status);

        $status = new Status();
        $status->setTitle(StatusOptions::IN_PROGRESS);
        $status->setDescription('In progress task');
        $status->setColor('#32CD32');
        $status->setOrder(2);
        $manager->persist($status);

        $status = new Status();
        $status->setTitle(StatusOptions::COMPLETED);
        $status->setDescription('Completed task');
        $status->setColor('#FF4500');
        $status->setOrder(3);
        $manager->persist($status);

        $status = new Status();
        $status->setTitle(StatusOptions::CLOSED);
        $status->setDescription('Closed task');
        $status->setColor('#A9A9A9');
        $status->setOrder(4);
        $manager->persist($status);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 3;
    }
}