<?php

namespace Igsem\APIBundle\Controller;


use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use API\TaskBundle\Security\VoteOptions;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiBaseController
 *
 * @package API\CoreBundle\Controller
 */
abstract class ApiBaseController extends Controller
{

    /**
     * @param     $data
     * @param int $statusCode
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    protected function createApiResponse($data, $statusCode = 200): Response
    {
        $json = $this->serialize($data);

        return new Response($json, $statusCode, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * @param        $data
     * @param string $format
     *
     * @return mixed
     */
    protected function serialize($data, $format = 'json')
    {
        return $this->get('jms_serializer')
            ->serialize($data, $format);
    }

    /**
     * Return's 201 code if create = true, else 200
     *
     * @param bool $create
     *
     * @return int
     */
    protected function getCreateUpdateStatusCode($create): int
    {
        if ($create) {
            return StatusCodesHelper::CREATED_CODE;
        }
        return StatusCodesHelper::SUCCESSFUL_CODE;

    }

    /**
     * @param array $tasksArray
     * @return array
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    protected function addCanEditParamToEveryTask(array $tasksArray): array
    {
        $tasksModified = [];

        foreach ($tasksArray['data'] as $task) {
            $taskEntityFromDb = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($task['id']);

            if ($this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $taskEntityFromDb)) {
                $task['canEdit'] = true;
            } else {
                $task['canEdit'] = false;
            }
            $tasksModified['data'][] = $task;
        }

        $tasksModified['_links'] = $tasksArray['_links'];
        $tasksModified['total'] = $tasksArray['total'];
        $tasksModified['page'] = $tasksArray['page'];
        $tasksModified['numberOfPages'] = $tasksArray['numberOfPages'];

        return $tasksModified;
    }

}