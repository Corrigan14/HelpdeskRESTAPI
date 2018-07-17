<?php

namespace API\TaskBundle\Services\RepeatingTask;

use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Security\RepeatingTaskIntervalOptions;
use API\TaskBundle\Services\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GetMethods
 * @package API\TaskBundle\Services\RepeatingTask
 */
class GetMethods
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

        $pagination = PaginationHelper::getPagination($this->router->generate('repeating_task_list'), $limit, $options['page'], $count, $options['filtersForUrl']);

        return array_merge($response, $pagination);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getRepeatingTask(int $id): array
    {
        $entity = $this->em->getRepository('APITaskBundle:RepeatingTask')->getEntity($id);

        return [
            'data' => $entity,
            '_links' => $this->getEntityLinks($id),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getEntityLinks(int $id):array
    {
        return [
            'update' => $this->router->generate('repeating_task_update',['repeatingTaskId' => $id]),
            'delete' => $this->router->generate('repeating_task_delete',['repeatingTaskId' => $id]),
            'inactivate' => $this->router->generate('repeating_task_inactivate', ['repeatingTaskId' => $id]),
            'restore' => $this->router->generate('repeating_task_restore', ['repeatingTaskId' => $id]),
        ];
    }

}