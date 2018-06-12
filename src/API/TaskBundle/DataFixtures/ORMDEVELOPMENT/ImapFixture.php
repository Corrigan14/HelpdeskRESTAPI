<?php

namespace API\TaskBundle\DataFixtures\ORMDEVELOPMENT;

use API\TaskBundle\Entity\Imap;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImapFixture
 *
 * @package API\TaskBundle\DataFixtures\ORMDEVELOPMENT
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
        $imap->setHost('imap.lanhelpdesk.com');
        $imap->setPort(143);
        $imap->setName('symfony@lanhelpdesk.com');
        $imap->setPassword('eglrdAYVXB@18');
        $imap->setSsl('false');
        $imap->setInboxEmail('symfony@lanhelpdesk.com');
        $imap->setMoveEmail('done@done.sk');
        $imap->setIgnoreCertificate(true);
        $imap->setIsActive(true);
        $imap->setDescription('Lanhelpdesk IMAP');
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