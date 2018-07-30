<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GetController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class GetController extends ApiBaseController
{

    /**
     *  ### Response ###
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
     *           "put": "/api/v1/entityName/id",
     *           "patch": "/api/v1/entityName/id",
     *           "delete": "/api/v1/entityName/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns Repeating Task Entity",
     *  requirements={
     *     {
     *       "name"="repeatingTaskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output="API\TaskBundle\Entity\RepeatingTask",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $repeatingTaskId
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function getAction(int $repeatingTaskId): Response
    {
        $locationURL = $this->generateUrl('repeating_task', ['repeatingTaskId' => $repeatingTaskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $repeatingTask = $this->getDoctrine()->getRepository('APITaskBundle:RepeatingTask')->find($repeatingTaskId);
        if (!$repeatingTask instanceof RepeatingTask) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Repeating Task with requested Id does not exist!']));

            return $response;
        }

        // User can see a repeating task if he is ADMIN or repeating task is related to the task where he has a permission to view it
        if (!$this->checkViewPermission($repeatingTask)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $repeatingTaskArray = $this->get('repeating_task_get_service')->getRepeatingTask($repeatingTaskId);
        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode($repeatingTaskArray));

        return $response;
    }

    /**
     * @param RepeatingTask $repeatingTask
     * @return bool
     * @throws \LogicException
     */
    private function checkViewPermission(RepeatingTask $repeatingTask): bool
    {
        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $options = [
            'allowedTasksId' => $this->get('task_service')->getUsersViewTasksId($loggedUser),
            'repeatingTasksTaskId' => $repeatingTask->getTask()->getId()
        ];

        if (!$this->get('repeating_task_voter')->isGranted(VoteOptions::VIEW_REPEATING_TASK, $options)) {
            return false;
        }

        return true;
    }
}
