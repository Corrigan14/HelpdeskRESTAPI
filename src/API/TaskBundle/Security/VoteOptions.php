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
    const CREATE_USER_WITH_USER_ROLE = 'create_user';
    const UPDATE_USER_WITH_USER_ROLE = 'update_user';

    // TAG VOTE OPTIONS
    const SHOW_TAG = 'show_tag';
    const UPDATE_TAG = 'update_tag';
    const DELETE_TAG = 'delete_tag';

    // FILTER VOTE OPTIONS
    const SHOW_FILTER = 'show_filter';
    const UPDATE_FILTER = 'update_filter';
    const UPDATE_PROJECT_FILTER = 'update_project_filter';
    const DELETE_FILTER = 'delete_filter';
    const SET_REMEMBERED_FILTER = 'set_remembered_filter';

    // TASK VOTE OPTIONS
    const SHOW_TASK = 'read_task';
    const CREATE_TASK_IN_PROJECT = 'create_task';
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

    // PROJECT CRUD
    const LIST_PROJECTS = 'list_projects';
    const VIEW_PROJECT = 'read_project';
    const EDIT_PROJECT = 'edit_project';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}