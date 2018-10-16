<?php

namespace API\TaskBundle\Controller\Filter;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Security\Filter\EntityParams;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CreateController
 *
 * @package API\TaskBundle\Controller\Filter
 */
class CreateController extends ApiBaseController
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
     *  resource = true,
     *  description="Create a new Filter Entity.
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
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \ReflectionException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('filter_create');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $dataValidation = $this->validateData($request);
        if (false === $dataValidation['status']) {
            $response->setStatusCode($dataValidation['errorCode'])
                ->setContent(json_encode($dataValidation['errorMessage']));

            return $response;
        }

        $filter = new Filter();
        $filter->setIsActive(true);
        $filter->setCreatedBy($this->getUser());
        $filter->setFilter($dataValidation['filterArray']);
        $filter->setReport($dataValidation['report']);
        $filter->setPublic($dataValidation['public']);
        $filter->setColumns($dataValidation['columns']);
        $filter->setColumnsTaskAttributes($dataValidation['columns_task_attributes']);
        $filter->setDefault($dataValidation['default']);

        $errors = $this->get('entity_processor')->processEntity($filter, $dataValidation['requestData']);
        if ($errors) {
            $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_MESSAGE)
                ->setContent(json_encode($errors));

            return $response;
        }

        $this->getDoctrine()->getManager()->persist($filter);
        $this->getDoctrine()->getManager()->flush();

        $filterArray = $this->get('filter_get_service')->getFilterResponse($filter->getId());
        $response->setStatusCode(StatusCodesHelper::CREATED_CODE)
            ->setContent(json_encode($filterArray));

        return $response;
    }

    /**
     * @param $request
     * @return array
     * @throws \LogicException
     * @throws \ReflectionException
     */
    private function validateData($request): array
    {
        $requestDataCheck = $this->get('api_base.service')->checkRequestData($request, EntityParams::getAllowedEntityParams());
        if (isset($requestDataCheck['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => $requestDataCheck['error']
            ];
        }

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

        $permissionAndFormatDataCheck = $this->get('filter_update_service')->permissionAndFormatDataCheck($requestDataCheck['requestData'], $checkRolesAclForPublicFilterCreation, $checkRoleAclForReportFilterCreation);
        if (false === $permissionAndFormatDataCheck['status']) {
            return [
                'status' => false,
                'errorCode' => $permissionAndFormatDataCheck['errorCode'],
                'errorMessage' => $permissionAndFormatDataCheck['errorMessage']
            ];
        }

        unset($requestDataCheck['requestData']['public'], $requestDataCheck['requestData']['report'], $requestDataCheck['requestData']['filter'], $requestDataCheck['requestData']['columns'], $requestDataCheck['requestData']['columns_task_attributes']);

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