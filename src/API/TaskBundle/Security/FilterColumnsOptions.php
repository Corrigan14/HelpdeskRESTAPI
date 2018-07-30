<?php

namespace API\TaskBundle\Security;

/**
 * Class FilterColumnsOptions
 *
 * @package API\TaskBundle\Security
 */
class FilterColumnsOptions
{
    // Attributes available for filter
    public const ID = 'id';
    public const TITLE = 'title';
    public const DESCRIPTION = 'description';
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
    public const WORK = 'work';
    public const WORK_TIME = 'work_time';
    public const ATTACHMENT = 'attachment';

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