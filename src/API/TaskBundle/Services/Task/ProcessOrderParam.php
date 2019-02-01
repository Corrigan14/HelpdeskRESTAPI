<?php

namespace API\TaskBundle\Services\Task;


use API\TaskBundle\Security\Filter\FilterAttributeOptions;

/**
 * Class ProcessOrderParam
 *
 * @package API\TaskBundle\Services\Task
 */
class ProcessOrderParam
{
    /**
     * @param $requestBody
     * @return array
     * @throws \ReflectionException
     */
    public function processOrderParam($requestBody): array
    {
        $order = [];
        $allowedOrderParams = FilterAttributeOptions::getConstants();

        if (!isset($requestBody['order'])) {
            $order[FilterAttributeOptions::ID] = 'DESC';

            return [
                'correct' => true,
                'order' => $order
            ];
        }

        $orderString = $requestBody['order'];
        $orderArray = explode(',', $orderString);
        foreach ($orderArray as $item) {
            $orderArrayKeyValue = explode('=>', $item);

            //Check if param to order by is allowed
            if (!\in_array($orderArrayKeyValue[0], $allowedOrderParams, true)) {
                $message = 'Requested filter parameter ' . $orderArrayKeyValue[0] . ' is not allowed! Allowed are: ' . implode(',', $allowedOrderParams);
                return [
                    'correct' => false,
                    'message' => $message
                ];
            }

            $orderArrayKeyValueLower = strtolower($orderArrayKeyValue[1]);
            if (!($orderArrayKeyValueLower === 'asc' || $orderArrayKeyValueLower === 'desc')) {
                $message = $orderArrayKeyValue[1] . ' Is not allowed! You can order data only ASC or DESC!';

                return [
                    'correct' => false,
                    'message' => $message
                ];
            }
            $order[$orderArrayKeyValue[0]] = $orderArrayKeyValue[1];
        }


        return [
            'correct' => true,
            'order' => $order
        ];
    }

    /**
     * @param $requestBody
     * @return string
     */
    public function processOrderParamForCompanyList($requestBody): string
    {
        if (!isset($requestBody['order'])) {
            return 'DESC';
        }

        if ('ASC' === strtoupper($requestBody['order'])) {
            return 'ASC';
        }

        return 'DESC';

    }

}