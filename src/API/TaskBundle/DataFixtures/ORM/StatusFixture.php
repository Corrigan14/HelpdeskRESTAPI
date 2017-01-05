<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\Status;
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
        $status->setTitle('New');
        $manager->persist($status);

        $status = new Status();
        $status->setTitle('In Progress');
        $manager->persist($status);

        $status = new Status();
        $status->setTitle('Completed');
        $manager->persist($status);

        $status = new Status();
        $status->setTitle('Closed');
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