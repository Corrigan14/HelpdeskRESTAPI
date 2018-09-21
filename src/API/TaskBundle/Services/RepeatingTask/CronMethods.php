<?php

namespace API\TaskBundle\Services\RepeatingTask;

use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\TaskHasAttachment;
use API\TaskBundle\Entity\TaskSubtask;
use API\TaskBundle\Security\RepeatingTask\IntervalOptions;
use API\TaskBundle\Security\StatusFunctionOptions;
use Doctrine\ORM\EntityManager;

/**
 * Class CronMethods
 * @package API\TaskBundle\Services\RepeatingTask
 */
class CronMethods
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
     * @param array $repeatingTasks
     * @return bool|array
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \LogicException
     */
    public function createTasks(array $repeatingTasks)
    {
        $actualTime = new \DateTime();
        $createdTasks = [];
        $dailyTasks = [];
        $weeklyTasks = [];
        $monthlyTasks = [];
        $yearlyTasks = [];

        if (0 === \count($repeatingTasks)) {
            return false;
        }

        /** @var RepeatingTask $repeatingTask */
        foreach ($repeatingTasks as $repeatingTask) {
            $interval = $repeatingTask->getInterval();
            if (IntervalOptions::DAY === $interval) {
                $dailyTasks[] = $this->processRepeatedTask($repeatingTask, $actualTime, 'day');
            }
            if (IntervalOptions::WEEK === $interval) {
                $weeklyTasks[] = $this->processRepeatedTask($repeatingTask, $actualTime, 'week');
            }
            if (IntervalOptions::MONTH === $interval) {
                $monthlyTasks[] = $this->processRepeatedTask($repeatingTask, $actualTime, 'month');
            }
            if (IntervalOptions::YEAR === $interval) {
                $yearlyTasks[] = $this->processRepeatedTask($repeatingTask, $actualTime, 'year');
            }
        }
        if (array_filter($dailyTasks)) {
            $createdTasks = array_merge($createdTasks, $dailyTasks);
        }
        if (array_filter($weeklyTasks)) {
            $createdTasks = array_merge($createdTasks, $weeklyTasks);
        }
        if (array_filter($monthlyTasks)) {
            $createdTasks = array_merge($createdTasks, $monthlyTasks);
        }
        if (array_filter($yearlyTasks)) {
            $createdTasks = array_merge($createdTasks, $yearlyTasks);
        }

        if (true === !array_filter($createdTasks)) {
            return false;
        }
        return $createdTasks;
    }


    /**
     * @param RepeatingTask $repeatingTask
     * @param \DateTime $actualTime
     * @param string $interval
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \LogicException
     */
    private function processRepeatedTask(RepeatingTask $repeatingTask, \DateTime $actualTime, string $interval): array
    {
        $lastRepeat = $repeatingTask->getLastRepeatDateTime();
        if (null === $lastRepeat) {
            return $this->createChildTask($repeatingTask->getTask(), $repeatingTask, $actualTime);
        }

        $intervalLength = $repeatingTask->getIntervalLength();
        $repeatingTime = $lastRepeat->modify('+' . $intervalLength . $interval);
        if ($repeatingTime < $actualTime) {
            return $this->createChildTask($repeatingTask->getTask(), $repeatingTask, $actualTime);
        }

        return [];
    }

    /**
     * @param Task $parentTask
     * @param RepeatingTask $repeatingTask
     * @param \DateTime $repeatingTime
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMException
     */
    private function createChildTask(Task $parentTask, RepeatingTask $repeatingTask, \DateTime $repeatingTime): array
    {
        $newStatus = $this->em->getRepository('APITaskBundle:Status')->findOneBy([
            'function' => StatusFunctionOptions::NEW_TASK,
            'default' => true
        ]);

        $childTask = new Task();
        $childTask->setTitle($parentTask->getTitle());
        $childTask->setDescription($parentTask->getDescription());
        $childTask->setWorkType($parentTask->getWorkType());
        $childTask->setWork($parentTask->getWork());
        $childTask->setImportant($parentTask->getImportant());
        $childTask->setCreatedBy($parentTask->getCreatedBy());
        $childTask->setRequestedBy($parentTask->getRequestedBy());
        $childTask->setProject($parentTask->getProject());
        $childTask->setCompany($parentTask->getCompany());
        $childTask->setStatus($newStatus);
        $childTask->setParentTask($parentTask);

        //Tags
        $parentTaskTags = $parentTask->getTags();
        foreach ($parentTaskTags as $tag) {
            $childTask->addTag($tag);
            $this->em->persist($childTask);
        }

        //Followers
        $parentTaskFollowers = $parentTask->getFollowers();
        foreach ($parentTaskFollowers as $follower) {
            $childTask->addFollower($follower);
            $this->em->persist($childTask);
        }

        //Sub-tasks
        $parentTaskSubtasks = $parentTask->getSubtasks();
        /** @var TaskSubtask $parentSubtask */
        foreach ($parentTaskSubtasks as $parentSubtask) {
            $childSubtask = new TaskSubtask();
            $childSubtask->setTask($childTask);
            $childSubtask->setTitle($parentSubtask->getTitle());
            $childSubtask->setDone(false);
            $childSubtask->setCreatedBy($parentSubtask->getCreatedBy());
            $this->em->persist($childSubtask);
        }

        //Task Has Attachments
        $parentTaskAttachments = $parentTask->getTaskHasAttachments();
        foreach ($parentTaskAttachments as $taskHasAttachmentParent) {
            $taskHasAttachmentChild = new TaskHasAttachment();
            $taskHasAttachmentChild->setTask($childTask);
            $taskHasAttachmentChild->setSlug($taskHasAttachmentParent->getSlug());
            $this->em->persist($taskHasAttachmentChild);
        }

        //Task Has Assigners
        $parentTaskAssigners = $parentTask->getTaskHasAssignedUsers();
        /** @var TaskHasAssignedUser $taskHasAssignerParent */
        foreach ($parentTaskAssigners as $taskHasAssignerParent) {
            $taskHasAssignerChild = new TaskHasAssignedUser();
            $taskHasAssignerChild->setTask($childTask);
            $taskHasAssignerChild->setStatus($newStatus);
            $taskHasAssignerChild->setUser($taskHasAssignerParent->getUser());
            $taskHasAssignerChild->setActual($taskHasAssignerParent->getActual());
            $taskHasAssignerChild->setGps($taskHasAssignerParent->getGps());
            $this->em->persist($taskHasAssignerChild);
        }

        $parentTask->addChildTask($childTask);

        $oldRepeatsNumber = $repeatingTask->getAlreadyRepeated();
        $newRepeatsNumber = $oldRepeatsNumber + 1;
        $repeatingTask->setLastRepeat($repeatingTime);
        $repeatingTask->setAlreadyRepeated($newRepeatsNumber);

        $this->em->persist($childTask);
        $this->em->persist($parentTask);
        $this->em->persist($repeatingTask);
        $this->em->flush();

        return [
            'parentId' => $parentTask->getId(),
            'id' => $childTask->getId(),
            'title' => $childTask->getTitle(),
            'createdAt' => $childTask->getCreatedAt(),
            'updatedAt' => $childTask->getUpdatedAt(),
            'createdBy' => [
                'id' => $childTask->getCreatedBy()->getId(),
                'username' => $childTask->getCreatedBy()->getUsername(),
                'email' => $childTask->getCreatedBy()->getEmail()
            ],
            'requestedBy' => [
                'id' => $childTask->getRequestedBy()->getId(),
                'username' => $childTask->getRequestedBy()->getUsername(),
                'email' => $childTask->getRequestedBy()->getEmail()
            ]
        ];
    }
}