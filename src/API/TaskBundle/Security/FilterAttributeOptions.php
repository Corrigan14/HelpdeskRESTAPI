<?php

namespace API\TaskBundle\Security;

/**
 * Class FilterAttributeOptions
 *
 * @package API\TaskBundle\Security
 */
class FilterAttributeOptions
{
    // Attributes available for filter
    const STATUS = 'status';
    const PROJECT = 'project';
    const CREATOR = 'creator';
    const REQUESTER = 'requester';
    const COMPANY = 'taskCompany';
    const ASSIGNED = 'assigned';
    const TAG = 'tag';
    const FOLLOWER = 'follower';
    const CREATED = 'createdTime';
    const STARTED = 'startedTime';
    const DEADLINE = 'deadlineTime';
    const CLOSED = 'closedTime';
    const ARCHIVED = 'archived';
    const IMPORTANT = 'important';
    const ADDED_PARAMETERS = 'addedParameters';
    const SEARCH = 'search';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}