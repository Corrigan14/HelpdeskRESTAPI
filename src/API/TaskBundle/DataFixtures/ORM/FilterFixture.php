<?php

namespace API\TaskBundle\DataFixtures\ORM;


use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Security\StatusOptions;
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
            'title' => StatusOptions::NEW
        ]);
        $newStatId = $status->getId();

        $status = $manager->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => StatusOptions::IN_PROGRESS
        ]);
        $inProgressStatId = $status->getId();

        $admin = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $filter = new Filter();
        $filter->setTitle('DO IT');
        $filter->setFilter('&status=' . $newStatId . ',' . $inProgressStatId . '&assigned=not,current-user');
        $filter->setPublic(true);
        $filter->setCreatedBy($admin);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(true);
        $filter->setIconClass('&#xE88A;');

        $manager->persist($filter);

        $filter = new Filter();
        $filter->setTitle('IMPORTANT');
        $filter->setFilter('&important=TRUE&assigned=current-user');
        $filter->setPublic(true);
        $filter->setCreatedBy($admin);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(false);
        $filter->setIconClass('&#xE838;');

        $manager->persist($filter);

        $filter = new Filter();
        $filter->setTitle('SCHEDULED');
        $filter->setFilter('&startedTime=TO=now');
        $filter->setPublic(true);
        $filter->setCreatedBy($admin);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(false);
        $filter->setIconClass('&#xE858;');

        $manager->persist($filter);

        $filter = new Filter();
        $filter->setTitle('REQUESTED');
        $filter->setFilter('&requester=current-user');
        $filter->setPublic(true);
        $filter->setCreatedBy($admin);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(false);
        $filter->setIconClass('&#xE7EF;');

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