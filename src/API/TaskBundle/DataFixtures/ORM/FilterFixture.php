<?php

namespace API\TaskBundle\DataFixtures\ORM;


use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Services\FilterAttributeOptions;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FilterFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class FilterFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $status = $manager->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => 'new'
        ]);

        $user = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'user'
        ]);

        $admin = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $project = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of user 1'
        ]);

        $filterData = [
            FilterAttributeOptions::STATUS => $status->getId(),
            FilterAttributeOptions::CREATOR => $user->getId(), $admin->getId(),
            FilterAttributeOptions::ARCHIVED => TRUE
        ];

        $filter = new Filter();
        $filter->setTitle('Users PUBLIC Filter where status=new, creator = admin, user, archived = true');
        $filter->setFilter($filterData);
        $filter->setPublic(true);
        $filter->setCreatedBy($user);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(false);

        $manager->persist($filter);

        $filter = new Filter();
        $filter->setTitle('Admins PRIVATE Filter where status=new, creator = admin, user, archived = true');
        $filter->setFilter($filterData);
        $filter->setPublic(false);
        $filter->setCreatedBy($admin);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(false);

        $manager->persist($filter);

        $filter = new Filter();
        $filter->setTitle('Users PROJECT PRIVATE Filter where status=new, creator = admin, user, archived = true');
        $filter->setFilter($filterData);
        $filter->setPublic(false);
        $filter->setProject($project);
        $filter->setCreatedBy($user);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(false);

        $manager->persist($filter);

        $filter = new Filter();
        $filter->setTitle('Users PROJECT DEFAULT PRIVATE Filter where status=new, creator = admin, user, archived = true');
        $filter->setFilter($filterData);
        $filter->setPublic(false);
        $filter->setProject($project);
        $filter->setCreatedBy($user);
        $filter->setDefault(true);
        $filter->setIsActive(true);
        $filter->setReport(false);

        $manager->persist($filter);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 14;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}