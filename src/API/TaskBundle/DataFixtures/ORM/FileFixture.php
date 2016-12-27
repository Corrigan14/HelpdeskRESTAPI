<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\File;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FileFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class FileFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $file = new File();
        $file->setName('Test file name');
        $file->setSlug('zsskcd-jpg-2016-12-17-15-36');
        $file->setTempName('Temp name');
        $file->setType('jpeg');
        $file->setSize(300);
        $file->setUploadDir('Upload dir');
        $file->setPublic(false);

        $manager->persist($file);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 11;
    }
}