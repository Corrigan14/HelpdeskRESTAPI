<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends EntityRepository
{
    const LIMIT = 10;

    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param array $options
     * @return mixed
     */
    public function getAllEntities(int $page, array $options = [])
    {
        $loggedUser = $options['loggedUser'];
        $isAdmin = $options['isAdmin'];
        $isActive = $options['isActive'];

        if ('true' === $isActive) {
            $isActiveParam = 1;
        } else {
            $isActiveParam = 0;
        }

        if ($isAdmin) {
            if ('true' === $isActive || 'false' === $isActive) {
                $query = $this->createQueryBuilder('p')
                    ->select('p')
                    ->leftJoin('p.userHasProjects', 'userHasProjects')
                    ->leftJoin('userHasProjects.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->where('p.is_active = :isActive')
                    ->setParameter('isActive', $isActiveParam)
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('p')
                    ->leftJoin('p.userHasProjects', 'userHasProjects')
                    ->leftJoin('userHasProjects.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->getQuery();
            }
        } else {
            if ('true' === $isActive || 'false' === $isActive) {
                $query = $this->createQueryBuilder('p')
                    ->select('p')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->leftJoin('uhp.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->where('p.createdBy = :loggedUser OR uhp.user = :loggedUser')
                    ->andWhere('p.is_active = :isActive')
                    ->setParameters(['loggedUser' => $loggedUser, 'isActive' => $isActiveParam])
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('p')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->leftJoin('uhp.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->where('p.createdBy = :loggedUser OR uhp.user = :loggedUser')
                    ->setParameter('loggedUser', $loggedUser)
                    ->getQuery();
            }
        }

        // Pagination
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        } else {
            $query->setFirstResult(0);
        }

        $query->setMaxResults(self::LIMIT);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = $paginator->count();

        return [
            'count' => $count,
            'array' => $this->formatData($paginator)
        ];
    }

    /**
     * Return's all entities without pagination
     *
     * @param array $options
     * @return array
     */
    public function getAllUsersProjectsWithoutPagination(array $options)
    {
        $loggedUser = $options['loggedUser'];
        $isAdmin = $options['isAdmin'];
        $isActive = $options['isActive'];

        if ($isAdmin) {
            if (true === $isActive || false === $isActive) {
                $query = $this->createQueryBuilder('p')
                    ->select('p, uhp, uhpUser')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->leftJoin('uhp.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->where('p.is_active = :isActive')
                    ->setParameter('isActive', $isActive)
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('p, uhp,uhpUser')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->leftJoin('uhp.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->getQuery();
            }
        } else {
            if (true === $isActive || false === $isActive) {
                $query = $this->createQueryBuilder('p')
                    ->select('p, uhp, uhpUser')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->leftJoin('uhp.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->where('p.createdBy = :loggedUser OR uhp.user = :loggedUser')
                    ->andWhere('p.is_active = :isActive')
                    ->setParameters(['loggedUser' => $loggedUser, 'isActive' => $isActive])
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('p, uhp, uhpUser')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->leftJoin('uhp.user', 'uhpUser')
                    ->orderBy('p.id', 'DESC')
                    ->distinct()
                    ->where('p.createdBy = :loggedUser OR uhp.user = :loggedUser')
                    ->setParameter('loggedUser', $loggedUser)
                    ->getQuery();
            }
        }

        $query = $query->getArrayResult();

        $arrayProcessed = [];
        foreach ($query as $data) {
            $userHasProjects = $data['userHasProjects'];
            $userHasProjectsArray = [];
            if ($userHasProjects) {
                /** @var UserHasProject $item */
                foreach ($userHasProjects as $item) {
                    $userHasProjectsArray[] = [
                        'id' => $item['id'],
                        'user' => [
                            'id' => $item['user']['id'],
                            'username' => $item['user']['username'],
                            'email' => $item['user']['email']
                        ],
                        'acl' => json_decode($item['acl'])
                    ];
                }
            }
            $arrayProcessed [] = [
                'id' => $data['id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'createdAt' => $data['createdAt'],
                'updatedAt' => $data['updatedAt'],
                'is_active' => $data['is_active'],
                'userHasProjects' => $userHasProjectsArray
            ];
        }

        return $arrayProcessed;
    }

    /**
     * @param $id
     * @return array
     */
    public function getEntityWithTasks($id)
    {
        $query = $this->createQueryBuilder('project')
            ->select('project, userHasProjects, tasks, uhpUser')
            ->leftJoin('project.userHasProjects', 'userHasProjects')
            ->leftJoin('userHasProjects.user', 'uhpUser')
            ->leftJoin('project.tasks', 'tasks')
            ->where('project.id = :id')
            ->setParameter('id', $id);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $id
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getEntity($id)
    {
        $query = $this->createQueryBuilder('project')
            ->select('project')
            ->leftJoin('project.userHasProjects', 'userHasProjects')
            ->leftJoin('userHasProjects.user', 'uhpUser')
            ->where('project.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * @return array
     */
    public function getAllProjectEntitiesWithIdAndTitle(): array
    {
        $query = $this->createQueryBuilder('project')
            ->select('project.id, project.title')
            ->where('project.is_active = :isActive')
            ->setParameter('isActive', true);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $paginatorData
     * @return array
     */
    private function formatData($paginatorData): array
    {
        $response = [];
        /** @var Project $data */
        foreach ($paginatorData as $data) {
            $response[] = $this->processData($data);
        }

        return $response;
    }


    /**
     * @param Project $data
     * @return array
     */
    private function processData(Project $data): array
    {
        $userHasProjects = $data->getUserHasProjects();
        $userHasProjectsArray = [];
        if ($userHasProjects) {
            /** @var UserHasProject $item */
            foreach ($userHasProjects as $item) {
                $userHasProjectsArray[] = [
                    'id' => $item->getId(),
                    'user' => [
                        'id' => $item->getUser()->getId(),
                        'username' => $item->getUser()->getUsername(),
                        'email' => $item->getUser()->getEmail()
                    ],
                    'acl' => json_decode($item->getAcl())
                ];
            }
        }

        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'description' => $data->getDescription(),
            'createdAt' => $data->getCreatedAt(),
            'updatedAt' => $data->getUpdatedAt(),
            'is_active' => $data->getIsActive(),
            'userHasProjects' => $userHasProjectsArray
        ];

        return $response;
    }
}
