<?php

namespace API\TaskBundle\Controller\Filter;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SetRememberedController
 *
 * @package API\TaskBundle\Controller\Filter
 */
class SetRememberedController extends ApiBaseController
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
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function setUsersRememberedFilterAction($id): Response
    {
        $locationURL = $this->generateUrl('filter_set_user_remembered', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        if (!$filter instanceof Filter) {
            $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE)
                ->setContent(json_encode(['message' => 'Filter with requested Id does not exist!']));

            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::SET_REMEMBERED_FILTER, $filter)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $loggedUser->setRememberedFilter($filter);
        $filter->addRememberUser($loggedUser);
        $this->getDoctrine()->getManager()->persist($loggedUser);
        $this->getDoctrine()->getManager()->persist($filter);
        $this->getDoctrine()->getManager()->flush();

        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode(['message' => 'Filter was successfully set as remembered']));

        return $response;

    }
}