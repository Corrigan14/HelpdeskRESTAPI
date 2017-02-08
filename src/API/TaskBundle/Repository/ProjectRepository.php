<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Repository\RepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends EntityRepository implements RepositoryInterface
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
                    ->select('p, userHasProjects')
                    ->leftJoin('p.userHasProjects','userHasProjects')
                    ->where('p.is_active = :isActive')
                    ->setParameter('isActive', $isActiveParam)
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('p, userHasProjects')
                    ->leftJoin('p.userHasProjects','userHasProjects')
                    ->getQuery();
            }
        } else {
            if ('true' === $isActive || 'false' === $isActive) {
                $query = $this->createQueryBuilder('p')
                    ->select('p, uhp')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->where('p.createdBy = :loggedUser')
                    ->orWhere('uhp.user = :loggedUser')
                    ->andWhere('p.is_active = :isActive')
                    ->setParameters(['loggedUser' => $loggedUser, 'isActive' => $isActiveParam])
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('p, uhp')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->where('p.createdBy = :loggedUser')
                    ->orWhere('uhp.user = :loggedUser')
                    ->setParameter('loggedUser', $loggedUser)
                    ->getQuery();
            }
        }

        $query->setMaxResults(self::LIMIT);

        // Pagination calculating offset
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        }

        return $query->getArrayResult();
    }

    /**
     * Return count of all Entities
     *
     * @param array $options
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countEntities(array $options = []):int
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
                    ->select('COUNT(p.id)')
                    ->where('p.is_active = :isActive')
                    ->setParameter('isActive', $isActiveParam)
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('COUNT(p.id)')
                    ->getQuery();
            }
        } else {
            if ('true' === $isActive || 'false' === $isActive) {
                $query = $this->createQueryBuilder('p')
                    ->select('COUNT(p.id)')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->where('p.createdBy = :loggedUser')
                    ->orWhere('uhp.user = :loggedUser')
                    ->andWhere('p.is_active = :isActive')
                    ->setParameters(['loggedUser' => $loggedUser, 'isActive' => $isActiveParam])
                    ->getQuery();
            } else {
                $query = $this->createQueryBuilder('p')
                    ->select('COUNT(p.id)')
                    ->leftJoin('p.userHasProjects', 'uhp')
                    ->where('p.createdBy = :loggedUser')
                    ->orWhere('uhp.user = :loggedUser')
                    ->setParameter('loggedUser', $loggedUser)
                    ->getQuery();
            }
        }

        return $query->getSingleScalarResult();
    }

    /**
     * @param $id
     * @return array
     */
    public function getEntityWithTasks($id)
    {
        $query = $this->createQueryBuilder('project')
            ->select('project, userHasProjects, tasks')
            ->leftJoin('project.userHasProjects', 'userHasProjects')
            ->leftJoin('project.tasks', 'tasks')
            ->where('project.id = :id')
            ->setParameter('id', $id);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $id
     * @return array
     */
    public function getEntity($id)
    {
        $query = $this->createQueryBuilder('project')
            ->select('project, userHasProjects')
            ->leftJoin('project.userHasProjects', 'userHasProjects')
            ->where('project.id = :id')
            ->setParameter('id', $id);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @return array
     */
    public function getAllProjectEntitiesWithIdAndTitle():array
    {
        $query = $this->createQueryBuilder('project')
            ->select('project.id, project.title')
            ->where('project.is_active = :isActive')
            ->setParameter('isActive', true);

        return $query->getQuery()->getArrayResult();
    }
}
