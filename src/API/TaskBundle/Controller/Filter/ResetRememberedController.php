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
 * Class ResetRememberedController
 *
 * @package API\TaskBundle\Controller\Filter
 */
class ResetRememberedController extends ApiBaseController
{
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
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function resetUsersRememberedFilterAction(): Response
    {
        $locationURL = $this->generateUrl('filter_reset_user_remembered');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $savedFilter = $loggedUser->getRememberedFilter();

        if (!$savedFilter instanceof Filter) {
            $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
                ->setContent(json_encode(['message' => 'User does not have saved any filter!']));

            return $response;
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::SET_REMEMBERED_FILTER, $savedFilter)) {
            $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE)
                ->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));

            return $response;
        }

        $savedFilter->removeRememberUser($loggedUser);
        $loggedUser->setRememberedFilter(null);
        $this->getDoctrine()->getManager()->persist($savedFilter);
        $this->getDoctrine()->getManager()->persist($loggedUser);
        $this->getDoctrine()->getManager()->flush();

        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode(['message' => 'Filter was successfully removed from the logged user!']));

        return $response;
    }
}