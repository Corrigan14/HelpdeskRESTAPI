<?php

namespace API\TaskBundle\Services;

/**
 * Class FilterAttributeOptions
 *
 * @package API\TaskBundle\Services
 */
class FilterAttributeOptions
{
    // Attributes available for filter
    const STATUS = 'status';
    const PROJECT = 'project';
    const CREATOR = 'creator';
    const REQUESTER = 'requester';
    const COMPANY = 'company';
    const ASSIGNED = 'assigned';
    const TAG = 'tag';
    const FOLLOWER = 'follower';
    const CREATED = 'created';
    const STARTED = 'started';
    const DEADLINE = 'deadline';
    const CLOSED = 'closed';
    const ARCHIVED = 'archived';
    const ADDED_PARAMETERS = 'addedParameters';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}