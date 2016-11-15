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

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}