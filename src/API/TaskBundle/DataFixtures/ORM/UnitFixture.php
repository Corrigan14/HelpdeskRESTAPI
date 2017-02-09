<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\Unit;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UnitFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class UnitFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $unit = new Unit();
        $unit->setTitle('Kilogram');
        $unit->setShortcut('Kg');
        $unit->setIsActive(true);
        $manager->persist($unit);

        $unit = new Unit();
        $unit->setTitle('Kus');
        $unit->setShortcut('Ks');
        $unit->setIsActive(true);

        $manager->persist($unit);

        $unit = new Unit();
        $unit->setTitle('Centimeter');
        $unit->setShortcut('cm');
        $unit->setIsActive(true);

        $manager->persist($unit);

        $unit = new Unit();
        $unit->setTitle('Meter');
        $unit->setShortcut('m');
        $unit->setIsActive(true);

        $manager->persist($unit);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}