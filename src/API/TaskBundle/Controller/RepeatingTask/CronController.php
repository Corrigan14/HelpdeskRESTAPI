<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Entity\Task;
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
     *              "important": true,
     *              "work_type": "programovanie www",
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
        $createdTasks = array_merge($dailyTasks,$weeklyTasks,$monthlyTasks,$yearlyTasks);

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
        $lastRepeat = $repeatingTask->getLastRepeat();
        $intervalLength = $repeatingTask->getIntervalLength();

        // doplnit podmienku pre interval: opakovat, ak datum kedy by som mala zopakovat ulohu ($lastRepeat + $intervalLength*24hodin) je mensi ako aktualny datum
        if (null === $lastRepeat) {
            return $this->createChildTask($repeatingTask->getTask(), $repeatingTask, $actualTime);
        }

        return [];
    }

    /**
     * @param Task $parentTask
     * @param RepeatingTask $repeatingTask
     * @param \DateTime $actualTime
     * @return array
     * @throws \LogicException
     */
    private function createChildTask(Task $parentTask, RepeatingTask $repeatingTask, \DateTime $actualTime): array
    {
        $newStatus = $this->getDoctrine()->getRepository('APITaskBundle:Status')->findOneBy([
            'function' => StatusFunctionOptions::NEW_TASK,
            'default' => true
        ]);

        $childTask = new Task();
        $childTask->setTitle($parentTask->getTitle());
        $childTask->setDescription($parentTask->getDescription());
        $childTask->setWorkType($parentTask->getWorkType());
        $childTask->setImportant($parentTask->getImportant());
        $childTask->setCreatedBy($parentTask->getCreatedBy());
        $childTask->setRequestedBy($parentTask->getRequestedBy());
        $childTask->setProject($parentTask->getProject());
        $childTask->setCompany($parentTask->getCompany());
        $childTask->setStatus($newStatus);
        $childTask->setParentTask($parentTask);

        $parentTask->addChildTask($childTask);

        $oldRepeatsNumber = $repeatingTask->getAlreadyRepeated();
        $newRepeatsNumber = $oldRepeatsNumber + 1;
        $repeatingTask->setLastRepeat($actualTime);
        $repeatingTask->setAlreadyRepeated($newRepeatsNumber);
        
        $this->getDoctrine()->getManager()->persist($childTask);
        $this->getDoctrine()->getManager()->persist($parentTask);
        $this->getDoctrine()->getManager()->persist($repeatingTask);
        $this->getDoctrine()->getManager()->flush();

        return [
            'parentId' => $parentTask->getId(),
            'id' => $childTask->getId(),
            'title' => $childTask->getTitle(),
            'description' => $childTask->getDescription(),
            'important' => $childTask->getImportant(),
            'work_type' => $childTask->getWorkType(),
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
