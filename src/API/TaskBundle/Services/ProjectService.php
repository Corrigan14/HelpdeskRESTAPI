<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Repository\UserRepository;
use API\CoreBundle\Services\HateoasHelper;
use API\TaskBundle\Repository\ProjectRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class projectService
 *
 * @package API\TaskBundle\Services
 */
class ProjectService
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;


    /**
     * ProjectService constructor.
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
     * Return Projects Response  which includes Data and Links and Pagination
     *
     * @param int $page
     *
     * @param array $options
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getProjectsResponse(int $page, array $options)
    {
        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->em->getRepository('APITaskBundle:Project');
        $projects = $projectRepository->getAllEntities($page, $options);

        $response = [
            'data' => $projects,
        ];
        $pagination = HateoasHelper::getPagination(
            $this->router->generate('projects_list'),
            $page,
            $projectRepository->countEntities($options),
            ProjectRepository::LIMIT,
            $fields = [],
            $options['isActive']
        );

        return array_merge($response, $pagination);
    }

    /**
     * Return Project Response which includes all data about Project Entity and Links to update/partialUpdate/delete
     *
     * @param User $user
     *
     * @return array
     */
    public function getProjectResponse(User $user)
    {
        return [
            'data' => $user,
            '_links' => $this->getProjectLinks($user->getId()),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getProjectLinks(int $id)
    {
        return [
            'put' => $this->router->generate('user_update', ['id' => $id]),
            'patch' => $this->router->generate('user_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('user_delete', ['id' => $id]),
        ];
    }
}