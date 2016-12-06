<?php
namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\CompanyAttribute;
use API\TaskBundle\Services\VariableHelper;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CompanyAttributeFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class CompanyAttributeFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $ca = new CompanyAttribute();
        $ca->setTitle('input company additional attribute');
        $ca->setType(VariableHelper::INPUT);
        $manager->persist($ca);

        $ca = new CompanyAttribute();
        $ca->setTitle('select company additional attribute');
        $ca->setType(VariableHelper::SIMPLE_SELECT);
        $options = [
            'select1' => 'select1',
            'select2' => 'select2',
            'select3' => 'select3',
        ];
        $ca->setOptions($options);
        $manager->persist($ca);

        $ca = new CompanyAttribute();
        $ca->setTitle('integer number company additional attribute');
        $ca->setType(VariableHelper::INTEGER_NUMBER);
        $manager->persist($ca);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 4;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}