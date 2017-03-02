<?php

namespace API\TaskBundle\Repository;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Comment;
use API\TaskBundle\Entity\InvoiceableItem;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskData;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\TaskHasAttachment;
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
            ->addSelect('thau')
            ->addSelect('status')
            ->addSelect('assignedUser')
            ->addSelect('tags')
            ->addSelect('taskCompany')
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
            ->orderBy('task.id', 'ASC')
            ->distinct();

        if (array_key_exists('followers.id', $inFilter) || array_key_exists('followers.id', $equalFilter)) {
            $query->innerJoin('task.followers', 'followers');
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
                $query->andWhere($filter['not'] . ' IS NULL' . ' OR ' . $filter['equal']['key'] . ' = :parameter' . $paramNum);
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
            ->addSelect('thau')
            ->addSelect('status')
            ->addSelect('assignedUser')
            ->addSelect('tags')
            ->addSelect('taskCompany')
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
            ->orderBy('task.id', 'ASC')
            ->distinct();

        if (array_key_exists('followers.id', $inFilter) || array_key_exists('followers.id', $equalFilter)) {
            $query->innerJoin('task.followers', 'followers');
        }

        // Check and apply User's project ACL
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

        if (array_key_exists('VIEW_OWN_TASKS', $dividedProjects)) {
            /** @var array $companyTasksInProject */
            $ownTasksInProject = $dividedProjects['VIEW_OWN_TASKS'];
        } else {
            $ownTasksInProject = [];
        }

        $query->where('project.id IN (:allTasksInProject) ')
            ->orWhere('project.id IN (:companyTasksInProject) AND taskCompany.id = :loggedUserCompanyId')
            ->orWhere('project.id IN (:ownTasksInProject) AND (requestedBy.id = :loggedUserId OR createdBy.id = :loggedUserId)');
        $paramArray['allTasksInProject'] = $allTasksInProject;
        $paramArray['companyTasksInProject'] = $companyTasksInProject;
        $paramArray['loggedUserCompanyId'] = $companyId;
        $paramArray['ownTasksInProject'] = $ownTasksInProject;
        $paramArray['loggedUserId'] = $userId;

        //Check and apply filters
        $paramNum = 0;
        if (null !== $searchFilter) {
            $query->andWhere('task.id LIKE :taskIdParam OR task.title LIKE :taskTitleParam');
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
                $query->andWhere($filter['not'] . ' IS NULL' . ' OR ' . $filter['equal']['key'] . ' = :parameter' . $paramNum);
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
     * @param int $taskId
     * @return array
     */
    public function getTask(int $taskId)
    {
        $query = $this->createQueryBuilder('task')
            ->select('task, taskData, taskAttribute, project, createdBy, company, requestedBy, thau, status, assignedUser, creatorDetailData, requesterDetailData, tags, taskCompany, attachments, invoiceableItems, unit')
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
            ->leftJoin('task.taskHasAttachments', 'attachments')
            ->leftJoin('task.invoiceableItems', 'invoiceableItems')
            ->leftJoin('invoiceableItems.unit', 'unit')
            ->where('task.id = :taskId')
            ->setParameter('taskId', $taskId)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * @param int $taskId
     * @return array
     */
    public function getAllTasksTags(int $taskId): array
    {
        $query = $this->createQueryBuilder('t')
            ->select('t,tag')
            ->where('t.id = :taskId')
            ->leftJoin('t.tags', 'tag')
            ->setParameter('taskId', $taskId);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param int $taskId
     * @return array
     */
    public function getAllTaskFollowers(int $taskId): array
    {
        $query = $this->createQueryBuilder('t')
            ->select('t,follower')
            ->where('t.id = :taskId')
            ->leftJoin('t.followers', 'follower')
            ->setParameter('taskId', $taskId);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $paginatorData
     * @return array
     */
    private function formatData($paginatorData):array
    {
        $response = [];
        /** @var Task $data */
        foreach ($paginatorData as $data) {
            $response[] = $this->processData($data);
        }

        return $response;
    }

    /**
     * @param Task $data
     * @return array
     */
    private function processData(Task $data):array
    {
        $taskData = $data->getTaskData();
        $taskDataArray = [];
        if (count($taskData) > 0) {
            /** @var TaskData $item */
            foreach ($taskData as $item) {
                $taskDataArray[] = [
                    'id' => $item->getId(),
                    'value' => $item->getValue(),
                    'taskAttribute' => [
                        'id' => $item->getTaskAttribute()->getId(),
                        'title' => $item->getTaskAttribute()->getTitle(),
                    ]
                ];
            }
        }
        $followers = $data->getFollowers();
        $followersArray = [];
        if (count($followers) > 0) {
            /** @var User $item */
            foreach ($followers as $item) {
                $followersArray[] = [
                    'id' => $item->getId(),
                    'username' => $item->getUsername(),
                    'email' => $item->getEmail()
                ];
            }
        }
        $tags = $data->getTags();
        $tagsArray = [];
        if (count($tags) > 0) {
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
        if (count($taskHasAssignedUsers) > 0) {
            /** @var TaskHasAssignedUser $item */
            foreach ($taskHasAssignedUsers as $item) {
                $taskHasAssignedUsersArray[] = [
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
                        'email' => $item->getUser()->getEmail()
                    ]
                ];
            }
        }
        $taskHasAttachments = $data->getTaskHasAttachments();
        $taskHasAttachmentsArray = [];
        if (count($taskHasAttachments) > 0) {
            /** @var TaskHasAttachment $item */
            foreach ($taskHasAttachments as $item) {
                $taskHasAttachmentsArray[] = [
                    'id' => $item->getId(),
                    'slug' => $item->getSlug()
                ];
            }
        }
        $comments = $data->getComments();
        $commentsArray = [];
        $processedCommentsIds = [];
        if (count($comments) > 0) {
            /** @var Comment $comment */
            foreach ($comments as $comment) {
                // Check if comment was processed yet
                if (in_array($comment->getId(), $processedCommentsIds)) {
                    continue;
                }

                // Check if comment has children or not
                $commentsChildren = $comment->getInversedComment();
                if (count($commentsChildren) > 0) {
                    $processedCommentsIds[] = $comment->getId();
                    $commentsArray[] = [
                        'parent' => true,
                        'id' => $comment->getId(),
                        'title' => $comment->getTitle(),
                        'body' => $comment->getBody(),
                        'createdAt' => $comment->getCreatedAt(),
                        'updatedAt' => $comment->getUpdatedAt(),
                        'internal' => $comment->getInternal(),
                        'email' => $comment->getEmail(),
                        'email_to' => $comment->getEmailTo(),
                        'email_cc' => $comment->getEmailCc(),
                        'email_bcc' => $comment->getEmailBcc(),
                        'createdBy' => [
                            'id' => $comment->getCreatedBy()->getId(),
                            'username' => $comment->getCreatedBy()->getUsername(),
                            'email' => $comment->getCreatedBy()->getEmail()
                        ]
                    ];
                    $this->buildCommentTree($comment, $commentsArray, $processedCommentsIds, $comment->getId(), false);
                } else {
                    $processedCommentsIds[] = $comment->getId();
                    $commentsArray[] = [
                        'id' => $comment->getId(),
                        'title' => $comment->getTitle(),
                        'body' => $comment->getBody(),
                        'createdAt' => $comment->getCreatedAt(),
                        'updatedAt' => $comment->getUpdatedAt(),
                        'internal' => $comment->getInternal(),
                        'email' => $comment->getEmail(),
                        'email_to' => $comment->getEmailTo(),
                        'email_cc' => $comment->getEmailCc(),
                        'email_bcc' => $comment->getEmailBcc(),
                        'createdBy' => [
                            'id' => $comment->getCreatedBy()->getId(),
                            'username' => $comment->getCreatedBy()->getUsername(),
                            'email' => $comment->getCreatedBy()->getEmail()
                        ]
                    ];
                }
            }
        }
        $invoiceableItems = $data->getInvoiceableItems();
        $invoiceableItemsArray = [];
        if (count($invoiceableItems) > 0) {
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
        }

        $response = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'description' => $data->getDescription(),
            'deadline' => $data->getDeadline(),
            'startedAt' => $data->getDeadline(),
            'closedAt' => $data->getDeadline(),
            'important' => $data->getImportant(),
            'work' => $data->getWork(),
            'work_time' => $data->getWorkTime(),
            'createdAt' => $data->getCreatedAt(),
            'updatedAt' => $data->getUpdatedAt(),
            'createdBy' => [
                'id' => $data->getCreatedBy()->getId(),
                'username' => $data->getCreatedBy()->getUsername(),
                'email' => $data->getCreatedBy()->getEmail()
            ],
            'requestedBy' => [
                'id' => $data->getRequestedBy()->getId(),
                'username' => $data->getRequestedBy()->getUsername(),
                'email' => $data->getRequestedBy()->getEmail()
            ],
            'project' => [
                'id' => $data->getProject()->getId(),
                'title' => $data->getProject()->getTitle()
            ],
            'company' => [
                'id' => $data->getCompany()->getId(),
                'title' => $data->getCompany()->getTitle()
            ],
            'taskData' => $taskDataArray,
            'followers' => $followersArray,
            'tags' => $tagsArray,
            'taskHasAssignedUsers' => $taskHasAssignedUsersArray,
            'taskHasAttachments' => $taskHasAttachmentsArray,
            'comments' => $commentsArray,
            'invoiceableItems' => $invoiceableItemsArray
        ];

        return $response;
    }

    /**
     * @param Comment $comment
     * @param array $commentsArray
     * @param array $processedCommentsIds
     * @param int $parentId
     * @param bool $addToArray
     * @return array
     */
    private function buildCommentTree(Comment $comment, array &$commentsArray, array &$processedCommentsIds, int $parentId, $addToArray = false):array
    {
        if ($addToArray) {
            $processedCommentsIds[] = $comment->getId();
            $commentsArray[$parentId][] = [
                'child' => true,
                'parentId' => $parentId,
                'id' => $comment->getId(),
                'title' => $comment->getTitle(),
                'body' => $comment->getBody(),
                'createdAt' => $comment->getCreatedAt(),
                'updatedAt' => $comment->getUpdatedAt(),
                'internal' => $comment->getInternal(),
                'email' => $comment->getEmail(),
                'email_to' => $comment->getEmailTo(),
                'email_cc' => $comment->getEmailCc(),
                'email_bcc' => $comment->getEmailBcc(),
                'createdBy' => [
                    'id' => $comment->getCreatedBy()->getId(),
                    'username' => $comment->getCreatedBy()->getUsername(),
                    'email' => $comment->getCreatedBy()->getEmail()
                ]
            ];
        }

        $children = $comment->getInversedComment();
        foreach ($children as $child) {
            $this->buildCommentTree($child, $commentsArray, $processedCommentsIds, $comment->getId(), true);
        }

        return $commentsArray;
    }
}
