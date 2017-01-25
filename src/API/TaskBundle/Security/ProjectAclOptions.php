<?php

namespace API\TaskBundle\Security;

/**
 * Class ProjectAclOptions
 *
 * @package API\TaskBundle\Security
 */
class ProjectAclOptions
{
    const CREATE_TASK_IN_PROJECT = 'create_task_in_project';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}