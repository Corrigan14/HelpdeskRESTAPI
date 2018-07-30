<?php

namespace API\TaskBundle\Security;

/**
 * Class UserRoleAclOptions
 *
 * @package API\TaskBundle\Security
 */
class UserRoleAclOptions
{
    const LOGIN_TO_SYSTEM = 'login_to_system';

    // User can CRUD PUBLIC Filters
    const SHARE_FILTERS = 'share_filters';
    // User can CRUD PUBLIC Filters in Projects
    const PROJECT_SHARED_FILTERS = 'project_shared_filters';
    // User can CRUD Filters checked like REPORT
    const REPORT_FILTERS = 'report_filters';

    // User can CRUD PUBLIC Tags
    const SHARE_TAGS = 'share_tags';

    // User can CRUD Projects
    const CREATE_PROJECTS = 'create_projects';

    // User can Sent Emails from Comments
    const SENT_EMAILS_FROM_COMMENTS = 'sent_emails_from_comments';

    // User can create Tasks
    const CREATE_TASKS = 'create_tasks';
    // User can create Tasks in all Projects
    const CREATE_TASKS_IN_ALL_PROJECTS = 'create_tasks_in_all_projects';
    // User can update All Tasks
    const UPDATE_ALL_TASKS = 'update_all_tasks';

    // CRUD for User Entity
    const USER_SETTINGS = 'user_settings';
    // CRUD for UserRole Entity
    const USER_ROLE_SETTINGS = 'user_role_settings';
    // CRUD for CompanyAttribute Entity
    const COMPANY_ATTRIBUTE_SETTINGS = 'company_attribute_settings';
    // CRUD for Company Entity
    const COMPANY_SETTINGS = 'company_settings';
    // CRUD for Status Entity
    const STATUS_SETTINGS = 'status_settings';
    // CRUD for TaskAttribute Entity
    const TASK_ATTRIBUTE_SETTINGS = 'task_attribute_settings';
    // CRUD for Unit Entity
    const UNIT_SETTINGS = 'unit_settings';
    // CRUD for SystemSettings Entity
    const SYSTEM_SETTINGS = 'system_settings';
    // CRUD for IMAP Entity
    const IMAP_SETTINGS = 'imap_settings';
    // CRUD for SMTP Entity
    const SMTP_SETTINGS = 'smtp_settings';

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