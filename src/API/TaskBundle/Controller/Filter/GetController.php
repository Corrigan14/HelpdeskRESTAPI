<?php

namespace API\TaskBundle\Controller\Filter;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GetController
 *
 * @package API\TaskBundle\Controller\Filter
 */
class GetController extends ApiBaseController
{
    /**
     *  ### Response ###
     *      {
     *         "data":
     *         {
     *             "id": 145,
     *             "title": 145,
     *             "public": true,
     *             "filter":
     *             {
     *                "status": "238,239",
     *                "assigned": "not,current-user"
     *             },
     *             "report": false,
     *             "is_active": true,
     *             "default": true,
     *             "icon_class": "&#xE88A;"
     *             "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             },
     *             "project": null,
     *             "columns":
     *             [
     *                "title",
     *                "creator",
     *                "company",
     *                "assigned",
     *                "createdTime",
     *                "deadlineTime",
     *                "status"
     *             ],
     *             "columns_task_attributes":
     *             [
     *                205,
     *                206
     *             ]
     *         },
     *        "_links":
     *        {
     *          "update filter": "/api/v1/task-bundle/filters/15",
     *          "inactivate": "/api/v1/task-bundle/filters/15/inactivate",
     *          "restore": "/api/v1/task-bundle/filters/15/restore",
     *          "set logged users remembered filter": "/api/v1/task-bundle/filters/15/user-remembered",
     *          "get logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered",
     *          "remove logged users remembered filter": "/api/v1/task-bundle/filters/user-remembered/delete"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Filter entity",
     *  requirements={
     *     {
     *       "name"="id",
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
     *  output="API\TaskBundle\Entity\Filter",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     * @return Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);

        if (!$filter instanceof Filter) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));

            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::SHOW_FILTER, $filter)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

        $filterArray = $this->get('filter_get_service')->getFilterResponse($id);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($filterArray));

        return $response;
    }
}