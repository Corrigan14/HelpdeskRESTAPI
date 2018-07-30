<?php

namespace API\TaskBundle\Security;

/**
 * Class LanguageOptions
 *
 * @package API\TaskBundle\Security
 */
class LanguageOptions
{
    public const ENGLISH = 'AJ';
    public const SLOVAK = 'SJ';

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