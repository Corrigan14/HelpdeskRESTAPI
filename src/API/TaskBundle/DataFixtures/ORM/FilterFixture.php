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

        $doItFilter = [
            FilterAttributeOptions::STATUS => $newStatId . ',' . $inProgressStatId,
            FilterAttributeOptions::ASSIGNED => 'not,current-user'
        ];
        $filter = new Filter();
        $filter->setTitle('DO IT');
        $filter->setFilter($doItFilter);
        $filter->setPublic(true);
        $filter->setCreatedBy($admin);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(true);
        $filter->setIconClass('&#xE88A;');

        $manager->persist($filter);

        $importantFilter = [
            FilterAttributeOptions::IMPORTANT => 'TRUE',
            FilterAttributeOptions::ASSIGNED => 'current-user'
        ];
        $filter = new Filter();
        $filter->setTitle('IMPORTANT');
        $filter->setFilter($importantFilter);
        $filter->setPublic(true);
        $filter->setCreatedBy($admin);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(false);
        $filter->setIconClass('&#xE838;');

        $manager->persist($filter);

        $scheduledFilter = [
            FilterAttributeOptions::STARTED => 'TO=now'
        ];
        $filter = new Filter();
        $filter->setTitle('SCHEDULED');
        $filter->setFilter($scheduledFilter);
        $filter->setPublic(true);
        $filter->setCreatedBy($admin);
        $filter->setIsActive(true);
        $filter->setReport(false);
        $filter->setDefault(false);
        $filter->setIconClass('&#xE858;');

        $manager->persist($filter);

        $requestedFilter = [
            FilterAttributeOptions::REQUESTER => 'current-user'
        ];
        $filter = new Filter();
        $filter->setTitle('REQUESTED');
        $filter->setFilter($requestedFilter);
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