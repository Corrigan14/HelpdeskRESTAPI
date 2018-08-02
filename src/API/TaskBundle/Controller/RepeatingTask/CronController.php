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
 * Class CronController
 * @package API\TaskBundle\Controller\RepeatingTask
 */
class CronController extends ApiBaseController
{
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
     *        }
     *      }
     *
     * @ApiDoc(
     *  description="CRON call for repeating tasks. Returns a list of create Tasks or failed message.",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request"
     *  }
     *  )
     *
     * @return Response
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function cronAction(): Response
    {
        $locationURL = $this->generateUrl('repeating_task_cron');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $repeatingTasks = $this->getDoctrine()->getRepository('APITaskBundle:RepeatingTask')->getCronAvailableEntities(new \DateTime());

        dump($repeatingTasks);
        return $response;
    }
}
