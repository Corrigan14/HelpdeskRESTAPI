<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ProjectFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class ProjectFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userUser = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $userAdmin = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        if ($userUser instanceof User) {
            $project = new Project();
            $project->setTitle('Project of user 1');
            $project->setCreatedBy($userUser);
            $project->setDescription('Description of project 1.');

            $manager->persist($project);

            $project = new Project();
            $project->setTitle('Project of user 2');
            $project->setCreatedBy($userUser);
            $project->setDescription('Description of project 2.');

            $manager->persist($project);
            $manager->flush();
        }

        if ($userAdmin instanceof User) {
            $project = new Project();
            $project->setTitle('Project of admin');
            $project->setCreatedBy($userAdmin);
            $project->setDescription('Description of project of admin.');

            $manager->persist($project);
            $manager->flush();
        }

        if ($userAdmin instanceof User) {
            $project = new Project();
            $project->setTitle('Project of admin 2');
            $project->setCreatedBy($userAdmin);
            $project->setDescription('Description of second project of admin.');

            $manager->persist($project);
            $manager->flush();
        }

        if ($userAdmin instanceof User) {
            $project = new Project();
            $project->setTitle('Project of admin 3 - inactive');
            $project->setCreatedBy($userAdmin);
            $project->setIsActive(false);
            $project->setDescription('Description of third project of admin.');

            $manager->persist($project);
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
        return 6;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}