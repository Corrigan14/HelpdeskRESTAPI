<?php

namespace API\TaskBundle\Controller\RepeatingTask;

use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
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
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function cronAction(): Response
    {
        $locationURL = $this->generateUrl('repeating_task_cron');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $repeatingTasks = $this->getDoctrine()->getRepository('APITaskBundle:RepeatingTask')->getCronAvailableEntities();

        $createdTasks['data'] = $this->get('repeating_task_cron_service')->createTasks($repeatingTasks);
        if (!$createdTasks['data']) {
            $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::NO_CRON_TASKS_CREATED]));

            return $response;
        }

        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode($createdTasks));

        return $response;
    }
}
