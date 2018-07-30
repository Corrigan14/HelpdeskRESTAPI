<?php

namespace API\TaskBundle\Services\RepeatingTask;

use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Security\RepeatingTask\IntervalOptions;
use API\TaskBundle\Security\RepeatingTaskIntervalOptions;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UpdateMethods
 * @package API\TaskBundle\Services\RepeatingTask
 */
class UpdateMethods
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * UserService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $errors
     * @param RepeatingTask $repeatingTask
     * @param $requestData
     * @return Response|bool
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     */
    public function updateRepeatingTask($errors, RepeatingTask $repeatingTask, $requestData)
    {
        if (false !== $errors) {
            $response['error'] = $errors;
            return $response;
        }

        // Interval param has to match to the defined choices
        $allowedIntervals = IntervalOptions::getConstants();
        if (isset($requestData['interval']) && !\in_array($requestData['interval'], $allowedIntervals, true)) {
            $response['error'] =  $requestData['interval'] . ' is not allowed parameter for a Repeating Tasks INTERVAL! Allowed are: ' . implode(',', $allowedIntervals);
            return $response;
        }

        $this->em->persist($repeatingTask);
        $this->em->flush();
        return true;
    }

}