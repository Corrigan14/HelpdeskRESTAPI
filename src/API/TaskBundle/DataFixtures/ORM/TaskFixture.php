<?php

namespace API\TaskBundle\DataFixtures\ORM;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\StatusOptions;
use API\TaskBundle\Security\TaskWorkTypeOptions;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaskFixture
 *
 * @package API\TaskBundle\DataFixtures\ORM
 */
class TaskFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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

        $adminUser = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'admin'
        ]);

        $managerUser = $manager->getRepository('APICoreBundle:User')->findOneBy([
            'username' => 'manager'
        ]);

        $adminsProject = $manager->getRepository('APITaskBundle:Project')->findOneBy([
            'title' => 'Project of admin'
        ]);

        $lanCompany = $manager->getRepository('APICoreBundle:Company')->findOneBy([
            'title' => 'LanSystems'
        ]);

        $newStatus = $manager->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => StatusOptions::NEW
        ]);

        $inProgresStatus = $manager->getRepository('APITaskBundle:Status')->findOneBy([
            'title' => StatusOptions::IN_PROGRESS
        ]);

        if ($adminUser instanceof User && $managerUser instanceof User) {
            for ($numberOfTasks = 1; $numberOfTasks < 1000; $numberOfTasks++) {
                $task = new Task();
                $task->setTitle('Task ' . $numberOfTasks);
                $task->setDescription('Description of Admins Task ' . $numberOfTasks);
                $task->setImportant(true);
                $task->setCreatedBy($adminUser);
                $task->setRequestedBy($adminUser);
                $task->setCompany($lanCompany);
                $task->setStatus($newStatus);
                $task->setWorkType(TaskWorkTypeOptions::MATERIAL);
                $task->setProject($adminsProject);

                $manager->persist($task);
            }

            for ($numberOfTasks = 1001; $numberOfTasks < 3000; $numberOfTasks++) {
                $task = new Task();
                $task->setTitle('Task ' . $numberOfTasks);
                $task->setDescription('Description of Users Task ' . $numberOfTasks);
                $task->setImportant(true);
                $task->setCreatedBy($managerUser);
                $task->setRequestedBy($managerUser);
                $task->setCompany($lanCompany);
                $task->setStatus($inProgresStatus);
                $task->setProject($adminsProject);
                $task->setStartedAt(new \DateTime());
                $task->setWorkType(TaskWorkTypeOptions::PROGRAMOVANIE_WWW);

                $manager->persist($task);
            }

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
        return 8;
    }
}