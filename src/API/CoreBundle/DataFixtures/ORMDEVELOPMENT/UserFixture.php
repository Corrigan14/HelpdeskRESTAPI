<?php

namespace API\CoreBundle\DataFixtures\ORMDEVELOPMENT;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\TaskBundle\Security\LanguageOptions;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserFixture
 *
 * @package ScrumBundle\DataFixtures\ORMDEVELOPMENT
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
        $companyWS = $manager->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Web-Solutions'
        ]);

        $companyLS = $manager->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'LanSystems'
        ]);

        $adminUserRole = $manager->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'ADMIN'
        ]);

        $managerUserRole = $manager->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'MANAGER'
        ]);

        $agentUserRole = $manager->getRepository('APITaskBundle:UserRole')->findOneBy([
            'title' => 'AGENT'
        ]);

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
        $user->setCompany($companyWS);
        $user->setUserRole($adminUserRole);
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
        $user->setCompany($companyLS);
        $user->setUserRole($managerUserRole);
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
        $user->setCompany($companyLS);
        $user->setUserRole($agentUserRole);
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
        $user->setCompany($companyLS);
        $user->setUserRole($customerUserRole);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('user@user.sk')
            ->setUsername('user')
            ->setRoles(['ROLE_USER']);
        $plainPassword = 'user';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $user->setLanguage($language);
        $user->setCompany($companyLS);
        $user->setUserRole($customerUserRole);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('testuser2@user.sk')
            ->setUsername('testuser2')
            ->setRoles(['ROLE_USER']);
        $plainPassword = 'testuser';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $user->setLanguage($language);
        $user->setCompany($companyLS);
        $user->setUserRole($customerUserRole);
        $manager->persist($user);

        for ($numberOfUsers = 2; $numberOfUsers < 100; $numberOfUsers++) {
            $user = new User();
            $user->setEmail('customer@customer' . $numberOfUsers . '.sk')
                ->setUsername('customer'.$numberOfUsers)
                ->setRoles(['ROLE_USER']);
            $plainPassword = 'customer'.$numberOfUsers;
            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $plainPassword);
            $user->setPassword($encoded);
            $user->setLanguage($language);
            $user->setCompany($companyWS);
            $user->setUserRole($customerUserRole);

            $detailData = new UserData();
            $detailData->setName('Customer'.$numberOfUsers);
            $detailData->setSurname('Customerovic'.$numberOfUsers);

            $manager->persist($user);
            $manager->flush();
            $detailData->setUser($user);

            $manager->persist($detailData);
        }

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