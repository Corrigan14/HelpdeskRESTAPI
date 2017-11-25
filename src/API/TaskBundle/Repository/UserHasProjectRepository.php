<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use Doctrine\ORM\EntityRepository;

/**
 * UserHasProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserHasProjectRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param string $rule
     * @return array
     */
    public function getAllProjectEntitiesWithIdAndTitle(User $user, string $rule):array
    {
        $query = $this->createQueryBuilder('uhp')
            ->select('project.id, project.title')
            ->leftJoin('uhp.project', 'project')
            ->where('uhp.user = :user')
            ->andWhere('uhp.acl LIKE :rule')
            ->andWhere('project.is_active = :isActive')
            ->distinct()
            ->setParameters(['user' => $user, 'rule' => '%' . $rule . '%', 'isActive' => true]);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param User $user
     * @return array
     */
    public function getAllProjectEntitiesWithIdAndTitleWhereUsersACLExists(User $user):array
    {
        $query = $this->createQueryBuilder('uhp')
            ->select('project.id, project.title')
            ->leftJoin('uhp.project', 'project')
            ->where('uhp.user = :user')
            ->andWhere('project.is_active = :isActive')
            ->distinct()
            ->setParameters(['user' => $user, 'isActive' => true]);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param Project $project
     * @param string $rule
     * @return array
     */
    public function getAllUserEntitiesWithIdAndTitle(Project $project, string $rule):array
    {
        $query = $this->createQueryBuilder('uhp')
            ->select('user.id, user.username, userDetailData.name, userDetailData.surname')
            ->leftJoin('uhp.user', 'user')
            ->leftJoin('user.detailData','userDetailData')
            ->where('uhp.project = :project')
            ->andWhere('uhp.acl LIKE :rule')
            ->andWhere('user.is_active = :isActive')
            ->distinct()
            ->setParameters(['project' => $project->getId(), 'rule' => '%' . $rule . '%', 'isActive' => true]);

        return $query->getQuery()->getArrayResult();
    }
}
