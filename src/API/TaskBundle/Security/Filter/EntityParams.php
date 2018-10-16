<?php

namespace API\TaskBundle\Security\Filter;


/**
 * Class EntityParams
 *
 * @package API\TaskBundle\Security\Filter
 */
class EntityParams
{
    /**
     * @return array
     */
    public static function getAllowedEntityParams(): array
    {
        return [
            'title',
            'public',
            'filter',
            'report',
            'is_active',
            'default',
            'icon_class',
            'order',
            'columns',
            'columns_task_attributes'
        ];
    }

}