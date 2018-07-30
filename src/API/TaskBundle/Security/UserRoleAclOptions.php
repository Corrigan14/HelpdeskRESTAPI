<?php

namespace API\TaskBundle\Security;

/**
 * Class UserRoleAclOptions
 *
 * @package API\TaskBundle\Security
 */
class UserRoleAclOptions
{
    public const LOGIN_TO_SYSTEM = 'login_to_system';

    // User can CRUD PUBLIC Filters
    public const SHARE_FILTERS = 'share_filters';
    // User can CRUD PUBLIC Filters in Projects
    public const PROJECT_SHARED_FILTERS = 'project_shared_filters';
    // User can CRUD Filters checked like REPORT
    public const REPORT_FILTERS = 'report_filters';

    // User can CRUD PUBLIC Tags
    public const SHARE_TAGS = 'share_tags';

    // User can CRUD Projects
    public const CREATE_PROJECTS = 'create_projects';

    // User can Sent Emails from Comments
    public const SENT_EMAILS_FROM_COMMENTS = 'sent_emails_from_comments';

    // User can create Tasks
    public const CREATE_TASKS = 'create_tasks';
    // User can create Tasks in all Projects
    public const CREATE_TASKS_IN_ALL_PROJECTS = 'create_tasks_in_all_projects';
    // User can update All Tasks
    public const UPDATE_ALL_TASKS = 'update_all_tasks';

    // CRUD for User Entity
    public const USER_SETTINGS = 'user_settings';
    // CRUD for UserRole Entity
    public const USER_ROLE_SETTINGS = 'user_role_settings';
    // CRUD for CompanyAttribute Entity
    public const COMPANY_ATTRIBUTE_SETTINGS = 'company_attribute_settings';
    // CRUD for Company Entity
    public const COMPANY_SETTINGS = 'company_settings';
    // CRUD for Status Entity
    public const STATUS_SETTINGS = 'status_settings';
    // CRUD for TaskAttribute Entity
    public const TASK_ATTRIBUTE_SETTINGS = 'task_attribute_settings';
    // CRUD for Unit Entity
    public const UNIT_SETTINGS = 'unit_settings';
    // CRUD for SystemSettings Entity
    public const SYSTEM_SETTINGS = 'system_settings';
    // CRUD for IMAP Entity
    public const IMAP_SETTINGS = 'imap_settings';
    // CRUD for SMTP Entity
    public const SMTP_SETTINGS = 'smtp_settings';

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