<?php
namespace API\TaskBundle\DataFixtures\ORM;

use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Services\VariableHelper;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaskAttributeFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class TaskAttributeFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $ta = new TaskAttribute();
        $ta->setTitle('input task additional attribute');
        $ta->setType(VariableHelper::INPUT);
        $ta->setDescription('Test description of Input');
        $manager->persist($ta);

        $ta = new TaskAttribute();
        $ta->setTitle('select task additional attribute');
        $ta->setType(VariableHelper::SIMPLE_SELECT);
        $ta->setDescription('Test description of Simple select');
        $options = [
            'select1' => 'select1',
            'select2' => 'select2',
            'select3' => 'select3',
        ];
        $ta->setOptions($options);
        $manager->persist($ta);

        $ta = new TaskAttribute();
        $ta->setTitle('integer number task additional attribute');
        $ta->setType(VariableHelper::INTEGER_NUMBER);
        $manager->persist($ta);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 8;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}