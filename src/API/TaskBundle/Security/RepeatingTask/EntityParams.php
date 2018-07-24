<?php

namespace API\TaskBundle\Security\RepeatingTask;


/**
 * Class EntityParams
 *
 * @package API\TaskBundle\Security\RepeatingTask
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
            'startAt',
            'interval',
            'intervalLength',
            'repeatsNumber'
        ];
    }

}