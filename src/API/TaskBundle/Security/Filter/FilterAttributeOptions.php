<?php

namespace API\TaskBundle\Security\Filter;

/**
 * Class FilterAttributeOptions
 *
 * @package API\TaskBundle\Security
 */
class FilterAttributeOptions
{
    // Attributes available for filter
    const ID = 'id';
    const TITLE = 'title';
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
    const REPORT = 'report';

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