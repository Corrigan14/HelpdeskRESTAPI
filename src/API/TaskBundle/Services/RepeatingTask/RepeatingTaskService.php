<?php

namespace API\TaskBundle\Services\RepeatingTask;


use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Services\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class RepeatingTaskService
 * @package API\TaskBundle\Services\RepeatingTask
 */
class RepeatingTaskService
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;

    /**
     * UserService constructor.
     *
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * @param array $options
     * @return array
     */
    public function getRepeatingTasks(array $options): array
    {
        $tasksWithLoggedUserViewACL = false;
        if (!$options['isAdmin']) {
            $tasksWithLoggedUserViewACL = $this->getTasksWithLoggedUserViewAcl($options['loggedUser']);
        }
        $responseData = $this->em->getRepository('APITaskBundle:RepeatingTask')->getAllEntities($options, $tasksWithLoggedUserViewACL);

        $response ['data'] = $responseData['array'];
        $count = \count($responseData['array']);

        $limit = $options['limit'];
        if (999 !== $limit) {
            $count = $responseData['count'];
        }

        $pagination = PaginationHelper::getPagination($this->router->generate('projects_list'), $limit, $options['page'], $count, $options['filtersForUrl']);

        return array_merge($response, $pagination);
    }

    /**
     * @param User $user
     * @return array
     */
    private function getTasksWithLoggedUserViewAcl(User $user): array
    {
        $response = [];
        $projectTasks = [];

        $userHasProjects = $user->getUserHasProjects();
        /** @var UserHasProject $userHasProject */
        foreach ($userHasProjects as $userHasProject) {
            /** @var Project $project */
            $acl = $userHasProject->getAcl();
            if (null === $acl) {
                continue;
            }
            if (\in_array(ProjectAclOptions::VIEW_ALL_TASKS, $acl, true)) {
                $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'][] = $userHasProject->getProject()->getId();
                continue;
            }
            if (\in_array(ProjectAclOptions::VIEW_TASKS_FROM_USERS_COMPANY, $acl, true)) {
                $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'][] = $userHasProject->getProject()->getId();
                continue;
            }
            if (\in_array(ProjectAclOptions::VIEW_OWN_TASKS, $acl, true)) {
                $dividedProjects['VIEW_OWN_TASKS'][] = $userHasProject->getProject()->getId();
                continue;
            }
        }
        // Vyuzit repositar - TASK, ktory spoji tasky s projektmi a checkne dane podmienky
        // Potrebujem pole povolenych taskId

        return $response;
    }

}