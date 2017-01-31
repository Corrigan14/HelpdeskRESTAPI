<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Tag;
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
        /** @var User $user */
        $admin = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin',
        ]);

        /** @var User $user */
        $user = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user',
        ]);

        if($user instanceof User && $admin instanceof User){
            /** @var \API\TaskBundle\Entity\Tag $tag1 */
            $tag1 = new Tag();
            $tag1->setTitle('Free Time');
            $tag1->setColor('BF4848');
            $tag1->setPublic(true);
            $tag1->setCreatedBy($admin);

            $tag2 = new Tag();
            $tag2->setTitle('Work');
            $tag2->setColor('4871BF');
            $tag2->setPublic(true);
            $tag2->setCreatedBy($admin);

            $tag3 = new Tag();
            $tag3->setTitle('Home');
            $tag3->setColor('DFD112');
            $tag3->setPublic(false);
            $tag3->setCreatedBy($user);

            $tag4 = new Tag();
            $tag4->setTitle('Another Admin Public Tag');
            $tag4->setColor('DFD115');
            $tag4->setPublic(false);
            $tag4->setCreatedBy($admin);

            $manager->persist($tag1);
            $manager->persist($tag2);
            $manager->persist($tag3);
            $manager->persist($tag4);
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
}