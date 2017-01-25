<?php

namespace API\CoreBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserDataFixture
 *
 * @package API\CoreBundle\DataFixtures\ORM
 */
class UserDataFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $adminUser = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        if ($adminUser instanceof User) {
            $userData = new UserData();
            $userData->setName('Admin');
            $userData->setSurname('Adminovic');
            $userData->setCity('Bratislava');
            $userData->setFunction('Admin of project');
            $userData->setSignature('Admin Adminovic, Lan Systems s.r.o.');
            $userData->setFacebook('facebook.sk');
            $userData->setTwitter('twitter.sk');
            $userData->setLinkdin('linkdin.sk');
            $userData->setGoogle('google.sk');
            $userData->setUser($adminUser);

            $manager->persist($userData);
            $manager->persist($adminUser);

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
        return 3;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}