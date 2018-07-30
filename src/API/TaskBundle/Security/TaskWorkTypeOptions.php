<?php

namespace API\TaskBundle\Security;

/**
 * Class TaskWorkTypeOptions
 *
 * @package API\TaskBundle\Security
 */
class TaskWorkTypeOptions
{
    // Attributes available for a work type param
    public const VZDIALENA_PODPORA = 'vzdialena podpora';
    public const SERVIS_IT = 'servis IT';
    public const SERVIS_SERVEROV = 'servis serverov';
    public const PROGRAMOVANIE_WWW = 'programovanie www';
    public const INSTALACIE_KLIENTSKEHO_OS = 'instalacie klientskeho os';
    public const BUG_REKLAMACIA = 'bug reklamacia';
    public const NAVRH = 'navrh';
    public const MATERIAL = 'material';
    public const CENOVA_PONUKA = 'cenova ponuka';
    public const ADMINISTRATIVA = 'administrativa';
    public const KONZULTACIA = 'konzultacia';
    public const REFAKTURACIA = 'refakturacia';
    public const TESTOVANIE = 'testovanie';


    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getConstants(): array
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}