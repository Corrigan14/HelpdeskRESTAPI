<?php

namespace API\TaskBundle\DataFixtures\ORMDEVELOPMENT;

use API\TaskBundle\Entity\CompanyData;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CompanyDataFixture
 * @package API\TaskBundle\DataFixtures\ORMDEVELOPMENT
 */
class CompanyDataFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $company = $manager->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'Web-Solutions'
        ]);

        $companyAttributeInteger = $manager->getRepository('APITaskBundle:CompanyAttribute')->findOneBy([
            'title' => 'integer number company additional attribute'
        ]);

        $companyAttributeString = $manager->getRepository('APITaskBundle:CompanyAttribute')->findOneBy([
            'title' => 'input company additional attribute'
        ]);

        if ($company && $companyAttributeInteger) {
            $cd = new CompanyData();
            $cd->setCompany($company);
            $cd->setCompanyAttribute($companyAttributeInteger);
            $cd->setValue(10);
            $manager->persist($cd);
        }

        if ($company && $companyAttributeString) {
            $cd = new CompanyData();
            $cd->setCompany($company);
            $cd->setCompanyAttribute($companyAttributeString);
            $cd->setValue('String DATA');
            $manager->persist($cd);
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
        return 5;
    }
}