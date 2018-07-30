<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\RepeatingTask;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DeleteController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class DeleteController extends ApiBaseController
{
    /**
     * @ApiDoc(
     *  description="Delete Repeating Task Entity",
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
     *      204 ="Entity was successfully deleted",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  })
     *
     * @param int $repeatingTaskId
     * @return Response
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(int $repeatingTaskId):Response
    {
        $locationURL = $this->generateUrl('repeating_task_delete', ['repeatingTaskId' => $repeatingTaskId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $dataValidation = $this->validateData($repeatingTaskId);
        if (false === $dataValidation['status']) {
            $response->setStatusCode($dataValidation['errorCode'])
                ->setContent(json_encode($dataValidation['errorMessage']));

            return $response;
        }

        /** @var RepeatingTask $repeatingTask */
        $repeatingTask = $dataValidation['repeatingTask'];
        $this->getDoctrine()->getManager()->remove($repeatingTask);
        $this->getDoctrine()->getManager()->flush();

        $response->setStatusCode(StatusCodesHelper::DELETED_CODE)
                 ->setContent(json_encode(['message' => StatusCodesHelper::DELETED_MESSAGE]));

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
        if (!$this->checkDeletePermission($repeatingTask->getTask())) {
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
    private function checkDeletePermission(Task $task): bool
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

        if (!$this->get('repeating_task_voter')->isGranted(VoteOptions::DELETE_REPEATING_TASK, $options)) {
            return false;
        }
        return true;
    }
}
