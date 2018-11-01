<?php

namespace API\TaskBundle\Services\Task;

use API\CoreBundle\Entity\User;


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
     * @return array
     */
    public function createOptionsForTasksArray(User $user, $isAdmin, $processedBasicFilterParams, $processedOrderParam, $processedAdditionalFilterParams): array
    {
        return [
            'loggedUser' => $user,
            'isAdmin' => $isAdmin,
            'inFilter' => ['inFilter'],
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
            'project' => null
        ];
    }

}