<?php

namespace API\TaskBundle\Security\Filter;

/**
 * Class FilterColumnsOptions
 *
 * @package API\TaskBundle\Security\Filter
 */
class FilterColumnsOptions
{
    // Attributes available for filter
    const ID = 'id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
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
    const WORK = 'work';
    const WORK_TIME = 'work_time';
    const ATTACHMENT = 'attachment';
    const NULL = 'null';

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