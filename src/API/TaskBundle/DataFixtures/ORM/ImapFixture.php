<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\Imap;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImapFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class ImapFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $userProject = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        $imap = new Imap();
        $imap->setHost('test');
        $imap->setPort(3306);
        $imap->setName('test');
        $imap->setPassword('test');
        $imap->setSsl(true);
        $imap->setInboxEmail('test@test.sk');
        $imap->setMoveEmail('test@test.sk');
        $imap->setIgnoreCertificate(false);
        $imap->setProject($userProject);

        $manager->persist($imap);
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
}