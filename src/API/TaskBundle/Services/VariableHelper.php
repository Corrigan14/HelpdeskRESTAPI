<?php

namespace API\TaskBundle\Services;

/**
 * Class VariableHelper
 *
 * @package API\TaskBundle\Services
 */
class VariableHelper
{
    // Types of attributes used f.i. in Company attributes, Task attributes
    const INPUT = 'input';
    const TEXT_AREA = 'text_area';
    const SIMPLE_SELECT = 'simple_select';
    const MULTI_SELECT = 'multi_select';
    const DATE = 'date';
    const DECIMAL_NUMBER = 'decimal_number';
    const INTEGER_NUMBER = 'integer_number';
    const CHECKBOX = 'checkbox';

    // Allowed keys in filter array
    public static $allowedKeysInFilter = [
        'taskGlobalStatus.id',
        'taskHasAssignedUsers.actual',
        'project.id',
        'createdBy.id',
        'requestedBy.id',
        'taskCompany.id',
        'assignedUser.id',
        'tags.id',
        'followers.id',
        'task.createdAt',
        'task.startedAt',
        'task.deadline',
        'task.closedAt',
        'project.is_active',
        'task.project',
        'projectCreator.id',
        'thau.user',
        'task.tags',
        'task.important'
    ];

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getConstants():array
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}