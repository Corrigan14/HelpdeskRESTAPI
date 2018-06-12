<?php

namespace API\TaskBundle\DataFixtures\ORMDEVELOPMENT;

use API\TaskBundle\Entity\Smtp;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SmtpFixture
 *
 * @package API\TaskBundle\DataFixtures\ORMDEVELOPMENT
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
        $smtp->setHost('smtp.lanhelpdesk.com');
        $smtp->setPort(25);
        $smtp->setEmail('symfony@lanhelpdesk.com');
        $smtp->setName('symfony@lanhelpdesk.com');
        $smtp->setPassword('eglrdAYVXB@18');
        $smtp->setSsl(false);
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