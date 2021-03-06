<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\File;
use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use API\TaskBundle\Entity\InvoiceableItem;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskData;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\TaskHasAttachment;
use API\TaskBundle\Security\Filter\FilterAttributeOptions;
use API\TaskBundle\Services\VariableHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * TaskRepository
 */
class TaskRepository extends EntityRepository
{
    const LIMIT = 10;

    /**
     * Return's all entities with specific conditions based on actual Entity
     *
     * @param array $options
     * @return array
     */
    public function getAllAdminTasks( array $options):array
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
        $order = $options['order'];
        $limit = $options['limit'];
        $project = $options['project'];
        $page = $options['page'];

        $query = $this->createQueryBuilder('task')
            ->select('task')
            ->addSelect('taskData')
            ->addSelect('taskAttribute')
            ->addSelect('project')
            ->addSelect('projectCreator')
            ->addSelect('createdBy')
            ->addSelect('creatorDetailData')
            ->addSelect('company')
            ->addSelect('requestedBy')
            ->addSelect('requesterDetailData')
            ->addSelect('taskHasAttachments')
            ->addSelect('taskGlobalStatus')
            ->addSelect('assignedUser')
            ->addSelect('assigneeDetailData')
            ->addSelect('tags')
            ->addSelect('taskCompany')
            ->addSelect('followers')
            ->addSelect('followersDetailData')
            ->addSelect('invoiceableItems')
            ->addSelect('unit')
            ->addSelect('taskHasAssignedUsers')
            ->addSelect('assignedUserStatus')
            ->addSelect('assignedUser')
            ->leftJoin('task.taskData', 'taskData')
            ->leftJoin('taskData.taskAttribute', 'taskAttribute')
            ->leftJoin('task.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
            ->leftJoin('task.createdBy', 'createdBy')
            ->leftJoin('createdBy.detailData', 'creatorDetailData')
            ->leftJoin('createdBy.company', 'company')
            ->leftJoin('task.requestedBy', 'requestedBy')
            ->leftJoin('requestedBy.detailData', 'requesterDetailData')
            ->leftJoin('task.taskHasAssignedUsers', 'taskHasAssignedUsers')
            ->leftJoin('task.taskHasAttachments', 'taskHasAttachments')
            ->leftJoin('taskHasAssignedUsers.status', 'assignedUserStatus')
            ->leftJoin('taskHasAssignedUsers.user', 'assignedUser')
            ->leftJoin('assignedUser.detailData', 'assigneeDetailData')
            ->leftJoin('task.tags', 'tags')
            ->leftJoin('task.company', 'taskCompany')
            ->leftJoin('task.status', 'taskGlobalStatus')
            ->leftJoin('task.followers', 'followers')
            ->leftJoin('followers.detailData', 'followersDetailData')
            ->leftJoin('task.invoiceableItems', 'invoiceableItems')
            ->leftJoin('invoiceableItems.unit', 'unit')
            ->distinct();

        foreach ($order as $key => $value) {
            switch ($key) {
                case FilterAttributeOptions::ID:
                    $query->addOrderBy('task.id', $value);
                    break;
                case FilterAttributeOptions::TITLE:
                    $query->addOrderBy('task.title', $value);
                    break;
                case FilterAttributeOptions::STATUS:
                    $query->addOrderBy('taskGlobalStatus.id', $value);
                    break;
                case FilterAttributeOptions::PROJECT:
                    $query->addOrderBy('project.id', $value);
                    break;
                case FilterAttributeOptions::CREATOR:
                    $query->addOrderBy('createdBy.id', $value);
                    break;
                case FilterAttributeOptions::REQUESTER:
                    $query->addOrderBy('requestedBy.id', $value);
                    break;
                case FilterAttributeOptions::COMPANY:
                    $query->addOrderBy('taskCompany.id', $value);
                    break;
                case FilterAttributeOptions::ASSIGNED:
                    $query->addOrderBy('assignedUser.username', $value);
                    break;
                case FilterAttributeOptions::TAG:
                    $query->addOrderBy('tags.id', $value);
                    break;
                case FilterAttributeOptions::FOLLOWER:
                    $query->addOrderBy('followers.id', $value);
                    break;
                case FilterAttributeOptions::CREATED:
                    $query->addOrderBy('task.createdAt', $value);
                    break;
                case FilterAttributeOptions::STARTED:
                    $query->addOrderBy('task.startedAt', $value);
                    break;
                case FilterAttributeOptions::DEADLINE:
                    $query->addOrderBy('task.deadline', $value);
                    break;
                case FilterAttributeOptions::CLOSED:
                    $query->addOrderBy('task.closedAt', $value);
                    break;
                case FilterAttributeOptions::IMPORTANT:
                    $query->addOrderBy('task.important', $value);
                    break;
                case FilterAttributeOptions::ARCHIVED:
                    $query->addOrderBy('project.is_active', $value);
                    break;
                default:
                    $query->addOrderBy('task.id', 'DESC');
            }
        }

        $query->where('task.id is not NULL');

        $paramArray = [];
        $paramNum = 0;
        if (null !== $searchFilter) {
            $query->andWhere('task.id LIKE :taskIdParam OR task.title LIKE :taskTitleParam');
            $paramArray['taskIdParam'] = '%' . $searchFilter . '%';
            $paramArray['taskTitleParam'] = '%' . $searchFilter . '%';
        }
        foreach ($isNullFilter as $value) {
            // check if query is allowed
            if (\in_array($value, VariableHelper::$allowedKeysInFilter, true)) {
                $query->andWhere($value . ' IS NULL');
            }
        }

        foreach ($inFilter as $key => $value) {
            // check if query is allowed
            if (\in_array($key, VariableHelper::$allowedKeysInFilter, true)) {
                $query->andWhere($key . ' IN (:parameters' . $paramNum . ')');
                $paramArray['parameters' . $paramNum] = $value;
                $paramNum++;
            }
        }

        foreach ($equalFilter as $key => $value) {
            if (\in_array($key, VariableHelper::$allowedKeysInFilter, true)) {
                $query->andWhere($key . ' = :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
            }
        }

        foreach ($notAndCurrentFilter as $filter) {
            if (\in_array($filter['not'], VariableHelper::$allowedKeysInFilter, true) && \in_array($filter['equal']['key'], VariableHelper::$allowedKeysInFilter, true)) {
                $query->andWhere($filter['not'] . ' IS NULL' . ' OR ' . $filter['equal']['key'] . ' IN (:parameters' . $paramNum . ')');
                $paramArray['parameters' . $paramNum] = $filter['equal']['value'];
            }
        }

        foreach ($dateFilter as $key => $value) {
            if (\in_array($key, VariableHelper::$allowedKeysInFilter, true)) {
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

        $addedParamNum = 0;
        if (\count($inFilterAddedParams) > 1) {
            foreach ($inFilterAddedParams as $key => $value) {
                $andString = 'taskAttribute.id = :attributeId' . $addedParamNum;
                $paramArray['attributeId' . $addedParamNum] = $key;
                $addedParamNum++;

                $helperCount = 1;
                $queryString = '';
                if (\count($value) > 1) {
                    foreach ($value as $val) {
                        // Create Query
                        if ($helperCount === 1) {
                            $queryString = 'taskData.value LIKE :parameters' . $paramNum;
                            $helperCount++;
                        } else {
                            $queryString = $queryString . ' OR ' . 'taskData.value LIKE :parameters' . $paramNum;
                        }
                        $paramArray['parameters' . $paramNum] = '%' . $val . '%';
                        $paramNum++;

                    }
                } else {
                    $queryString = 'taskData.value LIKE :parameters' . $paramNum;
                    $paramArray['parameters' . $paramNum] = '%' . $value[0] . '%';
                    $paramNum++;
                }
                $query->andWhere($andString . ' AND ' . '(' . $queryString . ')');

            }
        } else {
            foreach ($inFilterAddedParams as $key => $value) {
                $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
                $paramArray['attributeId' . $addedParamNum] = $key;
                $addedParamNum++;

                $helperCount = 1;
                $queryString = '';
                if (count($value) > 1) {
                    foreach ($value as $val) {
                        // Create Query
                        if ($helperCount === 1) {
                            $queryString = 'taskData.value LIKE :parameters' . $paramNum;
                            $helperCount++;
                        } else {
                            $queryString = $queryString . ' OR ' . 'taskData.value LIKE :parameters' . $paramNum;
                        }
                        $paramArray['parameters' . $paramNum] = '%' . $val . '%';
                        $paramNum++;

                    }
                } else {
                    $queryString = 'taskData.value LIKE :parameters' . $paramNum;
                    $paramArray['parameters' . $paramNum] = '%' . $value[0] . '%';
                    $paramNum++;
                }
                $query->andWhere($queryString);

            }
        }

        foreach ($equalFilterAddedParams as $key => $value) {
            $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
            $query->andWhere('taskData.boolValue = :parameters' . $paramNum);
            $paramArray['parameters' . $paramNum] = $value;
            $paramArray['attributeId' . $addedParamNum] = $key;

            $paramNum++;
            $addedParamNum++;
        }

        foreach ($dateFilterAddedParams as $key => $value) {
            if (isset($value['from']) && isset($value['to'])) {
                $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
                $query->andWhere($query->expr()->between('taskData.value', ':FROM' . $paramNum, ':TO' . $paramNum));
                $paramArray['FROM' . $paramNum] = $value['from'];
                $paramArray['TO' . $paramNum] = $value['to'];
                $paramArray['attributeId' . $addedParamNum] = $key;

                $paramNum++;
                $addedParamNum++;
            } elseif (isset($value['from']) && !isset($value['to'])) {
                $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
                $query->andWhere('taskData.value' . '>= :FROM' . $paramNum);
                $paramArray['FROM' . $paramNum] = $value['from'];
                $paramArray['attributeId' . $addedParamNum] = $key;

                $paramNum++;
                $addedParamNum++;
            } elseif (isset($value['to']) && !isset($value['from'])) {
                $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
                $query->andWhere('taskData.value' . '<= :TO' . $paramNum);
                $paramArray['TO' . $paramNum] = $value['to'];

                $paramNum++;
                $addedParamNum++;
            }
        }

        if (null !== $project) {
            $query->andWhere('project.id = :mainProjectId' . $addedParamNum);
            $paramArray['mainProjectId' . $addedParamNum] = $project;
        }

        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        if (999 !== $limit) {
            // Pagination
            if (1 < $page) {
                $query->setFirstResult($limit * $page - $limit);
            } else {
                $query->setFirstResult(0);
            }

            $query->setMaxResults($limit);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = $paginator->count();

            return [
                'count' => $count,
                'array' => $this->formatData($paginator)
            ];
        }

        // Return all entities
        return [
            'array' => $this->formatData($query->getQuery()->getArrayResult(), true)
        ];
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
    public function getAllUsersTasks(int $page, int $userId, int $companyId, $dividedProjects, array $options): array
    {
        $paramArray = [];

        $inFilter = $options['inFilter'];
        $equalFilter = $options['equalFilter'];
        $dateFilter = $options['dateFilter'];
        $isNullFilter = $options['isNullFilter'];
        $searchFilter = $options['searchFilter'];
        $notAndCurrentFilter = $options['notAndCurrentFilter'];
        $inFilterAddedParams = $options['inFilterAddedParams'];
        $equalFilterAddedParams = $options['equalFilterAddedParams'];
        $dateFilterAddedParams = $options['dateFilterAddedParams'];
        $order = $options['order'];
        $limit = $options['limit'];
        $project = $options['project'];

        $query = $this->createQueryBuilder('task')
            ->select('task')
            ->addSelect('taskData')
            ->addSelect('taskAttribute')
            ->addSelect('project')
            ->addSelect('projectCreator')
            ->addSelect('createdBy')
            ->addSelect('creatorDetailData')
            ->addSelect('company')
            ->addSelect('requestedBy')
            ->addSelect('requesterDetailData')
            ->addSelect('taskHasAttachments')
            ->addSelect('taskGlobalStatus')
            ->addSelect('assignedUser')
            ->addSelect('assigneeDetailData')
            ->addSelect('tags')
            ->addSelect('taskCompany')
            ->addSelect('followers')
            ->addSelect('followersDetailData')
            ->addSelect('invoiceableItems')
            ->addSelect('unit')
            ->addSelect('taskHasAssignedUsers')
            ->addSelect('assignedUserStatus')
            ->addSelect('assignedUser')
            ->leftJoin('task.taskData', 'taskData')
            ->leftJoin('taskData.taskAttribute', 'taskAttribute')
            ->leftJoin('task.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
            ->leftJoin('task.createdBy', 'createdBy')
            ->leftJoin('createdBy.detailData', 'creatorDetailData')
            ->leftJoin('createdBy.company', 'company')
            ->leftJoin('task.requestedBy', 'requestedBy')
            ->leftJoin('requestedBy.detailData', 'requesterDetailData')
            ->leftJoin('task.taskHasAssignedUsers', 'taskHasAssignedUsers')
            ->leftJoin('task.taskHasAttachments', 'taskHasAttachments')
            ->leftJoin('taskHasAssignedUsers.status', 'assignedUserStatus')
            ->leftJoin('taskHasAssignedUsers.user', 'assignedUser')
            ->leftJoin('assignedUser.detailData', 'assigneeDetailData')
            ->leftJoin('task.tags', 'tags')
            ->leftJoin('task.company', 'taskCompany')
            ->leftJoin('task.status', 'taskGlobalStatus')
            ->leftJoin('task.followers', 'followers')
            ->leftJoin('followers.detailData', 'followersDetailData')
            ->leftJoin('task.invoiceableItems', 'invoiceableItems')
            ->leftJoin('invoiceableItems.unit', 'unit')
            ->distinct();

        foreach ($order as $key => $value) {
            switch ($key) {
                case FilterAttributeOptions::ID:
                    $query->addOrderBy('task.id', $value);
                    break;
                case FilterAttributeOptions::TITLE:
                    $query->addOrderBy('task.title', $value);
                    break;
                case FilterAttributeOptions::STATUS:
                    $query->addOrderBy('taskGlobalStatus.id', $value);
                    break;
                case FilterAttributeOptions::PROJECT:
                    $query->addOrderBy('project.id', $value);
                    break;
                case FilterAttributeOptions::CREATOR:
                    $query->addOrderBy('createdBy.id', $value);
                    break;
                case FilterAttributeOptions::REQUESTER:
                    $query->addOrderBy('requestedBy.id', $value);
                    break;
                case FilterAttributeOptions::COMPANY:
                    $query->addOrderBy('taskCompany.id', $value);
                    break;
                case FilterAttributeOptions::ASSIGNED:
                    $query->addOrderBy('assignedUser.id', $value);
                    break;
                case FilterAttributeOptions::TAG:
                    $query->addOrderBy('tags.id', $value);
                    break;
                case FilterAttributeOptions::FOLLOWER:
                    $query->addOrderBy('followers.id', $value);
                    break;
                case FilterAttributeOptions::CREATED:
                    $query->addOrderBy('task.createdAt', $value);
                    break;
                case FilterAttributeOptions::STARTED:
                    $query->addOrderBy('task.startedAt', $value);
                    break;
                case FilterAttributeOptions::DEADLINE:
                    $query->addOrderBy('task.deadline', $value);
                    break;
                case FilterAttributeOptions::CLOSED:
                    $query->addOrderBy('task.closedAt', $value);
                    break;
                case FilterAttributeOptions::IMPORTANT:
                    $query->addOrderBy('task.important', $value);
                    break;
                case FilterAttributeOptions::ARCHIVED:
                    $query->addOrderBy('project.is_active', $value);
                    break;
                default:
                    $query->addOrderBy('task.id', 'DESC');
            }
        }

        // Check and apply User's project ACL
        if (array_key_exists('VIEW_ALL_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $allTasksInProject */
            $allTasksInProject = $dividedProjects['VIEW_ALL_TASKS_IN_PROJECT'];
        } else {
            $allTasksInProject = [1];
        }

        if (array_key_exists('VIEW_COMPANY_TASKS_IN_PROJECT', $dividedProjects)) {
            /** @var array $companyTasksInProject */
            $companyTasksInProject = $dividedProjects['VIEW_COMPANY_TASKS_IN_PROJECT'];
        } else {
            $companyTasksInProject = [1];
        }

        if (array_key_exists('VIEW_OWN_TASKS', $dividedProjects)) {
            /** @var array $companyTasksInProject */
            $ownTasksInProject = $dividedProjects['VIEW_OWN_TASKS'];
        } else {
            $ownTasksInProject = [1];
        }

        // Select only in Allowed users projects
        $query->andWhere($query->expr()->orX(
            $query->expr()->in('project.id', $allTasksInProject),
            $query->expr()->andX(
                $query->expr()->in('project.id', $companyTasksInProject),
                $query->expr()->eq('taskCompany.id', $companyId)
            ),
            $query->expr()->andX(
                $query->expr()->in('project.id', $ownTasksInProject),
                $query->expr()->orX(
                    $query->expr()->eq('requestedBy.id', $userId),
                    $query->expr()->eq('createdBy.id', $userId)
                )
            )
        ));

        //Check and apply filters
        $paramNum = 0;
        if (null !== $searchFilter) {
            $query->andWhere('task.id LIKE :taskIdParam OR task.title LIKE :taskTitleParam');
            $paramArray['taskIdParam'] = '%' . $searchFilter . '%';
            $paramArray['taskTitleParam'] = '%' . $searchFilter . '%';
        }

        foreach ($isNullFilter as $value) {
            // check if query is allowed
            if (\in_array($value, VariableHelper::$allowedKeysInFilter, true)) {
                $query->andWhere($value . ' IS NULL');
            }
        }

        foreach ($inFilter as $key => $value) {
            // check if query is allowed
            if (\in_array($key, VariableHelper::$allowedKeysInFilter, true)) {
                $query->andWhere($key . ' IN (:parameters' . $paramNum . ')');
                $paramArray['parameters' . $paramNum] = $value;

                $paramNum++;
            }
        }

        foreach ($equalFilter as $key => $value) {
            if (\in_array($key, VariableHelper::$allowedKeysInFilter, true)) {
                $query->andWhere($key . ' = :parameter' . $paramNum);
                $paramArray['parameter' . $paramNum] = $value;

                $paramNum++;
            }
        }

        foreach ($notAndCurrentFilter as $filter) {
            if (\in_array($filter['not'], VariableHelper::$allowedKeysInFilter, true) && \in_array($filter['equal']['key'], VariableHelper::$allowedKeysInFilter, true)) {
                $query->andWhere($filter['not'] . ' IS NULL' . ' OR ' . $filter['equal']['key'] . ' IN (:parameters' . $paramNum . ')');
                $paramArray['parameters' . $paramNum] = $filter['equal']['value'];
            }
        }

        foreach ($dateFilter as $key => $value) {
            if (\in_array($key, VariableHelper::$allowedKeysInFilter, true)) {
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

        $addedParamNum = 0;
        if (\count($inFilterAddedParams) > 1) {
            foreach ($inFilterAddedParams as $key => $value) {
                $andString = 'taskAttribute.id = :attributeId' . $addedParamNum;
                $paramArray['attributeId' . $addedParamNum] = $key;
                $addedParamNum++;

                $helperCount = 1;
                $queryString = '';
                if (count($value) > 1) {
                    foreach ($value as $val) {
                        // Create Query
                        if ($helperCount === 1) {
                            $queryString = 'taskData.value LIKE :parameters' . $paramNum;
                            $helperCount++;
                        } else {
                            $queryString = $queryString . ' OR ' . 'taskData.value LIKE :parameters' . $paramNum;
                        }
                        $paramArray['parameters' . $paramNum] = '%' . $val . '%';
                        $paramNum++;

                    }
                } else {
                    $queryString = 'taskData.value LIKE :parameters' . $paramNum;
                    $paramArray['parameters' . $paramNum] = '%' . $value[0] . '%';
                    $paramNum++;
                }
                $query->andWhere($andString . ' AND ' . '(' . $queryString . ')');

            }
        } else {
            foreach ($inFilterAddedParams as $key => $value) {
                $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
                $paramArray['attributeId' . $addedParamNum] = $key;
                $addedParamNum++;

                $helperCount = 1;
                $queryString = '';
                if (\count($value) > 1) {
                    foreach ($value as $val) {
                        // Create Query
                        if ($helperCount === 1) {
                            $queryString = 'taskData.value LIKE :parameters' . $paramNum;
                            $helperCount++;
                        } else {
                            $queryString = $queryString . ' OR ' . 'taskData.value LIKE :parameters' . $paramNum;
                        }
                        $paramArray['parameters' . $paramNum] = '%' . $val . '%';
                        $paramNum++;

                    }
                } else {
                    $queryString = 'taskData.value LIKE :parameters' . $paramNum;
                    $paramArray['parameters' . $paramNum] = '%' . $value[0] . '%';
                    $paramNum++;
                }
                $query->andWhere($queryString);

            }
        }

        foreach ($equalFilterAddedParams as $key => $value) {
            $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
            $query->andWhere('taskData.boolValue = :parameters' . $paramNum);
            $paramArray['parameters' . $paramNum] = $value;
            $paramArray['attributeId' . $addedParamNum] = $key;

            $paramNum++;
            $addedParamNum++;
        }

        foreach ($dateFilterAddedParams as $key => $value) {
            if (isset($value['from']) && isset($value['to'])) {
                $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
                $query->andWhere($query->expr()->between('taskData.value', ':FROM' . $paramNum, ':TO' . $paramNum));
                $paramArray['FROM' . $paramNum] = $value['from'];
                $paramArray['TO' . $paramNum] = $value['to'];
                $paramArray['attributeId' . $addedParamNum] = $key;

                $paramNum++;
                $addedParamNum++;
            } elseif (isset($value['from']) && !isset($value['to'])) {
                $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
                $query->andWhere('taskData.value' . '>= :FROM' . $paramNum);
                $paramArray['FROM' . $paramNum] = $value['from'];
                $paramArray['attributeId' . $addedParamNum] = $key;

                $paramNum++;
                $addedParamNum++;
            } elseif (isset($value['to']) && !isset($value['from'])) {
                $query->andWhere('taskAttribute.id = :attributeId' . $addedParamNum);
                $query->andWhere('taskData.value' . '<= :TO' . $paramNum);
                $paramArray['TO' . $paramNum] = $value['to'];

                $paramNum++;
                $addedParamNum++;
            }
        }

        if (null !== $project) {
            $query->andWhere('project.id = :mainProjectId' . $addedParamNum);
            $paramArray['mainProjectId' . $addedParamNum] = $project;
        }

        if (!empty($paramArray)) {
            $query->setParameters($paramArray);
        }

        if (999 !== $limit) {
            // Pagination
            if (1 < $page) {
                $query->setFirstResult($limit * $page - $limit);
            } else {
                $query->setFirstResult(0);
            }

            $query->setMaxResults($limit);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = $paginator->count();

            return [
                'count' => $count,
                'array' => $this->formatData($paginator)
            ];
        }

        // Return all entities
        return [
            'array' => $this->formatData($query->getQuery()->getArrayResult(), true)
        ];
    }

    /**
     * @param int $taskId
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getTask(int $taskId): array
    {
        $query = $this->createQueryBuilder('task')
            ->select('task')
            ->leftJoin('task.taskData', 'taskData')
            ->leftJoin('taskData.taskAttribute', 'taskAttribute')
            ->leftJoin('task.project', 'project')
            ->leftJoin('project.createdBy', 'projectCreator')
            ->leftJoin('task.createdBy', 'createdBy')
            ->leftJoin('createdBy.detailData', 'creatorDetailData')
            ->leftJoin('createdBy.company', 'company')
            ->leftJoin('task.requestedBy', 'requestedBy')
            ->leftJoin('requestedBy.detailData', 'requesterDetailData')
            ->leftJoin('task.taskHasAssignedUsers', 'taskHasAssignedUsers')
            ->leftJoin('task.taskHasAttachments', 'taskHasAttachments')
            ->leftJoin('taskHasAssignedUsers.status', 'assignedUserStatus')
            ->leftJoin('taskHasAssignedUsers.user', 'assignedUser')
            ->leftJoin('assignedUser.detailData', 'assigneeDetailData')
            ->leftJoin('task.tags', 'tags')
            ->leftJoin('task.company', 'taskCompany')
            ->leftJoin('task.status', 'taskGlobalStatus')
            ->leftJoin('task.followers', 'followers')
            ->leftJoin('followers.detailData', 'followersDetailData')
            ->leftJoin('task.invoiceableItems', 'invoiceableItems')
            ->leftJoin('invoiceableItems.unit', 'unit')
            ->where('task.id = :taskId')
            ->setParameter('taskId', $taskId)
            ->getQuery();

        return $this->processData($query->getSingleResult(), true);
    }

    /**
     * Return user's allowed tasks ID based on his ACL
     *
     * @param array $dividedTasks
     * @param int $userId
     * @param int $companyId
     * @return array
     */
    public function getUsersTasksId(array $dividedTasks, int $userId, int $companyId): array
    {
        $allTasksInProject = $dividedTasks['VIEW_ALL_TASKS_IN_PROJECT'];
        $companyTasksInProject = $dividedTasks['VIEW_COMPANY_TASKS_IN_PROJECT'];
        $ownTasksInProject = $dividedTasks['VIEW_OWN_TASKS'];

        $query = $this->createQueryBuilder('task')
            ->select('task.id')
            ->leftJoin('task.project', 'project')
            ->leftJoin('task.company', 'taskCompany')
            ->leftJoin('task.requestedBy', 'requestedBy')
            ->leftJoin('task.createdBy', 'createdBy');

        $query->where($query->expr()->orX(
            $query->expr()->in('project.id', $allTasksInProject),
            $query->expr()->andX(
                $query->expr()->in('project.id', $companyTasksInProject),
                $query->expr()->eq('taskCompany.id', $companyId)
            ),
            $query->expr()->andX(
                $query->expr()->in('project.id', $ownTasksInProject),
                $query->expr()->orX(
                    $query->expr()->eq('requestedBy.id', $userId),
                    $query->expr()->eq('createdBy.id', $userId)
                )
            )
        ));

        return $this->formatIdData($query->getQuery()->getArrayResult());
    }

    /**
     * @param Project $project
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNumberOfTasksFromProject(Project $project): int
    {
        $query = $this->createQueryBuilder('task')
            ->select('COUNT(task)')
            ->where('task.project = :project')
            ->setParameter('project', $project)
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param array $data
     * @return array
     */
    private function formatIdData(array $data): array
    {
        $response = [];
        foreach ($data as $datum) {
            $response[] = $datum['id'];
        }

        return $response;
    }

    /**
     * @param $paginatorData
     * @param bool $array
     * @return array
     */
    private function formatData($paginatorData, $array = false): array
    {
        $response = [];
        foreach ($paginatorData as $data) {
            if ($array) {
                $response[] = $this->processArrayData($data);
            } else {
                $response[] = $this->processData($data);
            }
        }

        return $response;
    }

    /**
     * @param Task $data
     * @param bool $single
     * @return array
     */
    private function processData(Task $data, $single = false): array
    {
        $taskData = $data->getTaskData();
        $taskDataArray = [];
        if (\count($taskData) > 0) {
            /** @var TaskData $item */
            foreach ($taskData as $item) {
                $taskDataArray[] = [
                    'id' => $item->getId(),
                    'value' => $item->getValue(),
                    'boolValue' => $item->getBoolValue(),
                    'dateValue' => $item->getDateValue(),
                    'taskAttribute' => [
                        'id' => $item->getTaskAttribute()->getId(),
                        'title' => $item->getTaskAttribute()->getTitle(),
                    ]
                ];
            }
        }
        $followers = $data->getFollowers();
        $followersArray = [];
        if (\count($followers) > 0) {
            /** @var User $item */
            foreach ($followers as $item) {
                $userDetailData = $item->getDetailData();
                $userName = null;
                $userSurname = null;
                if ($userDetailData instanceof UserData) {
                    $userName = $userDetailData->getName();
                    $userSurname = $userDetailData->getSurname();
                }
                $followersArray[] = [
                    'id' => $item->getId(),
                    'username' => $item->getUsername(),
                    'email' => $item->getEmail(),
                    'name' => $userName,
                    'surname' => $userSurname,
                ];
            }
        }
        $tags = $data->getTags();
        $tagsArray = [];
        if (\count($tags) > 0) {
            /** @var Tag $item */
            foreach ($tags as $item) {
                $tagsArray[] = [
                    'id' => $item->getId(),
                    'title' => $item->getTitle(),
                    'color' => $item->getColor()
                ];
            }
        }
        $taskHasAssignedUsers = $data->getTaskHasAssignedUsers();
        $taskHasAssignedUsersArray = [];
        if (\count($taskHasAssignedUsers) > 0) {
            $processedUsers = [];
            /** @var TaskHasAssignedUser $item */
            foreach ($taskHasAssignedUsers as $item) {
                $processedUsersDates[$item->getUser()->getId()] = null;
                if (!\in_array($item->getUser()->getId(), $processedUsers, true)) {
                    $processedUsersDates[$item->getUser()->getId()] = $item->getCreatedAt();
                    $userDetailData = $item->getUser()->getDetailData();
                    $userName = null;
                    $userSurname = null;
                    if ($userDetailData) {
                        $userName = $userDetailData->getName();
                        $userSurname = $userDetailData->getSurname();
                    }
                    $taskHasAssignedUsersArray[$item->getUser()->getId()] = [
                        'id' => $item->getId(),
                        'status_date' => $item->getStatusDate(),
                        'time_spent' => $item->getTimeSpent(),
                        'createdAt' => $item->getCreatedAt(),
                        'updatedAt' => $item->getUpdatedAt(),
                        'status' => [
                            'id' => $item->getStatus()->getId(),
                            'title' => $item->getStatus()->getTitle(),
                            'color' => $item->getStatus()->getColor(),
                        ],
                        'user' => [
                            'id' => $item->getUser()->getId(),
                            'username' => $item->getUser()->getUsername(),
                            'email' => $item->getUser()->getEmail(),
                            'name' => $userName,
                            'surname' => $userSurname,
                        ]
                    ];
                    $processedUsers [] = $item->getUser()->getId();
                } else {
                    if ($processedUsersDates[$item->getUser()->getId()] < $item->getCreatedAt()) {
                        $processedUsersDates[$item->getUser()->getId()] = $item->getCreatedAt();
                        $userDetailData = $item->getUser()->getDetailData();
                        $userName = null;
                        $userSurname = null;
                        if ($userDetailData) {
                            $userName = $userDetailData->getName();
                            $userSurname = $userDetailData->getSurname();
                        }
                        $taskHasAssignedUsersArray[$item->getUser()->getId()] = [
                            'id' => $item->getId(),
                            'status_date' => $item->getStatusDate(),
                            'time_spent' => $item->getTimeSpent(),
                            'createdAt' => $item->getCreatedAt(),
                            'updatedAt' => $item->getUpdatedAt(),
                            'status' => [
                                'id' => $item->getStatus()->getId(),
                                'title' => $item->getStatus()->getTitle(),
                                'color' => $item->getStatus()->getColor(),
                            ],
                            'user' => [
                                'id' => $item->getUser()->getId(),
                                'username' => $item->getUser()->getUsername(),
                                'email' => $item->getUser()->getEmail(),
                                'name' => $userName,
                                'surname' => $userSurname,
                            ]
                        ];
                    }
                }
            }
        }

        $taskHasAttachments = $data->getTaskHasAttachments();
        $taskHasAttachmentsArray = [];
        if (\count($taskHasAttachments) > 0) {
            /** @var TaskHasAttachment $item */
            foreach ($taskHasAttachments as $item) {
                $fileEntity = $this->getEntityManager()->getRepository('APICoreBundle:File')->findOneBy([
                    'slug' => $item->getSlug()
                ]);
                if ($fileEntity instanceof File) {
                    $name = $fileEntity->getName();
                } else {
                    $name = 'not available in a DB';
                }
                $taskHasAttachmentsArray[] = [
                    'id' => $item->getId(),
                    'slug' => $item->getSlug(),
                    'name' => $name
                ];
            }
        }

        $invoiceableItems = $data->getInvoiceableItems();
        $invoiceableItemsArray = [];
        if (\count($invoiceableItems) > 0) {
            /** @var InvoiceableItem $item */
            foreach ($invoiceableItems as $item) {
                $invoiceableItemsArray[] = [
                    'id' => $item->getId(),
                    'title' => $item->getTitle(),
                    'amount' => $item->getAmount(),
                    'unit_price' => $item->getUnitPrice(),
                    'unit' => [
                        'id' => $item->getUnit()->getId(),
                        'title' => $item->getUnit()->getTitle(),
                        'shortcut' => $item->getUnit()->getShortcut(),
                    ]
                ];
            }
        };
        $project = $data->getProject();
        $projectArray = [];
        if ($project instanceof Project) {
            $projectArray = [
                'id' => $data->getProject()->getId(),
                'title' => $data->getProject()->getTitle(),
                'is_active' => $data->getProject()->getIsActive()
            ];
        }
        $company = $data->getCompany();
        $companyArray = [];
        if ($company instanceof Company) {
            $companyArray = [
                'id' => $data->getCompany()->getId(),
                'title' => $data->getCompany()->getTitle()
            ];
        }

        $status = $data->getStatus();
        $statusArray = [];
        if ($status instanceof Status) {
            $statusArray = [
                'id' => $data->getStatus()->getId(),
                'title' => $data->getStatus()->getTitle(),
                'color' => $data->getStatus()->getColor(),
            ];
        }

        $userCreatorDetailData = $data->getCreatedBy()->getDetailData();
        $userCreatorName = null;
        $userCreatorSurname = null;
        if ($userCreatorDetailData instanceof UserData) {
            $userCreatorName = $userCreatorDetailData->getName();
            $userCreatorSurname = $userCreatorDetailData->getSurname();
        }

        $userRequesterDetailData = $data->getRequestedBy()->getDetailData();
        $userRequesterName = null;
        $userRequesterSurname = null;
        if ($userRequesterDetailData instanceof UserData) {
            $userRequesterName = $userRequesterDetailData->getName();
            $userRequesterSurname = $userRequesterDetailData->getSurname();
        }

        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'description' => $data->getDescription(),
            'deadline' => $data->getDeadline(),
            'startedAt' => $data->getStartedAt(),
            'closedAt' => $data->getClosedAt(),
            'important' => $data->getImportant(),
            'work' => $data->getWork(),
            'work_time' => $data->getWorkTime(),
            'work_type' => $data->getWorkType(),
            'createdAt' => $data->getCreatedAt(),
            'updatedAt' => $data->getUpdatedAt(),
            'statusChange' => $data->getStatusChange(),
            'createdBy' => [
                'id' => $data->getCreatedBy()->getId(),
                'username' => $data->getCreatedBy()->getUsername(),
                'email' => $data->getCreatedBy()->getEmail(),
                'name' => $userCreatorName,
                'surname' => $userCreatorSurname,
            ],
            'requestedBy' => [
                'id' => $data->getRequestedBy()->getId(),
                'username' => $data->getRequestedBy()->getUsername(),
                'email' => $data->getRequestedBy()->getEmail(),
                'name' => $userRequesterName,
                'surname' => $userRequesterSurname,
            ],
            'project' => $projectArray,
            'company' => $companyArray,
            'status' => $statusArray,
            'taskData' => $taskDataArray,
            'followers' => $followersArray,
            'tags' => $tagsArray,
            'taskHasAssignedUsers' => $taskHasAssignedUsersArray,
            'taskHasAttachments' => $taskHasAttachmentsArray,
            'invoiceableItems' => $invoiceableItemsArray
        ];

        return $response;
    }


    /**
     * @param  array $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $taskData = $data['taskData'];
        $taskDataArray = [];
        if (\count($taskData) > 0) {
            foreach ($taskData as $item) {
                $taskDataArray[] = [
                    'id' => $item['id'],
                    'value' => $item['value'],
                    'boolValue' => $item['boolValue'],
                    'dateValue' => $item['dateValue'],
                    'taskAttribute' => [
                        'id' => $item['taskAttribute']['id'],
                        'title' => $item['taskAttribute']['title'],
                    ]
                ];
            }
        }
        $followers = $data['followers'];
        $followersArray = [];
        if (\count($followers) > 0) {
            foreach ($followers as $item) {
                $userDetailData = $item['detailData'];
                $userName = null;
                $userSurname = null;
                if ($userDetailData) {
                    $userName = $userDetailData['name'];
                    $userSurname = $userDetailData['surname'];
                }
                $followersArray[] = [
                    'id' => $item['id'],
                    'username' => $item['username'],
                    'email' => $item['email'],
                    'name' => $userName,
                    'surname' => $userSurname,
                ];
            }
        }
        $tags = $data['tags'];
        $tagsArray = [];
        if (\count($tags) > 0) {
            /** @var Tag $item */
            foreach ($tags as $item) {
                $tagsArray[] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'color' => $item['color']
                ];
            }
        }

        $taskHasAssignedUsers = $data['taskHasAssignedUsers'];
        $taskHasAssignedUsersArray = [];
        if (\count($taskHasAssignedUsers) > 0) {
            $processedUsers = [];
            foreach ($taskHasAssignedUsers as $item) {
                $processedUsersDates[$item['user']['id']] = null;
                if (!\in_array($item['user']['id'], $processedUsers, true)) {
                    $processedUsersDates[$item['user']['id']] = $item['createdAt'];
                    $userDetailData = $item['user']['detailData'];
                    $userName = null;
                    $userSurname = null;
                    if ($userDetailData) {
                        $userName = $userDetailData['name'];
                        $userSurname = $userDetailData['surname'];
                    }
                    $taskHasAssignedUsersArray[$item['user']['id']] = [
                        'id' => $item['id'],
                        'status_date' => isset($item['status_date']) ? date_timestamp_get($item['status_date']) : null,
                        'time_spent' => $item['time_spent'],
                        'createdAt' => isset($item['createdAt']) ? date_timestamp_get($item['createdAt']) : null,
                        'updatedAt' => isset($item['updatedAt']) ? date_timestamp_get($item['updatedAt']) : null,
                        'status' => [
                            'id' => $item['status']['id'],
                            'title' => $item['status']['title'],
                            'color' => $item['status']['color']
                        ],
                        'user' => [
                            'id' => $item['user']['id'],
                            'username' => $item['user']['username'],
                            'email' => $item['user']['email'],
                            'name' => $userName,
                            'surname' => $userSurname,
                        ]
                    ];
                    $processedUsers [] = $item['user']['id'];
                } else {
                    if ($processedUsersDates[$item['user']['id']] < $item['createdAt']) {
                        $processedUsersDates[$item['user']['id']] = $item['createdAt'];
                        $userDetailData = $item['user']['detailData'];
                        $userName = null;
                        $userSurname = null;
                        if ($userDetailData) {
                            $userName = $userDetailData['name'];
                            $userSurname = $userDetailData['surname'];
                        }
                        $taskHasAssignedUsersArray[$item['user']['id']] = [
                            'id' => $item['id'],
                            'status_date' => isset($item['status_date']) ? date_timestamp_get($item['status_date']) : null,
                            'time_spent' => $item['time_spent'],
                            'createdAt' => isset($item['createdAt']) ? date_timestamp_get($item['createdAt']) : null,
                            'updatedAt' => isset($item['updatedAt']) ? date_timestamp_get($item['updatedAt']) : null,
                            'status' => [
                                'id' => $item['status']['id'],
                                'title' => $item['status']['title'],
                                'color' => $item['status']['color']
                            ],
                            'user' => [
                                'id' => $item['user']['id'],
                                'username' => $item['user']['username'],
                                'email' => $item['user']['email'],
                                'name' => $userName,
                                'surname' => $userSurname,
                            ]
                        ];
                    }
                }
            }
        }

        $taskHasAttachments = $data['taskHasAttachments'];
        $taskHasAttachmentsArray = [];
        if (\count($taskHasAttachments) > 0) {
            foreach ($taskHasAttachments as $item) {
                $fileEntity = $this->getEntityManager()->getRepository('APICoreBundle:File')->findOneBy([
                    'slug' => $item['slug']
                ]);
                if ($fileEntity instanceof File) {
                    $name = $fileEntity->getName();
                } else {
                    $name = 'not available in a DB';
                }
                $taskHasAttachmentsArray[] = [
                    'id' => $item['id'],
                    'slug' => $item['slug'],
                    'name' => $name
                ];
            }
        }
        $invoiceableItems = $data['invoiceableItems'];
        $invoiceableItemsArray = [];
        if (\count($invoiceableItems) > 0) {
            foreach ($invoiceableItems as $item) {
                $invoiceableItemsArray[] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'amount' => (int)$item['amount'],
                    'unit_price' => (int)$item['unit_price'],
                    'unit' => [
                        'id' => $item['unit']['id'],
                        'title' => $item['unit']['title'],
                        'shortcut' => $item['unit']['shortcut'],
                    ]
                ];
            }
        };
        $project = $data['project'];
        $projectArray = [];
        if ($project) {
            $projectArray = [
                'id' => $data['project']['id'],
                'title' => $data['project']['title'],
                'is_active' => $data['project']['is_active']
            ];
        }
        $company = $data['company'];
        $companyArray = [];
        if ($company) {
            $companyArray = [
                'id' => $data['company']['id'],
                'title' => $data['company']['title']
            ];
        }
        $status = $data['taskGlobalStatus'];
        $statusArray = [];
        if ($status) {
            $statusArray = [
                'id' => $data['taskGlobalStatus']['id'],
                'title' => $data['taskGlobalStatus']['title'],
                'color' => $data['taskGlobalStatus']['color']
            ];
        }

        $userCreatorDetailData = $data['createdBy']['detailData'];
        $userCreatorName = null;
        $userCreatorSurname = null;
        if ($userCreatorDetailData) {
            $userCreatorName = $userCreatorDetailData['name'];
            $userCreatorSurname = $userCreatorDetailData['surname'];
        }

        $userRequesterDetailData = $data['requestedBy']['detailData'];
        $userRequesterName = null;
        $userRequesterSurname = null;
        if ($userRequesterDetailData) {
            $userRequesterName = $userRequesterDetailData['name'];
            $userRequesterSurname = $userRequesterDetailData['surname'];
        }

        $response = [
            'id' => $data['id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'deadline' => isset($data['deadline']) ? date_timestamp_get($data['deadline']) : null,
            'startedAt' => isset($data['startedAt']) ? date_timestamp_get($data['startedAt']) : null,
            'closedAt' => isset($data['closedAt']) ? date_timestamp_get($data['closedAt']) : null,
            'important' => $data['important'],
            'work' => $data['work'],
            'work_time' => isset($data['work_time']) ? (int)$data['work_time'] : null,
            'work_type' => $data['work_type'],
            'createdAt' => isset($data['createdAt']) ? date_timestamp_get($data['createdAt']) : null,
            'updatedAt' => isset($data['updatedAt']) ? date_timestamp_get($data['updatedAt']) : null,
            'statusChange' => isset($data['statusChange']) ? date_timestamp_get($data['statusChange']) : null,
            'createdBy' => [
                'id' => $data['createdBy']['id'],
                'username' => $data['createdBy']['username'],
                'email' => $data['createdBy']['email'],
                'name' => $userCreatorName,
                'surname' => $userCreatorSurname,
            ],
            'requestedBy' => [
                'id' => $data['requestedBy']['id'],
                'username' => $data['requestedBy']['username'],
                'email' => $data['requestedBy']['email'],
                'name' => $userRequesterName,
                'surname' => $userRequesterSurname,
            ],
            'project' => $projectArray,
            'company' => $companyArray,
            'status' => $statusArray,
            'taskData' => $taskDataArray,
            'followers' => $followersArray,
            'tags' => $tagsArray,
            'taskHasAssignedUsers' => $taskHasAssignedUsersArray,
            'taskHasAttachments' => $taskHasAttachmentsArray,
            'invoiceableItems' => $invoiceableItemsArray
        ];

        return $response;
    }
}
