<?php

namespace API\TaskBundle\Security;

/**
 * Class ProjectAclOptions
 *
 * @package API\TaskBundle\Security
 */
class ProjectAclOptions
{
    const VIEW_OWN_TASKS = 'view_own_tasks';
    const VIEW_TASKS_FROM_USERS_COMPANY = 'view_tasks_from_users_company';
    const VIEW_ALL_TASKS = 'view_all_tasks';
    const CREATE_TASK = 'create_task';
    const RESOLVE_TASK = 'resolve_task';
    const DELETE_TASK = 'delete_task';
    const VIEW_INTERNAL_NOTE = 'view_internal_note';
    const EDIT_INTERNAL_NOTE = 'edit_internal_note';
    const EDIT_PROJECT = 'edit_project';

    /**
     * @return array
     */
    public static function getConstants():array
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}