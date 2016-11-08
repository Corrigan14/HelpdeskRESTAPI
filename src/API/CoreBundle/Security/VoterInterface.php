<?php

namespace API\CoreBundle\Security;

/**
 * Interface VoterInterface
 * @package API\CoreBundle\Security
 */
interface VoterInterface
{
    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string   $action
     *
     * @param bool|int $targetId
     *
     * @return bool
     */
    public function isGranted($action , $targetId = false);
}