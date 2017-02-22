<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\SystemSettings;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SystemSettingsFixture
 */
class SystemSettingsFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $systemSetting = new SystemSettings();
        $systemSetting->setTitle('Company Name');
        $systemSetting->setValue('Lan Systems');
        $systemSetting->setIsActive(true);
        $manager->persist($systemSetting);

        $systemSetting = new SystemSettings();
        $systemSetting->setTitle('Logo');
        $systemSetting->setValue('Slug pre logo');
        $systemSetting->setIsActive(true);
        $manager->persist($systemSetting);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 18;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}