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
    public const ID = 'id';
    public const TITLE = 'title';
    public const STATUS = 'status';
    public const PROJECT = 'project';
    public const CREATOR = 'creator';
    public const REQUESTER = 'requester';
    public const COMPANY = 'taskCompany';
    public const ASSIGNED = 'assigned';
    public const TAG = 'tag';
    public const FOLLOWER = 'follower';
    public const CREATED = 'createdTime';
    public const STARTED = 'startedTime';
    public const DEADLINE = 'deadlineTime';
    public const CLOSED = 'closedTime';
    public const ARCHIVED = 'archived';
    public const IMPORTANT = 'important';
    public const ADDED_PARAMETERS = 'addedParameters';
    public const SEARCH = 'search';

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