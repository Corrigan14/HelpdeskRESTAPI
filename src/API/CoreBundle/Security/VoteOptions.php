<?php

namespace API\CoreBundle\Security;

/**
 * Class VoteOptions
 *
 * @package API\CoreBundle\Security
 */
class VoteOptions
{
    //USER CRUD
    const CREATE_USER = 'create_user';
    const SHOW_USER = 'read_user';
    const UPDATE_USER = 'update_user';
    const DELETE_USER = 'delete_user';
    const LIST_USERS = 'list_users';

    //COMPANY CRUD
    const CREATE_COMPANY = 'create_company';
    const SHOW_COMPANY = 'read_company';
    const UPDATE_COMPANY = 'update_company';
    const DELETE_COMPANY = 'delete_company';
    const LIST_COMPANIES = 'list_companies';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}