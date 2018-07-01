<?php

namespace API\TaskBundle\Security;

/**
 * Class ProjectAclOptions
 *
 * @package API\TaskBundle\Security
 */
class ProjectAclOptions
{
    public const VIEW_OWN_TASKS = 'view_own_tasks';
    public const VIEW_TASKS_FROM_USERS_COMPANY = 'view_tasks_from_users_company';
    public const VIEW_ALL_TASKS = 'view_all_tasks';
    public const CREATE_TASK = 'create_task';
    public const RESOLVE_TASK = 'resolve_task';
    public const DELETE_TASK = 'delete_task';
    public const VIEW_INTERNAL_NOTE = 'view_internal_note';
    public const EDIT_INTERNAL_NOTE = 'edit_internal_note';
    public const EDIT_PROJECT = 'edit_project';

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