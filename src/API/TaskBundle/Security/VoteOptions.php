<?php

namespace API\TaskBundle\Security;

/**
 * Class VoteOptions
 *
 * @package API\TaskBundle\Security
 */
class VoteOptions
{
    // TAG CRUD
    const CREATE_PUBLIC_TAG = 'create_public_tag';
    const SHOW_TAG = 'show_tag';
    const UPDATE_TAG = 'update_tag';
    const DELETE_TAG = 'delete_tag';

    // STATUS CRUD
    const CREATE_STATUS = 'create_status';
    const SHOW_STATUS = 'read_status';
    const UPDATE_STATUS = 'update_status';
    const DELETE_STATUS = 'delete_status';
    const LIST_STATUSES = 'list_statuses';

    // COMPANY ATTRIBUTE CRUD
    const CREATE_COMPANY_ATTRIBUTE = 'create_company_attribute';
    const SHOW_COMPANY_ATTRIBUTE = 'read_company_attribute';
    const UPDATE_COMPANY_ATTRIBUTE = 'update_company_attribute';
    const DELETE_COMPANY_ATTRIBUTE = 'delete_company_attribute';
    const LIST_COMPANY_ATTRIBUTES = 'list_company_attributes';

    // PROJECT CRUD
    const CREATE_PROJECT = 'create_project';
    const LIST_PROJECTS = 'list_projects';

    // USER HAS PROJECT ACL OPTIONS
    const ADD_USER_TO_PROJECT = 'add_user_to_project';
    const REMOVE_USER_FROM_PROJECT = 'remove_user_from_project';
    const EDIT_USER_ACL_IN_PROJECT = 'edit_user_acl_in_project';
    const VIEW_PROJECT = 'read_project';
    const UPDATE_PROJECT = 'update_project';
    const DELETE_PROJECT = 'delete_project';

    const CREATE_TASK_IN_PROJECT = 'create_task_in_project';
    const UPDATE_ALL_TASKS_IN_PROJECT = 'update_all_tasks_in_project';
    const UPDATE_COMPANY_TASKS_IN_PROJECT = 'update_company_tasks_in_project';
    const UPDATE_USER_TASKS_IN_PROJECT = 'update_user_tasks_in_project';
    const VIEW_ALL_TASKS_IN_PROJECT = 'view_all_tasks_in_project';
    const VIEW_COMPANY_TASKS_IN_PROJECT = 'view_company_tasks_in_project';
    const VIEW_USER_TASKS_IN_PROJECT = 'view_user_tasks_in_project';

    // TASK ATTRIBUTE CRUD
    const CREATE_TASK_ATTRIBUTE = 'create_task_attribute';
    const SHOW_TASK_ATTRIBUTE = 'read_task_attribute';
    const UPDATE_TASK_ATTRIBUTE = 'update_task_attribute';
    const DELETE_TASK_ATTRIBUTE = 'delete_task_attribute';
    const LIST_TASK_ATTRIBUTES = 'list_task_attributes';

    // TASK CRUD
    const LIST_TASKS = 'list_tasks';
    const CREATE_TASK = 'create_task';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}