<?php

namespace API\TaskBundle\DataFixtures\ORM;


use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Security\Filter\FilterAttributeOptions;
use API\TaskBundle\Security\StatusOptions;
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
        /** @var Status $status */
        $status = $manager->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => StatusOptions::NEW
        ]);
        $newStatId = $status->getId();

        /** @var Status $status */
        $status = $manager->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => StatusOptions::IN_PROGRESS
        ]);
        $inProgressStatId = $status->getId();

        /** @var User $admin */
        $admin = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);


        $doItFilter = [
            FilterAttributeOptions::STATUS => $newStatId . ',' . $inProgressStatId,
            FilterAttributeOptions::ASSIGNED => 'not,current-user'
        ];
        $doItColumns = [
            FilterAttributeOptions::TITLE,
            FilterAttributeOptions::CREATOR,
            FilterAttributeOptions::COMPANY,
            FilterAttributeOptions::ASSIGNED,
            FilterAttributeOptions::CREATED,
            FilterAttributeOptions::DEADLINE,
            FilterAttributeOptions::STATUS,
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
        $filter->setOrder(1);
        $filter->setColumns($doItColumns);

        $manager->persist($filter);

        $importantFilter = [
            FilterAttributeOptions::IMPORTANT => 'TRUE',
            FilterAttributeOptions::ASSIGNED => 'current-user'
        ];
        $importantColumns = [
            FilterAttributeOptions::TITLE,
            FilterAttributeOptions::CREATOR,
            FilterAttributeOptions::COMPANY,
            FilterAttributeOptions::ASSIGNED,
            FilterAttributeOptions::CREATED,
            FilterAttributeOptions::DEADLINE,
            FilterAttributeOptions::STATUS,
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
        $filter->setOrder(2);
        $filter->setColumns($importantColumns);

        $manager->persist($filter);

        $scheduledFilter = [
            FilterAttributeOptions::STARTED => 'TO=now'
        ];
        $scheduledColumns = [
            FilterAttributeOptions::TITLE,
            FilterAttributeOptions::CREATOR,
            FilterAttributeOptions::COMPANY,
            FilterAttributeOptions::ASSIGNED,
            FilterAttributeOptions::CREATED,
            FilterAttributeOptions::DEADLINE,
            FilterAttributeOptions::STATUS,
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
        $filter->setOrder(3);
        $filter->setColumns($scheduledColumns);

        $manager->persist($filter);

        $requestedFilter = [
            FilterAttributeOptions::REQUESTER => 'current-user'
        ];
        $requestedColumns = [
            FilterAttributeOptions::TITLE,
            FilterAttributeOptions::CREATOR,
            FilterAttributeOptions::COMPANY,
            FilterAttributeOptions::ASSIGNED,
            FilterAttributeOptions::CREATED,
            FilterAttributeOptions::DEADLINE,
            FilterAttributeOptions::STATUS,
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
        $filter->setOrder(4);
        $filter->setColumns($requestedColumns);


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
        return 7;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}