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

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}