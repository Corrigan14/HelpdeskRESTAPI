<?php

namespace API\TaskBundle\Security\RepeatingTask;

/**
 * Class RepeatingTaskIntervalOptions
 * @package API\TaskBundle\Security\RepeatingTask
 */
class IntervalOptions
{
    const DAY = 'day';
    const WEEK = 'week';
    const MONTH = 'month';
    const YEAR = 'year';

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