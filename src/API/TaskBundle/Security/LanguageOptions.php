<?php

namespace API\TaskBundle\Security;

/**
 * Class LanguageOptions
 *
 * @package API\TaskBundle\Security
 */
class LanguageOptions
{
    const ENGLISH = 'AJ';
    const SLOVAK = 'SJ';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}