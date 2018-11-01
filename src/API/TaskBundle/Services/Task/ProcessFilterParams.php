<?php

namespace API\TaskBundle\Services\Task;


use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Security\Filter\FilterAttributeOptions;
use API\TaskBundle\Services\VariableHelper;
use Doctrine\ORM\EntityManager;

/**
 * Class ProcessFilterParams
 *
 * @package API\TaskBundle\Services\Task
 */
class ProcessFilterParams
{
    /**
     * @var EntityManager
     */
    private $em;


    /**
     * ProjectService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param array $requestBody
     * @param User $loggedUser
     * @return array
     * @throws \LogicException
     */
    public function processFilterData(array $requestBody, User $loggedUser): array
    {
        $dataArray = $this->createArrayOfParams($requestBody);

        return $this->processData($dataArray, $loggedUser);
    }

    /**
     * @param $requestBody
     * @return array
     */
    private function createArrayOfParams($requestBody): array
    {
        $data = [];

        if (isset($requestBody['search'])) {
            $data[FilterAttributeOptions::SEARCH] = $requestBody['search'];
        }
        if (isset($requestBody['status'])) {
            $data[FilterAttributeOptions::STATUS] = $requestBody['status'];
        }
        if (isset($requestBody['project'])) {
            $data[FilterAttributeOptions::PROJECT] = $requestBody['project'];
        }
        if (isset($requestBody['creator'])) {
            $data[FilterAttributeOptions::CREATOR] = $requestBody['creator'];
        }
        if (isset($requestBody['requester'])) {
            $data[FilterAttributeOptions::REQUESTER] = $requestBody['requester'];
        }
        if (isset($requestBody['company'])) {
            $data[FilterAttributeOptions::COMPANY] = $requestBody['company'];
        }
        if (isset($requestBody['assigned'])) {
            $data[FilterAttributeOptions::ASSIGNED] = $requestBody['assigned'];
        }
        if (isset($requestBody['tag'])) {
            $data[FilterAttributeOptions::TAG] = $requestBody['tag'];
        }
        if (isset($requestBody['follower'])) {
            $data[FilterAttributeOptions::FOLLOWER] = $requestBody['follower'];
        }
        if (isset($requestBody['createdTime'])) {
            $data[FilterAttributeOptions::CREATED] = $requestBody['createdTime'];
        }
        if (isset($requestBody['startedTime'])) {
            $data[FilterAttributeOptions::STARTED] = $requestBody['startedTime'];
        }
        if (isset($requestBody['deadlineTime'])) {
            $data[FilterAttributeOptions::DEADLINE] = $requestBody['deadlineTime'];
        }
        if (isset($requestBody['closedTime'])) {
            $data[FilterAttributeOptions::CLOSED] = $requestBody['closedTime'];
        }
        if (isset($requestBody['archived'])) {
            if ('true' === strtolower($requestBody['archived'])) {
                $data[FilterAttributeOptions::ARCHIVED] = true;
            }
        }
        if (isset($requestBody['important']) && 'true' === strtolower($requestBody['important'])) {
            $data[FilterAttributeOptions::IMPORTANT] = true;
        }
        if (isset($requestBody['addedParameters'])) {
            $data[FilterAttributeOptions::ADDED_PARAMETERS] = $requestBody['addedParameters'];
        }

        return $data;

    }

    /**
     * @param array $data
     * @param User $user
     *
     * @return array
     * @throws \LogicException
     */
    private function processData(array $data, User $user): array
    {
        $inFilter = [];
        $dateFilter = [];
        $equalFilter = [];
        $isNullFilter = [];
        $notAndOptionsFilter = [];
        $searchFilter = null;

        $inFilterAddedParams = [];
        $dateFilterAddedParams = [];
        $equalFilterAddedParams = [];

        $filterForUrl = [];

        if (isset($data[FilterAttributeOptions::SEARCH])) {
            $searchFilter = $data[FilterAttributeOptions::SEARCH];
            $filterForUrl['search'] = '&search=' . $data['search'];
        }
        if (isset($data[FilterAttributeOptions::STATUS])) {
            $status = $data[FilterAttributeOptions::STATUS];
            if (!\is_array($status)) {
                $inFilter['taskGlobalStatus.id'] = explode(',', $status);
                $filterForUrl['status'] = '&status=' . $status;
            } else {
                $inFilter['taskGlobalStatus.id'] = $status;
                $filterForUrl['status'] = '&status=' . implode(',', $status);
            }
        }
        if (isset($data[FilterAttributeOptions::PROJECT])) {
            $project = $data[FilterAttributeOptions::PROJECT];
            if (!\is_array($project)) {
                $separatedData = $this->separateCurrentUserFilterData($user, $project, false, false, true);
                $filterForUrl['project'] = '&project=' . $project;
            } else {
                $separatedData = $this->separateCurrentUserFilterData($user, $project, true, false, true);
                $filterForUrl['project'] = '&project=' . implode(',', $project);
            }

            if (isset($separatedData['isNullFilter'])) {
                $isNullFilter[] = 'task.project';
            }
            if (isset($separatedData['equalFilter'])) {
                $equalFilter['project.id'] = $separatedData['equalFilter'];
            }
            if (isset($separatedData['notAndOptionsFilter'])) {
                $separatedData['notAndOptionsFilter']['not'] = 'task.project';
                $separatedData['notAndOptionsFilter']['equal']['key'] = 'project.id';
                $notAndOptionsFilter[] = $separatedData['notAndOptionsFilter'];
            }
            if (isset($separatedData['inFilter'])) {
                $inFilter['project.id'] = $separatedData['inFilter'];
            }
        }
        if (isset($data[FilterAttributeOptions::CREATOR])) {
            $creator = $data[FilterAttributeOptions::CREATOR];
            if (!\is_array($creator)) {
                $separatedData = $this->separateCurrentUserFilterData($user, $creator, false, false, false);
                $filterForUrl['createdBy'] = '&creator=' . $creator;
            } else {
                $separatedData = $this->separateCurrentUserFilterData($user, $creator, true, false, false);
                $filterForUrl['createdBy'] = '&creator=' . implode(',', $creator);
            }

            if (isset($separatedData['inFilter'])) {
                $inFilter['createdBy.id'] = $separatedData['inFilter'];
            }
        }
        if (isset($data[FilterAttributeOptions::REQUESTER])) {
            $requester = $data[FilterAttributeOptions::REQUESTER];
            if (!\is_array($requester)) {
                $separatedData = $this->separateCurrentUserFilterData($user, $requester, false, false, false);
                $filterForUrl['requestedBy'] = '&requester=' . $requester;
            } else {
                $separatedData = $this->separateCurrentUserFilterData($user, $requester, true, false, false);
                $filterForUrl['requestedBy'] = '&requester=' . implode(',', $requester);
            }

            if (isset($separatedData['inFilter'])) {
                $inFilter['requestedBy.id'] = $separatedData['inFilter'];
            }
        }
        if (isset($data[FilterAttributeOptions::COMPANY])) {
            $company = $data[FilterAttributeOptions::COMPANY];

            if (!\is_array($company)) {
                $separatedData = $this->separateCurrentUserFilterData($user, $company, false, true, false);
                $filterForUrl['taskCompany'] = '&taskCompany=' . $company;
            } else {
                $separatedData = $this->separateCurrentUserFilterData($user, $company, true, true, false);
                $filterForUrl['taskCompany'] = '&taskCompany=' . implode(',', $company);
            }

            if (isset($separatedData['inFilter'])) {
                $inFilter['taskCompany.id'] = $separatedData['inFilter'];
            }
        }
        if (isset($data[FilterAttributeOptions::ASSIGNED])) {
            $assigned = $data[FilterAttributeOptions::ASSIGNED];
            if (!\is_array($assigned)) {
                $separatedData = $this->separateCurrentUserFilterData($user, $assigned, false, false, false);
                $filterForUrl['assigned'] = '&assigned=' . $assigned;
            } else {
                $separatedData = $this->separateCurrentUserFilterData($user, $assigned, true, false, false);
                $filterForUrl['assigned'] = '&assigned=' . implode(',', $assigned);
            }

            if (isset($separatedData['isNullFilter'])) {
                $isNullFilter[] = 'taskHasAssignedUsers.user';
            }
            if (isset($separatedData['equalFilter'])) {
                $equalFilter['assignedUser.id'] = $separatedData['equalFilter'];
            }
            if (isset($separatedData['notAndOptionsFilter'])) {
                $separatedData['notAndOptionsFilter']['not'] = 'taskHasAssignedUsers.user';
                $separatedData['notAndOptionsFilter']['equal']['key'] = 'assignedUser.id';
                $notAndOptionsFilter[] = $separatedData['notAndOptionsFilter'];
            }
            if (isset($separatedData['inFilter'])) {
                $inFilter['assignedUser.id'] = $separatedData['inFilter'];
            }
        }
        if (isset($data[FilterAttributeOptions::TAG])) {
            $tag = $data[FilterAttributeOptions::TAG];
            if (!\is_array($tag)) {
                $inFilter['tags.id'] = explode(',', $tag);
                $filterForUrl['tag'] = '&tag=' . $tag;
            } else {
                $inFilter['tags.id'] = $tag;
                $filterForUrl['tag'] = '&tag=' . implode(',', $tag);
            }
        }
        if (isset($data[FilterAttributeOptions::FOLLOWER])) {
            $follower = $data[FilterAttributeOptions::FOLLOWER];
            if (!\is_array($follower)) {
                $separatedData = $this->separateCurrentUserFilterData($user, $follower, false, false, false);
                $filterForUrl['followers'] = '&follower=' . $follower;
            } else {
                $separatedData = $this->separateCurrentUserFilterData($user, $follower, true, false, false);
                $filterForUrl['followers'] = '&follower=' . implode(',', $follower);
            }

            if (isset($separatedData['inFilter'])) {
                $inFilter['followers.id'] = $separatedData['inFilter'];
            }
        }
        if (isset($data[FilterAttributeOptions::CREATED])) {
            $created = $data[FilterAttributeOptions::CREATED];
            $fromToData = $this->separateFromToDateData($created);
            $dateFilter['task.createdAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['created'] = '&createdTime=' . $created;
        }
        if (isset($data[FilterAttributeOptions::STARTED])) {
            $started = $data[FilterAttributeOptions::STARTED];
            $fromToData = $this->separateFromToDateData($started);
            $dateFilter['task.startedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['started'] = '&startedTime=' . $started;
        }
        if (isset($data[FilterAttributeOptions::DEADLINE])) {
            $deadline = $data[FilterAttributeOptions::DEADLINE];
            $fromToData = $this->separateFromToDateData($deadline);
            $dateFilter['task.deadline'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['deadline'] = '&deadlineTime=' . $deadline;
        }
        if (isset($data[FilterAttributeOptions::CLOSED])) {
            $closed = $data[FilterAttributeOptions::CLOSED];
            $fromToData = $this->separateFromToDateData($closed);
            $dateFilter['task.closedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['closed'] = '&closedTime=' . $closed;
        }
        if (isset($data[FilterAttributeOptions::ARCHIVED])) {
            if ('true' === strtolower($data[FilterAttributeOptions::ARCHIVED]) || true === $data[FilterAttributeOptions::ARCHIVED]) {
                $equalFilter['project.is_active'] = 0;
                $filterForUrl['archived'] = '&archived=TRUE';
            }
        }
        if (isset($data[FilterAttributeOptions::IMPORTANT])) {
            if ('true' === strtolower($data[FilterAttributeOptions::IMPORTANT]) || true === $data[FilterAttributeOptions::IMPORTANT]) {
                $equalFilter['task.important'] = 1;
                $filterForUrl['important'] = '&important=TRUE';
            }
        }
        if (isset($data[FilterAttributeOptions::ADDED_PARAMETERS])) {
            $processedAddedParams = $this->processAddedParams($data[FilterAttributeOptions::ADDED_PARAMETERS]);

            $filterForUrl['addedParameters'] = $processedAddedParams['filterForUrl'];
            $inFilterAddedParams = $processedAddedParams['inFilterAddedParams'];
            $equalFilterAddedParams = $processedAddedParams['equalFilterAddedParams'];
            $dateFilterAddedParams = $processedAddedParams['dateFilterAddedParams'];
        }

        return [
            'inFilter' => $inFilter,
            'equalFilter' => $equalFilter,
            'dateFilter' => $dateFilter,
            'isNullFilter' => $isNullFilter,
            'searchFilter' => $searchFilter,
            'notAndCurrentFilter' => $notAndOptionsFilter,
            'inFilterAddedParams' => $inFilterAddedParams,
            'equalFilterAddedParams' => $equalFilterAddedParams,
            'dateFilterAddedParams' => $dateFilterAddedParams,
            'filterForUrl' => $filterForUrl,
        ];
    }

    /**
     * Process additional params of a task
     *
     * @param $addedParameters
     * @return array
     */
    private function processAddedParams($addedParameters): array
    {
        $inFilterAddedParams = [];
        $dateFilterAddedParams = [];
        $equalFilterAddedParams = [];

        if (!\is_array($addedParameters)) {
            $arrayOfAddedParameters = explode('&', $addedParameters);

            foreach ($arrayOfAddedParameters as $value) {
                $strpos = explode('=', $value);
                $attributeId = $strpos[0];

                $taskAttribute = $this->em->getRepository('APITaskBundle:TaskAttribute')->find($attributeId);
                if (!$taskAttribute instanceof TaskAttribute) {
                    continue;
                }

                $typeOfTaskAttribute = $taskAttribute->getType();
                if (VariableHelper::CHECKBOX === $typeOfTaskAttribute) {
                    if ('true' === strtolower($strpos[1])) {
                        $equalFilterAddedParams[$attributeId] = 1;
                    } elseif ('false' === strtolower($strpos[1])) {
                        $equalFilterAddedParams[$attributeId] = 0;
                    }
                } elseif (VariableHelper::DATE === $typeOfTaskAttribute) {
                    $dateData = $this->separateFromToDateData($strpos[1], ':');
                    $dateFilterAddedParams[$attributeId] = $dateData;
                } else {
                    $attributeValues = explode(',', $strpos[1]);
                    $inFilterAddedParams[$attributeId] = $attributeValues;
                }

            }

            return [
                'inFilterAddedParams' => $inFilterAddedParams,
                'dateFilterAddedParams' => $dateFilterAddedParams,
                'equalFilterAddedParams' => $equalFilterAddedParams,
                'filterForUrl' => '&addedParameters=' . $addedParameters
            ];
        }


        foreach ($addedParameters as $key => $value) {
            $taskAttribute = $this->em->getRepository('APITaskBundle:TaskAttribute')->find($key);
            if (!$taskAttribute instanceof TaskAttribute) {
                continue;
            }

            $typeOfTaskAttribute = $taskAttribute->getType();
            if (VariableHelper::CHECKBOX === $typeOfTaskAttribute) {
                if ('true' === $value || true === $value) {
                    $equalFilterAddedParams[$key] = 1;
                } elseif ('false' === $value || false === $value) {
                    $equalFilterAddedParams[$key] = 0;
                }
            } elseif (VariableHelper::DATE === $typeOfTaskAttribute) {
                $dateData = $this->separateFromToDateData($value, ':');
                $dateFilterAddedParams[$key] = $dateData;
            } else {
                $inFilterAddedParams[$key] = $value;
            }
        }

        return [
            'inFilterAddedParams' => $inFilterAddedParams,
            'dateFilterAddedParams' => $dateFilterAddedParams,
            'equalFilterAddedParams' => $equalFilterAddedParams,
            'filterForUrl' => '&addedParameters=' . implode('&', $addedParameters)
        ];
    }

    /**
     * @param User $user
     * @param string|array $data
     * @param bool $isArray
     * @param bool $company
     * @param bool $project
     * @return array
     */
    private function separateCurrentUserFilterData(User $user, $data, bool $isArray, bool $company, bool $project): array
    {
        $response = [];
        $currentUserId = $user->getId();

        if (!$isArray) {
            $dataArray = explode(',', $data);
        } else {
            $dataArray = $data;
        }

        if ($company) {
            $currentUserId = $user->getCompany()->getId();
        }

        if ($project) {
            // List of project created by logged user
            $userProjects = $user->getProjects();
            $currentUserIds = [];
            /** @var Project $userProject */
            foreach ($userProjects as $userProject) {
                $currentUserIds[] = $userProject->getId();
            }
            if (\count($currentUserIds) > 0) {
                $currentUserId = $currentUserIds;
            }
        }

        foreach ($dataArray as $datum) {
            if ('current-user' === strtolower($datum)) {
                $response['inFilter'][] = $currentUserId;
            } elseif ('not' !== strtolower($datum)) {
                $response['inFilter'][] = (int)$datum;
            }
        }

        if (\count($dataArray) > 1 && (\in_array('not', $dataArray, true) || \in_array('NOT', $dataArray, true))) {
            $response['notAndOptionsFilter'] = [
                'equal' => [
                    'value' => $response['inFilter'],
                ],
            ];
            unset($response['inFilter']);
        }

        if (\count($dataArray) === 1 && (\in_array('not', $dataArray, true) || \in_array('NOT', $dataArray, true))) {
            $response['isNullFilter'] = true;
        }

        return $response;
    }

    /**
     * @param string $created
     *
     * @param string $separator
     * @return array
     */
    private function separateFromToDateData(string $created, $separator = '='): array
    {
        $created = strtoupper($created);

        $fromPosition = strpos($created, 'FROM' . $separator);
        $toPosition = strpos($created, 'TO' . $separator);

        $toDataDate = null;
        $fromDataDate = null;
        if (false !== $fromPosition && false !== $toPosition) {
            $fromData = substr($created, $fromPosition + 5, $toPosition - 6);
            $toData = substr($created, $toPosition + 3);

            $fromDataTimestamp = (int)$fromData;
            $fromDataDate = new \DateTime("@$fromDataTimestamp");

            if ('NOW' === $toData) {
                $toDataDate = new \DateTime();
            } else {
                $toDataTimestamp = (int)$toData;
                $toDataDate = new \DateTime("@$toDataTimestamp");
            }
        } elseif (false !== $fromPosition && false === $toPosition) {
            $fromData = substr($created, $fromPosition + 5);
            $fromDataTimestamp = (int)$fromData;
            $fromDataDate = new \DateTime("@$fromDataTimestamp");
        } elseif (false !== $toPosition && false === $fromPosition) {
            $toData = substr($created, $toPosition + 3);
            if ('NOW' === $toData) {
                $toDataDate = new \DateTime();
            } else {
                $toDataTimestamp = (int)$toData;
                $toDataDate = new \DateTime("@$toDataTimestamp");
            }
        }
        $response = [
            'from' => $fromDataDate,
            'to' => $toDataDate,
        ];
        return $response;
    }

}