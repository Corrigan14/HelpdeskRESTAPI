<?php
/**
 * Created by PhpStorm.
 * User: julius
 * Date: 3/20/16
 * Time: 12:38 PM
 */

namespace API\CoreBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserFixture
 *
 * @package ScrumBundle\DataFixtures\ORM
 */
class UserFixture implements FixtureInterface , ContainerAwareInterface , OrderedFixtureInterface
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
        $user = new User();
        $user->setEmail('admin@admin.sk')
             ->setUsername('admin')
             ->setRoles(['ROLE_USER']);
        $plainPassword = 'admin';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user , $plainPassword);
        $user->setPassword($encoded);
        $manager->persist($user);

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
}