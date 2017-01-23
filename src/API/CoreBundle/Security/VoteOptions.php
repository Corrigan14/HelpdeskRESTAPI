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
    const CREATE_USER_WITH_USER_ROLE = 'create_user_with_user_role';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}