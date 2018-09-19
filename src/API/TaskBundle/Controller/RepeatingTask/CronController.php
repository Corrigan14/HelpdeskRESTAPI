<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\TaskHasAttachment;
use API\TaskBundle\Entity\TaskSubtask;
use API\TaskBundle\Security\RepeatingTask\EntityParams;
use API\TaskBundle\Security\RepeatingTask\IntervalOptions;
use API\TaskBundle\Security\StatusFunctionOptions;
use API\TaskBundle\Security\StatusOptions;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class CronController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class CronController extends ApiBaseController
{
    /**
     * ### Response ###
     *      {
     *        "data":
     *        [
     *          {
     *              "parentId": 5996,
     *              "id": 5999,
     *              "title": "Task 2999",
     *              "description": "Description of Users Task 2999",
     *              "createdAt": 1537125154,
     *              "updatedAt": 1537125154,
     *              "createdBy":
     *              {
     *                  "id": 6,
     *                  "username": "manager",
     *                  "email": "manager@manager.sk"
     *              },
     *              "requestedBy":
     *              {
     *                  "id": 6,
     *                  "username": "manager",
     *                  "email": "manager@manager.sk"
     *              }
     *          }
     *        ]
     *      }
     *
     * @ApiDoc(
     *  description="CRON call for repeating tasks. Returns a list of created Tasks or failed message.",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request"
     *  }
     *  )
     *
     * @return Response
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function cronAction(): Response
    {
        $locationURL = $this->generateUrl('repeating_task_cron');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $repeatingTasks = $this->getDoctrine()->getRepository('APITaskBundle:RepeatingTask')->getCronAvailableEntities();

        $createdTasks['data'] = $this->createTasks($repeatingTasks);
        if (!$createdTasks['data']) {
            $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::NO_CRON_TASKS_CREATED]));

            return $response;
        }

        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode($createdTasks));

        return $response;
    }

    /**
     * @param $repeatingTasks
     * @return bool|array
     * @throws \LogicException
     */
    private function createTasks($repeatingTasks)
    {
        $actualTime = new \DateTime();
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
                $dailyTasks[] = $this->processDailyRepeatedTask($repeatingTask, $actualTime);
            }
        }
        $createdTasks = array_merge($dailyTasks, $weeklyTasks, $monthlyTasks, $yearlyTasks);

        if (0 === \count($createdTasks)) {
            return false;
        }
        return $createdTasks;
    }

    /**
     * @param RepeatingTask $repeatingTask
     * @param \DateTime $actualTime
     * @return array
     * @throws \LogicException
     */
    private function processDailyRepeatedTask(RepeatingTask $repeatingTask, \DateTime $actualTime): array
    {
        $lastRepeat = $repeatingTask->getLastRepeatDateTime();
        if (null === $lastRepeat) {
            return $this->createChildTask($repeatingTask->getTask(), $repeatingTask, $actualTime);
        }

        $intervalLength = $repeatingTask->getIntervalLength();
        $repeatingTime = $lastRepeat->modify('+' . $intervalLength . 'day');
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
     * @throws \LogicException
     */
    private function createChildTask(Task $parentTask, RepeatingTask $repeatingTask, \DateTime $repeatingTime): array
    {
        $newStatus = $this->getDoctrine()->getRepository('APITaskBundle:Status')->findOneBy([
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
            $this->getDoctrine()->getManager()->persist($childTask);
        }

        //Followers
        $parentTaskFollowers = $parentTask->getFollowers();
        foreach ($parentTaskFollowers as $follower) {
            $childTask->addFollower($follower);
            $this->getDoctrine()->getManager()->persist($childTask);
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
            $this->getDoctrine()->getManager()->persist($childSubtask);
        }

        //Task Has Attachments
        $parentTaskAttachments = $parentTask->getTaskHasAttachments();
        foreach ($parentTaskAttachments as $taskHasAttachmentParent) {
            $taskHasAttachmentChild = new TaskHasAttachment();
            $taskHasAttachmentChild->setTask($childTask);
            $taskHasAttachmentChild->setSlug($taskHasAttachmentParent->getSlug());
            $this->getDoctrine()->getManager()->persist($taskHasAttachmentChild);
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
            $this->getDoctrine()->getManager()->persist($taskHasAssignerChild);
        }

        $parentTask->addChildTask($childTask);

        $oldRepeatsNumber = $repeatingTask->getAlreadyRepeated();
        $newRepeatsNumber = $oldRepeatsNumber + 1;
        $repeatingTask->setLastRepeat($repeatingTime);
        $repeatingTask->setAlreadyRepeated($newRepeatsNumber);

        $this->getDoctrine()->getManager()->persist($childTask);
        $this->getDoctrine()->getManager()->persist($parentTask);
        $this->getDoctrine()->getManager()->persist($repeatingTask);
        $this->getDoctrine()->getManager()->flush();

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
