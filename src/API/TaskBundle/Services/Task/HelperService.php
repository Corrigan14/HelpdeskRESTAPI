<?php

namespace API\TaskBundle\Services\Task;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Filter;


/**
 * Class HelperService
 *
 * @package API\TaskBundle\Services\Task
 */
class HelperService
{
    /**
     * @param User $user
     * @param $isAdmin
     * @param $processedBasicFilterParams
     * @param $processedOrderParam
     * @param $processedAdditionalFilterParams
     * @param boolean|Filter $filter
     * @return array
     */
    public function createOptionsForTasksArray(User $user, $isAdmin, $processedBasicFilterParams, $processedOrderParam, $processedAdditionalFilterParams, $filter = false): array
    {
        $project = null;
        if ($filter) {
            $project = $filter->getProject();
        }

        return [
            'loggedUser' => $user,
            'isAdmin' => $isAdmin,
            'inFilter' => $processedAdditionalFilterParams['inFilter'],
            'equalFilter' => $processedAdditionalFilterParams['equalFilter'],
            'isNullFilter' => $processedAdditionalFilterParams['isNullFilter'],
            'dateFilter' => $processedAdditionalFilterParams['dateFilter'],
            'searchFilter' => $processedAdditionalFilterParams['searchFilter'],
            'notAndCurrentFilter' => $processedAdditionalFilterParams['notAndCurrentFilter'],
            'inFilterAddedParams' => $processedAdditionalFilterParams['inFilterAddedParams'],
            'equalFilterAddedParams' => $processedAdditionalFilterParams['equalFilterAddedParams'],
            'dateFilterAddedParams' => $processedAdditionalFilterParams['dateFilterAddedParams'],
            'filtersForUrl' => $processedAdditionalFilterParams['filterForUrl'],
            'order' => $processedOrderParam['order'],
            'limit' => $processedBasicFilterParams['limit'],
            'page' => $processedBasicFilterParams['page'],
            'project' => $project
        ];
    }

    /**
     * @param $processedAdditionalFilterParams
     * @param string $processedOrderParam
     * @param $filter
     * @return array
     */
    public function createOptionsForCompanyArray($processedAdditionalFilterParams, string $processedOrderParam, $filter): array
    {
        $project = null;
        if ($filter) {
            $project = $filter->getProject();
        }

        return [
            'inFilter' => $processedAdditionalFilterParams['inFilter'],
            'equalFilter' => $processedAdditionalFilterParams['equalFilter'],
            'isNullFilter' => $processedAdditionalFilterParams['isNullFilter'],
            'dateFilter' => $processedAdditionalFilterParams['dateFilter'],
            'searchFilter' => $processedAdditionalFilterParams['searchFilter'],
            'notAndCurrentFilter' => $processedAdditionalFilterParams['notAndCurrentFilter'],
            'inFilterAddedParams' => $processedAdditionalFilterParams['inFilterAddedParams'],
            'equalFilterAddedParams' => $processedAdditionalFilterParams['equalFilterAddedParams'],
            'dateFilterAddedParams' => $processedAdditionalFilterParams['dateFilterAddedParams'],
            'filtersForUrl' => $processedAdditionalFilterParams['filterForUrl'],
            'order' => $processedOrderParam,
            'project' => $project
        ];
    }
}