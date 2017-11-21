<?php

namespace API\CoreBundle\Services;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Repository\UserRepository;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Services\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class userService
 * @package API\CoreBundle\Services
 */
class UserService
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
     * Return Users Response  which includes Data and Links and Pagination
     *
     * @param int $page
     *
     * @param string $isActive
     * @param string $order
     * @param array $filtersForUrl
     * @param int $limit
     * @return array
     */
    public function getUsersResponse(int $page, $isActive, string $order, array $filtersForUrl, int $limit)
    {
        $responseData = $this->em->getRepository('APICoreBundle:User')->getCustomUsers($page, $isActive, $order, $limit);

        $response = [
            'data' => $responseData['array'],
        ];

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = \count($responseData['array']);
        }

        $url = $this->router->generate('users_list');

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filtersForUrl);

        return array_merge($response, $pagination);
    }

    /**
     * Return User Response which includes all data about User Entity and Links to update/partialUpdate/delete
     *
     * @param array $ids
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getUserResponse(array $ids)
    {
        $userId = $ids['userId'];
        $user = $this->em->getRepository('APICoreBundle:User')->getUserResponse($userId);

        return [
            'data' => $user,
            '_links' => $this->getUserLinks($ids),
        ];
    }

    /**
     * @param string|bool $term
     * @param int $page
     * @param string|bool $isActive
     * @param array $filtersForUrl
     * @param string $order
     * @param int $limit
     * @return array
     */
    public function getUsersSearchResponse($term, int $page, $isActive, array $filtersForUrl, string $order, int $limit): array
    {
        $responseData = $this->em->getRepository('APICoreBundle:User')->getUsersSearch($term, $page, $isActive, $order, $limit);

        $response = [
            'data' => $responseData['array'],
        ];

        $url = $this->router->generate('user_search');

        if (999 !== $limit) {
            $count = $responseData['count'];
        } else {
            $count = count($responseData['array']);
        }

        $pagination = PaginationHelper::getPagination($url, $limit, $page, $count, $filtersForUrl);

        return array_merge($response, $pagination);
    }

    /**
     * @return array
     */
    public function getListOfAllUsers(): array
    {
        return $this->em->getRepository('APICoreBundle:User')->getAllUserEntitiesWithIdAndTitle();
    }

    /**
     * @param Project $project
     * @param string $rule
     * @return array
     */
    public function getListOfAvailableProjectAssigners(Project $project, string $rule): array
    {
        return $this->em->getRepository('APITaskBundle:UserHasProject')->getAllUserEntitiesWithIdAndTitle($project, $rule);
    }

    /**
     * @param array $ids
     * @return array
     */
    private function getUserLinks(array $ids)
    {
        $userId = $ids['userId'];
        $userRoleId = $ids['userRoleId'];
        $userCompanyId = $ids['userCompanyId'];

        if ($userCompanyId) {
            $linksForCompany = [
                'put: company' => $this->router->generate('user_update_with_company', ['id' => $userId, 'companyId' => $userCompanyId]),
                'put: user-role & company' => $this->router->generate('user_update_with_company_and_user_role', ['id' => $userId, 'userRoleId' => $userRoleId, 'companyId' => $userCompanyId]),
                'patch: company' => $this->router->generate('user_partial_update_with_company', ['id' => $userId, 'companyId' => $userCompanyId]),
                'patch: user-role & company' => $this->router->generate('user_partial_update_with_company_and_user_role', ['id' => $userId, 'userRoleId' => $userRoleId, 'companyId' => $userCompanyId]),
            ];
        } else {
            $linksForCompany = [];
        }

        $otherLinks = [
            'put' => $this->router->generate('user_update', ['id' => $userId]),
            'put: user-role' => $this->router->generate('user_update_with_user_role', ['id' => $userId, 'userRoleId' => $userRoleId]),
            'patch' => $this->router->generate('user_partial_update', ['id' => $userId]),
            'patch: user-role' => $this->router->generate('user_partial_update_with_user_role', ['id' => $userId, 'userRoleId' => $userRoleId]),
            'delete' => $this->router->generate('user_delete', ['id' => $userId]),
            'restore' => $this->router->generate('user_restore', ['id' => $userId]),
        ];

        return array_merge($otherLinks, $linksForCompany);
    }

}