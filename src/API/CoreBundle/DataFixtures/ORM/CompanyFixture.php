<?php

namespace API\CoreBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\Company;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CompanyFixture
 *
 * @package API\CoreBundle\DataFixtures\ORM
 */
class CompanyFixture implements FixtureInterface , ContainerAwareInterface , OrderedFixtureInterface
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
        $company = new Company();
        $company->setTitle('Web-Solutions')
            ->setIco('1102587')
            ->setDic('12587459644')
            ->setStreet('Cesta 125')
            ->setZip('021478')
            ->setCity('Bratislava')
            ->setCountry('Slovenska Republika');
        $manager->persist($company);

        $company = new Company();
        $company->setTitle('LanSystems')
            ->setIco('11025878')
            ->setDic('1258745899644')
            ->setStreet('Ina cesta 125')
            ->setZip('021478')
            ->setCity('Bratislava')
            ->setCountry('Slovenska Republika');
        $manager->persist($company);

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