<?php

namespace API\TaskBundle\Services\RepeatingTask;

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
     * @param array $tasksWithLoggedUserViewACL
     * @return array
     */
    public function getRepeatingTasks(array $options, array $tasksWithLoggedUserViewACL): array
    {
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

}