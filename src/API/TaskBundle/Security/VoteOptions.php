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
    const SOLVE_ALL_TASKS_IN_PROJECT = 'solve_all_tasks_in_project';
    const SOLVE_COMPANY_TASKS_IN_PROJECT = 'solve_company_tasks_in_project';
    const SOLVE_USER_TASKS_IN_PROJECT = 'solve_user_tasks_in_project';

    // TASK ATTRIBUTE CRUD
    const CREATE_TASK_ATTRIBUTE = 'create_task_attribute';
    const SHOW_TASK_ATTRIBUTE = 'read_task_attribute';
    const UPDATE_TASK_ATTRIBUTE = 'update_task_attribute';
    const DELETE_TASK_ATTRIBUTE = 'delete_task_attribute';
    const LIST_TASK_ATTRIBUTES = 'list_task_attributes';

    // TASK CRUD
    const LIST_TASKS = 'list_tasks';
    const SHOW_TASK = 'read_task';
    const CREATE_TASK = 'create_task';
    const UPDATE_TASK = 'update_task';
    const DELETE_TASK = 'delete_task';
    const SOLVE_TASK = 'solve_task';

    // OTHER WITH TASK
    const ADD_TASK_FOLLOWER = 'add_task_follower';
    const REMOVE_TASK_FOLLOWER = 'remove_task_follower';
    const SHOW_LIST_OF_TASK_FOLLOWERS = 'show_list_of_task_followers';
    const ADD_TAG_TO_TASK = 'add_tag_to_task';
    const REMOVE_TAG_FROM_TASK = 'remove_tag_from_task';
    const SHOW_LIST_OF_TASK_TAGS = 'show_list_of_task_tags';
    const ASSIGN_USER_TO_TASK = 'assign_user_to_task';
    const UPDATE_ASSIGN_USER_TO_TASK = 'update_assign_user_to_task';
    const REMOVE_ASSIGN_USER_FROM_TASK = 'remove_assign_user_from_task';
    const SHOW_LIST_OF_USERS_ASSIGNED_TO_TASK = 'show_list_of_users_assigned_to_task';
    const ADD_ATTACHMENT_TO_TASK = 'add_attachment_to_task';
    const REMOVE_ATTACHMENT_FROM_TASK = 'remove_attachment_from_task';
    const SHOW_LIST_OF_TASK_ATTACHMENTS = 'show_list_of_task_attachments';
    const ADD_COMMENT_TO_TASK = 'add_comment_to_task';
    const ADD_COMMENT_TO_COMMENT = 'add_comment_to_comment';
    const DELETE_COMMENT = 'delete_comment';
    const SHOW_LIST_OF_TASKS_COMMENTS = 'show_list_of_tasks_comments';
    const SHOW_TASKS_COMMENT = 'show_tasks_comment';
    const ADD_ATTACHMENT_TO_COMMENT = 'add_attachment_to_comment';
    const REMOVE_ATTACHMENT_FROM_COMMENT = 'remove_attachment_from_comment';
    const SHOW_LIST_OF_COMMENTS_ATTACHMENTS = 'show_list_of_comments_attachments';

    // FILTER CRUD
    const SHOW_FILTER = 'show_filter';
    const CREATE_PUBLIC_FILTER = 'create_public_filter';
    const CREATE_FILTER = 'create_filter';
    const CREATE_PROJECT_FILTER = 'create_project_filter';
    const CREATE_PUBLIC_PROJECT_FILTER = 'create_public_project_filter';
    const UPDATE_FILTER = 'update_filter';
    const UPDATE_PROJECT_FILTER = 'update_project_filter';
    const DELETE_FILTER = 'delete_filter';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}