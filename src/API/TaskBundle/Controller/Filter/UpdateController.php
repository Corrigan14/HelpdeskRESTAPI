<?php

namespace API\TaskBundle\Controller\Filter;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Security\Filter\EntityParams;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UpdateController
 *
 * @package API\TaskBundle\Controller\Filter
 */
class UpdateController extends ApiBaseController
{

    /**
     * ### Response ###
     *      {
     *        "data":
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
     *       "_links":
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
     *  resource = true,
     *  description="Update Filter Entity.
     *  Filter field is expected to be an array with key = filter option, value = requested data id/val/... (look at task list filters)",
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public function updateAction(Request $request, int $id): Response
    {
        $locationURL = $this->generateUrl('filter_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);
        $dataValidation = $this->validateData($request, $filter);
        if (false === $dataValidation['status']) {
            $response->setStatusCode($dataValidation['errorCode'])
                ->setContent(json_encode($dataValidation['errorMessage']));

            return $response;
        }

        $filter->setFilter($dataValidation['filterArray']);
        $filter->setReport($dataValidation['report']);
        $filter->setPublic($dataValidation['public']);
        $filter->setColumns($dataValidation['columns']);
        $filter->setColumnsTaskAttributes($dataValidation['columns_task_attributes']);
        $filter->setDefault($dataValidation['default']);

        $errors = $this->get('entity_processor')->processEntity($filter, $dataValidation['requestData']);
        if ($errors) {
            $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE)
                ->setContent(json_encode($errors));

            return $response;
        }

        $this->getDoctrine()->getManager()->persist($filter);
        $this->getDoctrine()->getManager()->flush();

        $filterArray = $this->get('filter_get_service')->getFilterResponse($filter->getId());
        $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE)
            ->setContent(json_encode($filterArray));

        return $response;
    }

    /**
     * @param $request
     * @param $filter
     * @return array
     * @throws \LogicException
     * @throws \ReflectionException
     */
    private function validateData($request, $filter): array
    {
        if (!$filter instanceof Filter) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => 'Filter with requested Id does not exist!'
            ];
        }

        $requestDataCheck = $this->get('api_base.service')->checkRequestData($request, EntityParams::getAllowedEntityParams());
        if (isset($requestDataCheck['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => $requestDataCheck['error']
            ];
        }

        $permissionToUpdateFilter = $this->get('filter_voter')->isGranted(VoteOptions::UPDATE_FILTER, $filter);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SHARE_FILTERS,
            'user' => $this->getUser()
        ];
        $checkRolesAclForPublicFilterCreation = $this->get('acl_helper')->roleHasACL($aclOptions);

        $aclOptions = [
            'acl' => UserRoleAclOptions::REPORT_FILTERS,
            'user' => $this->getUser()
        ];
        $checkRoleAclForReportFilterCreation = $this->get('acl_helper')->roleHasACL($aclOptions);

        $permissionAndFormatDataCheck = $this->get('filter_update_service')->permissionAndFormatDataCheck($requestDataCheck['requestData'], $checkRolesAclForPublicFilterCreation, $checkRoleAclForReportFilterCreation, $permissionToUpdateFilter, $filter);
        if (false === $permissionAndFormatDataCheck['status']) {
            return [
                'status' => false,
                'errorCode' => $permissionAndFormatDataCheck['errorCode'],
                'errorMessage' => $permissionAndFormatDataCheck['errorMessage']
            ];
        }

        unset($requestDataCheck['requestData']['public'], $requestDataCheck['requestData']['report'], $requestDataCheck['requestData']['filter'], $requestDataCheck['requestData']['columns'], $requestDataCheck['requestData']['columns_task_attributes'],  $requestDataCheck['requestData']['default']);

        return [
            'status' => true,
            'requestData' => $requestDataCheck['requestData'],
            'public' => $permissionAndFormatDataCheck['public'],
            'report' => $permissionAndFormatDataCheck['report'],
            'filterArray' => $permissionAndFormatDataCheck['filterArray'],
            'columns' => $permissionAndFormatDataCheck['columns'],
            'columns_task_attributes' => $permissionAndFormatDataCheck['columns_task_attributes'],
            'default' => $permissionAndFormatDataCheck['default']
        ];
    }
}