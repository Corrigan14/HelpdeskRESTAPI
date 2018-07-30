<?php

namespace API\TaskBundle\Security;

/**
 * Class StatusFunctionOptions
 *
 * @package API\TaskBundle\Security
 */
class StatusFunctionOptions
{
    const NEW_TASK = 'new_task';
    const IN_PROGRESS_TASK = 'in_progress_task';
    const COMPLETED_TASK = 'completed_task';
    const CLOSED_TASK = 'closed_task';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getConstants(): array
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}