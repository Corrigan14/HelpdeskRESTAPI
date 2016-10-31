<?php

namespace API\CoreBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\Tag;
use API\CoreBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TagFixture
 *
 * @package ScrumBundle\DataFixtures\ORM
 */
class TagFixture implements FixtureInterface , ContainerAwareInterface , OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin',
        ]);

        if($user){
            $tag1 = new Tag();
            $tag1->setTitle('Free Time');
            $tag1->setColor('BF4848');
            $tag1->setUser($user);

            $tag2 = new Tag();
            $tag2->setTitle('Work');
            $tag2->setColor('4871BF');
            $tag2->setUser($user);

            $tag3 = new Tag();
            $tag3->setTitle('Home');
            $tag3->setColor('DFD112');
            $tag3->setUser($user);

            $manager->persist($tag1);
            $manager->persist($tag2);
            $manager->persist($tag3);
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
        return 2;
    }
}