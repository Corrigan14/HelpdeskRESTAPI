<?php

namespace API\TaskBundle\Security;

/**
 * Class StatusOptions
 *
 * @package API\TaskBundle\Security
 */
class StatusOptions
{
    const NEW = 'new';
    const IN_PROGRESS = 'In Progress';
    const COMPLETED = 'Completed';
    const CLOSED = 'Closed';

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