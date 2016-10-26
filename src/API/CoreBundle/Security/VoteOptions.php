<?php
/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 10/25/16
 * Time: 1:43 PM
 */

namespace API\CoreBundle\Security;

/**
 * Class VoteOptions
 *
 * @package API\CoreBundle\Security
 */
class VoteOptions
{
    //CRUD
    const CREATE_USER = 'create_user';
    const SHOW_USER = 'read_user';
    const UPDATE_USER = 'update_user';
    const DELETE_USER = 'delete_user';
    const LIST_USERS = 'list_users';
}