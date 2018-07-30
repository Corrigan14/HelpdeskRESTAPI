<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\RepeatingTask\EntityParams;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ActivationController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class ActivationController extends ApiBaseController
{
    /**
     * @ApiDoc(
     *  description="Inactivate Repeating Task Entity",
     *  requirements={
     *     {
     *       "name"="repeatingTaskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a processed object"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="is_active param of Entity was successfully changed to inactive: 0",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $repeatingTaskId
     * @return Response
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function inactivateAction(int $repeatingTaskId):Response
    {
        $locationURL = $this->generateUrl('repeating_task_inactivate', ['repeatingTaskId' => $repeatingTaskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $dataValidation = $this->validateData($repeatingTaskId);
        if (false === $dataValidation['status']) {
            $response->setStatusCode($dataValidation['errorCode'])
                ->setContent(json_encode($dataValidation['errorMessage']));
            return $response;
        }

        /** @var RepeatingTask $repeatingTask */
        $repeatingTask = $dataValidation['repeatingTask'];
        $repeatingTask->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($repeatingTask);
        $this->getDoctrine()->getManager()->flush();

        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
                 ->setContent(json_encode(['message' => StatusCodesHelper::UNACITVATE_MESSAGE]));

        return $response;
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 4,
     *           "title": "Monthly repeating task",
     *           "startAt": 1530385892,
     *           "interval": "month",
     *           "intervalLength": "2",
     *           "repeatsNumber": 10,
     *           "createdAt": 1530385892,
     *           "updatedAt": 1530385892,
     *           "is_active": false,
     *           "task":
     *           {
     *               "id": 8996,
     *               "title": "Task 1"
     *           }
     *        },
     *        "_links":
     *        {
     *           "update": "/api/v1/task-bundle/repeating-tasks/22",
     *           "delete": "/api/v1/task-bundle/repeating-tasks/22",
     *           "inactivate": "/api/v1/task-bundle/repeating-tasks/22/inactivate",
     *           "restore": "/api/v1/task-bundle/repeating-tasks/22/restore"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update Repeating Task Entity",
     *  requirements={
     *     {
     *       "name"="repeatingTaskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a Repeating task"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\RepeatingTask"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  }
     * )
     *
     * @param int $repeatingTaskId
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(int $repeatingTaskId): Response
    {
        $locationURL = $this->generateUrl('repeating_task_restore', ['repeatingTaskId' => $repeatingTaskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $dataValidation = $this->validateData($repeatingTaskId);
        if (false === $dataValidation['status']) {
            $response->setStatusCode($dataValidation['errorCode'])
                ->setContent(json_encode($dataValidation['errorMessage']));
            return $response;
        }

        /** @var RepeatingTask $repeatingTask */
        $repeatingTask = $dataValidation['repeatingTask'];
        $repeatingTask->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($repeatingTask);
        $this->getDoctrine()->getManager()->flush();

        $repeatingTaskArray = $this->get('repeating_task_get_service')->getRepeatingTask($repeatingTask->getId());
        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode($repeatingTaskArray));

        return $response;
    }

    /**
     * @param int $repeatingTaskId
     * @return array|bool
     * @throws \LogicException
     */
    private function validateData(int $repeatingTaskId): array
    {
        $repeatingTask = $this->getDoctrine()->getRepository('APITaskBundle:RepeatingTask')->find($repeatingTaskId);
        if (!$repeatingTask instanceof RepeatingTask) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::NOT_FOUND_CODE,
                'errorMessage' => 'Repeating task with requested Id does not exist!'
            ];

        }

        // User can see a repeating task if he is ADMIN or repeating task is related to the task where he has a permission to create a Task
        if (!$this->checkUpdatePermission($repeatingTask->getTask())) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::ACCESS_DENIED_CODE,
                'errorMessage' => StatusCodesHelper::ACCESS_DENIED_MESSAGE
            ];
        }

        return [
            'status' => true,
            'repeatingTask' => $repeatingTask
        ];
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \LogicException
     */
    private function checkUpdatePermission(Task $task): bool
    {
        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $tasksProject = $task->getProject()->getId();
        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $loggedUser,
            'project' => $tasksProject
        ]);

        $options = [
            'userHasProject' => $userHasProject
        ];

        if (!$this->get('repeating_task_voter')->isGranted(VoteOptions::UPDATE_REPEATING_TASK, $options)) {
            return false;
        }
        return true;
    }
}
