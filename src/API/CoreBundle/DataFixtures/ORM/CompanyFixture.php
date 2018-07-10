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
class CompanyFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $company->setTitle('Unassigned')
            ->setIco('000000')
            ->setDic('000000000000')
            ->setStreet('000000')
            ->setZip('000000')
            ->setCity('000000')
            ->setCountry('000000');
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