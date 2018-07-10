<?php

namespace API\CoreBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\UserRole;
use API\TaskBundle\Security\LanguageOptions;
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
class UserFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        /** @var Company $company */
        $company = $manager->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Unassigned'
        ]);

        /** @var UserRole $adminUserRole */
        $adminUserRole = $manager->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'ADMIN'
        ]);

        /** @var UserRole $managerUserRole */
        $managerUserRole = $manager->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'MANAGER'
        ]);

        /** @var UserRole $agentUserRole */
        $agentUserRole = $manager->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'AGENT'
        ]);

        /** @var UserRole $customerUserRole */
        $customerUserRole = $manager->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'CUSTOMER'
        ]);

        $language = LanguageOptions::ENGLISH;

        $user = new User();
        $user->setEmail('admin@admin.sk')
            ->setUsername('admin')
            ->setRoles(['ROLE_ADMIN']);
        $plainPassword = 'admin';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $user->setLanguage($language);
        $user->setUserRole($adminUserRole);
        $user->setCompany($company);
        $manager->persist($user);


        $user = new User();
        $user->setEmail('manager@manager.sk')
            ->setUsername('manager')
            ->setRoles(['ROLE_USER']);
        $plainPassword = 'manager';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $user->setLanguage($language);
        $user->setUserRole($managerUserRole);
        $user->setCompany($company);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('agent@agent.sk')
            ->setUsername('agent')
            ->setRoles(['ROLE_USER']);
        $plainPassword = 'agent';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $user->setLanguage($language);
        $user->setUserRole($agentUserRole);
        $user->setCompany($company);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('customer@customer.sk')
            ->setUsername('customer')
            ->setRoles(['ROLE_USER']);
        $plainPassword = 'customer';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $user->setLanguage($language);
        $user->setUserRole($customerUserRole);
        $user->setCompany($company);
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
        return 2;
    }
}