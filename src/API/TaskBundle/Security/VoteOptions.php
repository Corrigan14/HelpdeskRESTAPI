<?php

namespace API\TaskBundle\Security;

/**
 * Class VoteOptions
 *
 * @package API\TaskBundle\Security
 */
class VoteOptions
{
    // USER VOTE OPTIONS
    public const CREATE_USER_WITH_USER_ROLE = 'create_user';
    public const UPDATE_USER_WITH_USER_ROLE = 'update_user';

    // TAG VOTE OPTIONS
    public const SHOW_TAG = 'show_tag';
    public const UPDATE_TAG = 'update_tag';
    public const DELETE_TAG = 'delete_tag';

    // FILTER VOTE OPTIONS
    public const SHOW_FILTER = 'show_filter';
    public const UPDATE_FILTER = 'update_filter';
    public const UPDATE_PROJECT_FILTER = 'update_project_filter';
    public const CREATE_PROJECT_FILTER = 'create_project_filter';
    public const DELETE_FILTER = 'delete_filter';
    public const SET_REMEMBERED_FILTER = 'set_remembered_filter';

    // TASK VOTE OPTIONS
    public const SHOW_TASK = 'read_task';
    public const CREATE_TASK_IN_PROJECT = 'create_task';
    public const UPDATE_TASK = 'update_task';
    public const DELETE_TASK = 'delete_task';
    public const SOLVE_TASK = 'solve_task';

    // OTHER WITH TASK
    public const ADD_TASK_FOLLOWER = 'add_task_follower';
    public const REMOVE_TASK_FOLLOWER = 'remove_task_follower';
    public const SHOW_LIST_OF_TASK_FOLLOWERS = 'show_list_of_task_followers';
    public const ADD_TAG_TO_TASK = 'add_tag_to_task';
    public const REMOVE_TAG_FROM_TASK = 'remove_tag_from_task';
    public const SHOW_LIST_OF_TASK_TAGS = 'show_list_of_task_tags';
    public const ASSIGN_USER_TO_TASK = 'assign_user_to_task';
    public const UPDATE_ASSIGN_USER_TO_TASK = 'update_assign_user_to_task';
    public const REMOVE_ASSIGN_USER_FROM_TASK = 'remove_assign_user_from_task';
    public const SHOW_LIST_OF_USERS_ASSIGNED_TO_TASK = 'show_list_of_users_assigned_to_task';
    public const ADD_ATTACHMENT_TO_TASK = 'add_attachment_to_task';
    public const REMOVE_ATTACHMENT_FROM_TASK = 'remove_attachment_from_task';
    public const SHOW_LIST_OF_TASK_ATTACHMENTS = 'show_list_of_task_attachments';
    public const ADD_COMMENT_TO_TASK = 'add_comment_to_task';
    public const ADD_COMMENT_TO_COMMENT = 'add_comment_to_comment';
    public const DELETE_COMMENT = 'delete_comment';
    public const SHOW_LIST_OF_TASKS_COMMENTS = 'show_list_of_tasks_comments';
    public const SHOW_TASKS_COMMENT = 'show_tasks_comment';
    public const ADD_ATTACHMENT_TO_COMMENT = 'add_attachment_to_comment';
    public const REMOVE_ATTACHMENT_FROM_COMMENT = 'remove_attachment_from_comment';
    public const SHOW_LIST_OF_COMMENTS_ATTACHMENTS = 'show_list_of_comments_attachments';

    // PROJECT CRUD
    public const LIST_PROJECTS = 'list_projects';
    public const VIEW_PROJECT = 'read_project';
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