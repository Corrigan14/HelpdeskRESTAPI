<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FilterController
 *
 * @package API\TaskBundle\Controller
 */
class FilterController extends ApiBaseController
{









    /**
     * @ApiDoc(
     *  description="Set Filter Entity as REMEMBERED for a logged user.",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  })
     *
     * @param $id
     * @return Response
     * @throws \LogicException
     */
    public function setUsersRememberedFilterAction($id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_set_user_remembered', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        if (!$filter instanceof Filter) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));
            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::SET_REMEMBERED_FILTER, $filter)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $loggedUser->setRememberedFilter($filter);
        $filter->addRememberUser($loggedUser);
        $this->getDoctrine()->getManager()->persist($loggedUser);
        $this->getDoctrine()->getManager()->persist($filter);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => 'Filter was successfully set as remembered']));
        return $response;

    }

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
     *             "project":
     *             {
     *                "id": 2575,
     *                "title": "INBOX",
     *             },
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
     *  description="Returns Logged User's Remembered Filter. If he does not have one, EMPTY value is returned.",
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
     *      401 ="Unauthorized request"
     *  }
     * )
     *
     * @return bool|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function getUsersRememberedFilterAction(): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_get_user_remembered');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $savedFilter = $loggedUser->getRememberedFilter();

        if ($savedFilter instanceof Filter) {
            $filterArray = $this->get('filter_service')->getFilterResponse($savedFilter->getId());

            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode($filterArray));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode(null));
        }

        return $response;
    }

    /**
     * @ApiDoc(
     *  description="Delete Users remembered filter",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="Entity does not exist - no action was done|Entity was successfully deleted",
     *      401 ="Unauthorized request"
     *  })
     *
     * @return Response
     * @throws \LogicException
     */
    public function resetUsersRememberedFilterAction(): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_reset_user_remembered');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $savedFilter = $loggedUser->getRememberedFilter();

        if ($savedFilter instanceof Filter) {
            if (!$this->get('filter_voter')->isGranted(VoteOptions::SET_REMEMBERED_FILTER, $savedFilter)) {
                $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
                return $response;
            }

            $savedFilter->removeRememberUser($loggedUser);
            $loggedUser->setRememberedFilter(null);
            $this->getDoctrine()->getManager()->persist($savedFilter);
            $this->getDoctrine()->getManager()->persist($loggedUser);
            $this->getDoctrine()->getManager()->flush();

            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode(['message' => 'Filter was successfully removed from the logged user!']));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode(['message' => 'User does not have saved any filter!']));
        }

        return $response;
    }
}
