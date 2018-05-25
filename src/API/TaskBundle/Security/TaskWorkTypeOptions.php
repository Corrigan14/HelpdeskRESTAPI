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
    const VZDIALENA_PODPORA = 'vzdialena podpora';
    const SERVIS_IT = 'servis IT';
    const SERVIS_SERVEROV = 'servis serverov';
    const PROGRAMOVANIE_WWW = 'programovanie www';
    const INSTALACIE_KLIENTSKEHO_OS = 'instalacie klientskeho os';
    const BUG_REKLAMACIA = 'bug reklamacia';
    const NAVRH = 'navrh';
    const MATERIAL = 'material';
    const CENOVA_PONUKA = 'cenova ponuka';
    const ADMINISTRATIVA = 'administrativa';
    const KONZULTACIA = 'konzultacia';
    const REFAKTURACIA = 'refakturacia';
    const TESTOVANIE = 'testovanie';


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