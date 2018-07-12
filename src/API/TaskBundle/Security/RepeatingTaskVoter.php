<?php

namespace API\TaskBundle\Security;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Security\ApiBaseVoter;

/**
 * Class RepeatingTaskVoter
 *
 * @package API\TaskBundle\Security
 */
class RepeatingTaskVoter extends ApiBaseVoter
{
    /** @var  User */
    private $user;

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $action
     * @param array $options
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isGranted($action, array $options): bool
    {
        $this->user = $this->token->getUser();

        if (!$this->user instanceof User) {
            // the user must be logged in. If not, deny access.
            return false;
        }

        switch ($action) {
            case VoteOptions::VIEW_REPEATING_TASK:
                return $this->canView($options);
            default:
                return false;
        }
    }

    /**
     * User can view a repeating task if: he is an admin, or repeating task is related to the task where he has a permission to view it
     *
     * @param array $options
     * @return bool
     */
    private function canView(array $options): bool
    {
        if ($this->decisionManager->decide($this->token, ['ROLE_ADMIN'])) {
            return true;
        }

        $allowedTasksId = $options['allowedTasksId'];
        $repeatingTasksTaskId = $options['repeatingTasksTaskId'];
        if (!\in_array($repeatingTasksTaskId, $allowedTasksId, true)) {
            return false;
        }

        return true;
    }
}