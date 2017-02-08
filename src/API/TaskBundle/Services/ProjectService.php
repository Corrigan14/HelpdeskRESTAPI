<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Repository\UserRepository;
use API\CoreBundle\Services\HateoasHelper;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;
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
    public function getProjectsResponse(int $page, array $options):array
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
     * @param User $user
     * @param bool $isAdmin
     * @param string $rule
     * @return array
     */
    public function getListOfAvailableProjects(User $user, bool $isAdmin, string $rule):array
    {
        if ($isAdmin) {
            return $this->em->getRepository('APITaskBundle:Project')->getAllProjectEntitiesWithIdAndTitle();
        } else {
            return $this->em->getRepository('APITaskBundle:UserHasProject')->getAllProjectEntitiesWithIdAndTitle($user, $rule);
        }
    }

    /**
     * Return Project Response which includes all data about Project Entity and Links to update/partialUpdate/delete
     * @param Project $project
     * @return array
     */
    public function getProjectResponse(Project $project):array
    {
        return [
            'data' => $project,
            '_links' => $this->getProjectLinks($project->getId()),
        ];
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntityWithTaskResponse(int $id):array
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->getEntityWithTasks($id);

        return [
            'data' => $project[0],
            '_links' => $this->getProjectLinks($id),
        ];
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEntityResponse(int $id):array
    {
        $project = $this->em->getRepository('APITaskBundle:Project')->getEntity($id);

        return [
            'data' => $project[0],
            '_links' => $this->getProjectLinks($id),
        ];
    }

    /**
     * Return UserHasProject Response which includes all data about UHP Entity and Links to update/partialUpdate/delete
     * @param UserHasProject $userHasProject
     * @param int $projectId
     * @param int $userId
     * @return array
     */
    public function getUserHasProjectResponse(UserHasProject $userHasProject, int $projectId, int $userId):array
    {
        return [
            'data' => $userHasProject,
            '_links' => $this->getUserHasProjectLinks($projectId, $userId),
        ];
    }


    /**
     * @param int $projectId
     * @param int $userId
     * @return array
     */
    private function getUserHasProjectLinks(int $projectId, int $userId):array
    {
        return [
            'put' => $this->router->generate('project_update_user_acl', ['projectId' => $projectId, 'userId' => $userId]),
            'delete' => $this->router->generate('project_remove_from_user', ['projectId' => $projectId, 'userId' => $userId]),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getProjectLinks(int $id):array
    {
        return [
            'put' => $this->router->generate('projects_update', ['id' => $id]),
            'patch' => $this->router->generate('projects_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('projects_delete', ['id' => $id]),
        ];
    }
}