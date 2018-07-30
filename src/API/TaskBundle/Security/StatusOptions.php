<?php

namespace API\TaskBundle\Security;

/**
 * Class StatusOptions
 *
 * @package API\TaskBundle\Security
 */
class StatusOptions
{
    public const NEW = 'new';
    public const IN_PROGRESS = 'In Progress';
    public const COMPLETED = 'Completed';
    public const CLOSED = 'Closed';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getConstants():array
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}