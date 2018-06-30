<?php

namespace API\TaskBundle\Security;

/**
 * Class RepeatingTaskIntervalOptions
 * @package API\TaskBundle\Security
 */
class RepeatingTaskIntervalOptions
{
    public const DAY = 'day';
    public const WEEK = 'week';
    public const MONTH = 'month';
    public const YEAR = 'year';

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