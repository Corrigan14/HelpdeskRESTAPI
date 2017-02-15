<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\Smtp;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SmtpFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class SmtpFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $smtp = new Smtp();
        $smtp->setHost('Host');
        $smtp->setPort(3306);
        $smtp->setEmail('mb@web-solutions.sk');
        $smtp->setName('test');
        $smtp->setPassword('test');
        $smtp->setSsl(true);
        $smtp->setTls(false);

        $manager->persist($smtp);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 16;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}