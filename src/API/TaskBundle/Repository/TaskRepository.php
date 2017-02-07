<?php

namespace API\TaskBundle\Repository;

use API\TaskBundle\Services\VariableHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * TaskRepository
 */
class TaskRepository extends EntityRepository
{
    const LIMIT = 10;

    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param int $page
     * @param array $options
     * @return array
     */
    public function getAllAdminTasks(int $page, array $options)
    {
        $inFilter = $options['inFilter'];
        $equalFilter = $options['equalFilter'];
        $dateFilter = $options['dateFilter'];
        $isNullFilter = $options['isNullFilter'];
        $searchFilter = $options['searchFilter'];
        $notAndCurrentFilter = $options['notAndCurrentFilter'];
        $inFilterAddedParams = $options['inFilterAddedParams'];
        $equalFilterAddedParams = $options['equalFilterAddedParams'];
        $dateFilterAddedParams = $options['dateFilterAddedParams'];

        $query = $this->createQueryBuilder('task')
            ->select('task, taskData, taskAttribute, project, createdBy, company, requestedBy, thau, status, assignedUser, creatorDetailData, requesterDetailData, tags, taskCompany')
            ->leftJoin('task.taskData', 'taskData')
            ->leftJoin('taskData.taskAttribute', 'taskAttribute')
            ->leftJoin('task.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
            ->leftJoin('task.createdBy', 'createdBy')
            ->leftJoin('createdBy.detailData', 'creatorDetailData')
            ->leftJoin('createdBy.company', 'company')
            ->leftJoin('task.requestedBy', 'requestedBy')
            ->leftJoin('requestedBy.detailData', 'requesterDetailData')
            ->leftJoin('task.taskHasAssignedUsers', 'thau')
            ->leftJoin('thau.status', 'status')
            ->leftJoin('thau.user', 'assignedUser')
            ->leftJoin('task.tags', 'tags')
            ->leftJoin('task.company', 'taskCompany');

        if (array_key_exists('tags.id', $inFilter)) {
            $query->innerJoin('task.tags', 'tags');
        }

        if (array_key_exists('followers.id', $inFilter) || array_key_exists('followers.id', $equalFilter)) {
            $query->innerJoin('task.followers', 'followers');
        }

        $query->where('task.id is not NULL');

        $paramArray = [];
        $paramNum = 0;
        if (null !== $searchFilter) {
            $query->andWhere('task.id LIKE :taskIdParam');
            $query->orWhere('task.title LIKE :taskTitleParam');
            $paramArray['taskIdParam'] = '%' . $searchFilter . '%';
            $paramArray['taskTitleParam'] = '%' . $searchFilter . '%';
        }

        foreach ($isNullFilter as $value) {
            // check if query is allowed
            if (in_array($value, VariableHelper::$allowedKeysInFilter)) {
                $query->andWhere($value . ' IS NULL');
            }
        }

        foreach ($inFilter as $key => $value) {
            // check if query is allowed
            if (in_array($key, VariableHelper::$allowedKeysInFilter)) {
                $query->andWhere($key . ' IN (:parameters' . $paramNum . ')');
                $paramArray['parameters' . $paramNum] = $value;

                $paramNum++;
            }
        }

        foreach ($equalFilter as $key => $value) {
            if (in_array($key, VariableHelper::$allowedKeysInFilter)) {
                $query->andWhere($key . ' = :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
            }
        }

        foreach ($notAndCurrentFilter as $filter) {
            if (in_array($filter['not'], VariableHelper::$allowedKeysInFilter) && in_array($filter['equal']['key'], VariableHelper::$allowedKeysInFilter)) {
                $query->andWhere($filter['not'] . ' IS NULL');
                $query->orWhere($filter['equal']['key'] . ' = :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $filter['equal']['value'];
            }
        }

        foreach ($dateFilter as $key => $value) {
            if (in_array($key, VariableHelper::$allowedKeysInFilter)) {
                if (isset($value['from']) && isset($value['to'])) {
                    $query->andWhere($query->expr()->between($key, ':FROM' . $paramNum, ':TO' . $paramNum));
                    $paramArray['FROM' . $paramNum] = $value['from'];
                    $paramArray['TO' . $paramNum] = $value['to'];

                    $paramNum++;
                } elseif (isset($value['from']) && !isset($value['to'])) {
                    $query->andWhere($key . '>= :FROM' . $paramNum);
                    $paramArray['FROM' . $paramNum] = $value['from'];

                    $paramNum++;
                } elseif (isset($value['to']) && !isset($value['from'])) {
                    if (strtolower($value['to']) === 'now') {
                        $query->andWhere($key . '<= :TO' . $paramNum);
                        $nowDate = new \DateTime();
                        $nowDate->format('Y-m-d H:i:s');
                        $paramArray['TO' . $paramNum] = $nowDate;
                    } else {
                        $query->andWhere($key . '<= :TO' . $paramNum);
                        $paramArray['TO' . $paramNum] = $value['to'];
                    }
                    $paramNum++;
                }
            }
        }

        foreach ($inFilterAddedParams as $key => $value) {
            $query->andWhere('taskAttribute.id = :attributeId');
            $query->andWhere('taskData.value IN (:parameters' . $paramNum . ')');
            $paramArray['parameters' . $paramNum] = $value;
            $paramArray['attributeId'] = $key;

            $paramNum++;
        }

        foreach ($equalFilterAddedParams as $key => $value) {
            $query->andWhere('taskAttribute.id = :attributeId');
            $query->andWhere('taskData.value = :parameter' . $paramNum);
            $paramArray['parameter' . $paramNum] = $value;
            $paramArray['attributeId'] = $key;

            $paramNum++;
        }

        foreach ($dateFilterAddedParams as $key => $value) {
            if (isset($value[0])) {
                if (isset($value[1])) {
                    $query->andWhere('taskAttribute.id = :attributeId');
                    $query->andWhere($query->expr()->between('taskData.value', ':FROM' . $paramNum, ':TO' . $paramNum));
                    $paramArray['FROM' . $paramNum] = $value[0];
                    $paramArray['TO' . $paramNum] = $value[1];
                    $paramArray['attributeId'] = $key;

                    $paramNum++;
                }
            }
        }

        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        $query->setMaxResults(self::LIMIT);

        // Pagination calculating offset
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param array $options
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countAllAdminTasks(array $options)
    {
        $inFilter = $options['inFilter'];
        $equalFilter = $options['equalFilter'];
        $dateFilter = $options['dateFilter'];
        $isNullFilter = $options['isNullFilter'];
        $searchFilter = $options['searchFilter'];
        $inFilterAddedParams = $options['inFilterAddedParams'];
        $equalFilterAddedParams = $options['equalFilterAddedParams'];
        $dateFilterAddedParams = $options['dateFilterAddedParams'];

        $query = $this->createQueryBuilder('task')
            ->select('COUNT(DISTINCT task)')
            ->leftJoin('task.taskData', 'taskData')
            ->leftJoin('taskData.taskAttribute', 'taskAttribute')
            ->leftJoin('task.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
            ->leftJoin('task.createdBy', 'createdBy')
            ->leftJoin('createdBy.company', 'company')
            ->leftJoin('task.requestedBy', 'requestedBy')
            ->leftJoin('task.taskHasAssignedUsers', 'thau')
            ->leftJoin('thau.status', 'status')
            ->leftJoin('thau.user', 'assignedUser');

        if (array_key_exists('tags.id', $inFilter)) {
            $query->innerJoin('task.tags', 'tags');
        }

        if (array_key_exists('followers.id', $inFilter) || array_key_exists('followers.id', $equalFilter)) {
            $query->innerJoin('task.followers', 'followers');
        }

        $query->where('task.id is not NULL');

        $paramArray = [];
        $paramNum = 0;

        if (null !== $searchFilter) {
            $query->andWhere('task.id LIKE :taskIdParam');
            $query->orWhere('task.title LIKE :taskTitleParam');
            $paramArray['taskIdParam'] = '%' . $searchFilter . '%';
            $paramArray['taskTitleParam'] = '%' . $searchFilter . '%';
        }

        foreach ($isNullFilter as $value) {
            // check if query is allowed
            if (in_array($value, VariableHelper::$allowedKeysInFilter)) {
                $query->andWhere($value . ' IS NULL');
            }
        }

        foreach ($inFilter as $key => $value) {
            if (in_array($key, VariableHelper::$allowedKeysInFilter)) {
                $query->andWhere($key . ' IN (:parameters' . $paramNum . ')');
                $paramArray['parameters' . $paramNum] = $value;

                $paramNum++;
            }
        }

        foreach ($equalFilter as $key => $value) {
            if (in_array($key, VariableHelper::$allowedKeysInFilter)) {
                $query->andWhere($key . ' = :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
            }
        }

        foreach ($dateFilter as $key => $value) {
            if (in_array($key, VariableHelper::$allowedKeysInFilter)) {
                if (isset($value['from']) && isset($value['to'])) {
                    $query->andWhere($query->expr()->between($key, ':FROM' . $paramNum, ':TO' . $paramNum));
                    $paramArray['FROM' . $paramNum] = $value['from'];
                    $paramArray['TO' . $paramNum] = $value['to'];

                    $paramNum++;
                } elseif (isset($value['from']) && !isset($value['to'])) {
                    $query->andWhere($key . '>= :FROM' . $paramNum);
                    $paramArray['FROM' . $paramNum] = $value['from'];

                    $paramNum++;
                } elseif (isset($value['to']) && !isset($value['from'])) {
                    $query->andWhere($key . '<= :TO' . $paramNum);
                    $paramArray['TO' . $paramNum] = $value['to'];

                    $paramNum++;
                }
            }
        }
        foreach ($inFilterAddedParams as $key => $value) {
            $query->andWhere('taskAttribute.id = :attributeId');
            $query->andWhere('taskData.value IN (:parameters' . $paramNum . ')');
            $paramArray['parameters' . $paramNum] = $value;
            $paramArray['attributeId'] = $key;

            $paramNum++;
        }

        foreach ($equalFilterAddedParams as $key => $value) {
            $query->andWhere('taskAttribute.id = :attributeId');
            $query->andWhere('taskData.value = :parameter' . $paramNum);
            $paramArray['parameter' . $paramNum] = $value;
            $paramArray['attributeId'] = $key;

            $paramNum++;
        }

        foreach ($dateFilterAddedParams as $key => $value) {
            if (isset($value[0])) {
                if (isset($value[1])) {
                    $query->andWhere('taskAttribute.id = :attributeId');
                    $query->andWhere($query->expr()->between('taskData.value', ':FROM' . $paramNum, ':TO' . $paramNum));
                    $paramArray['FROM' . $paramNum] = $value[0];
                    $paramArray['TO' . $paramNum] = $value[1];
                    $paramArray['attributeId'] = $key;

                    $paramNum++;
                }
            }
        }

        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $page
     * @param int $userId
     * @param int $companyId
     * @param       $dividedProjects
     * @param array $options
     *
     * @return array|null
     */
    public function getAllUsersTasks(int $page, int $userId, int $companyId, $dividedProjects, array $options)
    {
        if (array_key_exists('VIEW_ALL_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $allTasksInProject */
            $allTasksInProject = $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'];
        } else {
            $allTasksInProject = [];
        }

        if (array_key_exists('VIEW_COMPANY_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $companyTasksInProject */
            $companyTasksInProject = $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'];
        } else {
            $companyTasksInProject = [];
        }

        $query = $this->createQueryBuilder('t')
            ->where('t.createdBy = :userId')
            ->orWhere('t.requestedBy = :userId');
        $paramArray['userId'] = $userId;

        $paramNum = 0;
        if (count($allTasksInProject) > 0) {
            foreach ($allTasksInProject as $project) {
                $query->orWhere('t.project = :project' . $paramNum);
                $paramArray['project' . $paramNum] = $project;

                $paramNum++;
            }
        }

        if (count($companyTasksInProject) > 0) {
            foreach ($companyTasksInProject as $project) {
                $query->orWhere('t.project = :project' . $paramNum)
                    ->leftJoin('t.createdBy', 'u')
                    ->andWhere('u.company = :companyId');
                $paramArray['project' . $paramNum] = $project;

                $paramNum++;
            }
            $paramArray['companyId'] = $companyId;
        }

        foreach ($options as $key => $value) {
            if (false !== $value) {
                $query->andWhere($key . '= :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
            }
        }

        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        $query->setMaxResults(self::LIMIT);

        // Pagination calculating offset
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        }

        return $query->getQuery()->getArrayResult();
    }


    /**
     * @param int $userId
     * @param int $companyId
     * @param       $dividedProjects
     * @param array $options
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countAllUsersTasks(int $userId, int $companyId, $dividedProjects, array $options)
    {
        if (array_key_exists('VIEW_ALL_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $allTasksInProject */
            $allTasksInProject = $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'];
        } else {
            $allTasksInProject = [];
        }

        if (array_key_exists('VIEW_COMPANY_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $companyTasksInProject */
            $companyTasksInProject = $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'];
        } else {
            $companyTasksInProject = [];
        }

        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.createdBy = :userId')
            ->orWhere('t.requestedBy = :userId');
        $paramArray['userId'] = $userId;

        $paramNum = 0;
        if (count($allTasksInProject) > 0) {
            foreach ($allTasksInProject as $project) {
                $query->orWhere('t.project = :project' . $paramNum);
                $paramArray['project' . $paramNum] = $project;

                $paramNum++;
            }
        }

        if (count($companyTasksInProject) > 0) {
            foreach ($companyTasksInProject as $project) {
                $query->orWhere('t.project = :project' . $paramNum)
                    ->leftJoin('t.createdBy', 'u')
                    ->andWhere('u.company = :companyId');
                $paramArray['project' . $paramNum] = $project;

                $paramNum++;
            }
            $paramArray['companyId'] = $companyId;
        }

        foreach ($options as $key => $value) {
            if (false !== $value) {
                $query->andWhere($key . '= :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
            }
        }

        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $taskId
     * @return array
     */
    public function getTask(int $taskId)
    {
        $query = $this->createQueryBuilder('task')
            ->select('task, taskData, taskAttribute, project, createdBy, company, requestedBy, thau, status, assignedUser, creatorDetailData, requesterDetailData, tags, taskCompany, attachments')
            ->leftJoin('task.taskData', 'taskData')
            ->leftJoin('taskData.taskAttribute', 'taskAttribute')
            ->leftJoin('task.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
            ->leftJoin('task.createdBy', 'createdBy')
            ->leftJoin('createdBy.detailData', 'creatorDetailData')
            ->leftJoin('createdBy.company', 'company')
            ->leftJoin('task.requestedBy', 'requestedBy')
            ->leftJoin('requestedBy.detailData', 'requesterDetailData')
            ->leftJoin('task.taskHasAssignedUsers', 'thau')
            ->leftJoin('thau.status', 'status')
            ->leftJoin('thau.user', 'assignedUser')
            ->leftJoin('task.tags', 'tags')
            ->leftJoin('task.company', 'taskCompany')
            ->leftJoin('task.taskHasAttachments','attachments')
            ->where('task.id = :taskId')
            ->setParameter('taskId', $taskId);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param int $taskId
     * @param int $page
     * @return array
     */
    public function getTasksTags(int $taskId, int $page): array
    {
        $query = $this->createQueryBuilder('t')
            ->select('t,tag')
            ->where('t.id = :taskId')
            ->leftJoin('t.tags', 'tag')
            ->setParameter('taskId', $taskId);

        $query->setMaxResults(TaskRepository::LIMIT);

        // Pagination calculating offset
        if (1 < $page) {
            $query->setFirstResult(TaskRepository::LIMIT * $page - TaskRepository::LIMIT);
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param int $taskId
     * @param int $page
     * @return array
     */
    public function getTasksFollowers(int $taskId, int $page): array
    {
        $query = $this->createQueryBuilder('t')
            ->select('t,follower')
            ->where('t.id = :taskId')
            ->leftJoin('t.followers', 'follower')
            ->setParameter('taskId', $taskId);

        $query->setMaxResults(TaskRepository::LIMIT);

        // Pagination calculating offset
        if (1 < $page) {
            $query->setFirstResult(TaskRepository::LIMIT * $page - TaskRepository::LIMIT);
        }

        return $query->getQuery()->getArrayResult();
    }
}
