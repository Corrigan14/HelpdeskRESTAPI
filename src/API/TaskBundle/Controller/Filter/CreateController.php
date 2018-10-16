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
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
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

        dump($dataValidation);
        return $response;
    }

    /**
     * @param $request
     * @return array
     * @throws \LogicException
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

        $checkPublicFilterPermission = $this->checkPublicFilterPermission($requestDataCheck['requestData']);
        if (isset($checkPublicFilterPermission['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::ACCESS_DENIED_CODE,
                'errorMessage' => $checkPublicFilterPermission['error']
            ];
        }

        return [
            'status' => true,
            'requestData' => $requestDataCheck['requestData'],
            'public' => $checkPublicFilterPermission['public']
        ];
    }

    /**
     * Check if user can create PUBLIC filter (it's role has SHARE_FILTER ACL)
     *
     * @param array $data
     * @return array
     * @throws \LogicException
     */
    private function checkPublicFilterPermission(array $data): array
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SHARE_FILTERS,
            'user' => $this->getUser()
        ];
        $checkRolesAclForPublicFilterCreation = $this->get('acl_helper')->roleHasACL($aclOptions);

        $response = $this->get('filter_update_service')->checkPublicFilterPermission($data, $checkRolesAclForPublicFilterCreation);

        return $response;
    }

    /**
     * @param Filter $filter
     * @param array $data
     * @param bool $create
     * @param $locationUrl
     * @param bool $project
     * @return Response
     * @throws \LogicException
     */
    private function updateEntity(Filter $filter, array $data, $create = false, $locationUrl, $project = false)
    {


        // Check if user can create REPORT Filter (it's role has to have ACL REPORT_FILTERS)
        if (isset($data['report']) && (true === $data['report'] || 'true' === $data['report'] || 1 === (int)$data['report'])) {
            $aclOptions = [
                'acl' => UserRoleAclOptions::REPORT_FILTERS,
                'user' => $this->getUser()
            ];

            if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                $response = $response->setContent(json_encode(['message' => 'You have not permission to create a REPORT filter!']));
                return $response;
            } else {
                $filter->setReport(true);
            }
            unset($data['report']);
        } elseif ($create) {
            $filter->setReport(false);
        }


        // Check if user want to set this filter like default
        if (isset($data['default']) && (true === $data['default'] || 'true' === $data['default'] || 1 === (int)$data['default'])) {
            $filter->setDefault(true);
        } elseif ($create) {
            $filter->setDefault(false);
        }

        // Check if every key sent in a filter array is allowed in FilterOptions and decode data correctly
        // Possilbe ways how to send Filter data:
        // 2. json: e.g {"assigned":"210,211","taskCompany":"202"}
        // 3. string in a specific format: assigned=>210,taskCompany=>202
        if (isset($data['filter'])) {
            //Try Json decode
            $filtersArray = json_decode($data['filter'], true);


            if (!\is_array($filtersArray)) {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'Invalid filter parameter format!']));
                return $response;
            }

            foreach ($filtersArray as $key => $value) {
                if (!\in_array($key, FilterAttributeOptions::getConstants(), true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Requested filter parameter ' . $key . ' is not allowed!']));
                    return $response;
                }
            }
            $filter->setFilter($filtersArray);
            unset($data['filter']);
        }

        // Check if user set some Columns and if these columns are allowed (exists)
        // The data format should be
        // 1. JSON ARRAY: ["title","status"]
        // 2. Arrray separated by ,: title, status
        if (isset($data['columns'])) {
            $dataColumnsArray = $data['columns'];
            if (!\is_array($dataColumnsArray)) {
                $dataColumnsArray = json_decode($data['columns'], true);
                if (!\is_array($dataColumnsArray)) {
                    $dataColumnsArray = explode(',', $data['columns']);
                }
            }

            foreach ($dataColumnsArray as $col) {
                if (!\in_array($col, FilterColumnsOptions::getConstants(), true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Requested column parameter ' . $col . ' is not allowed!']));
                    return $response;
                }
            }

            $filter->setColumns($dataColumnsArray);
            unset($data['columns']);
        }

        // Check if user set some Columns and if these columns are allowed (exists)
        // The data format shoul be
        // 1. JSON ARRAY: ["title","status"]
        // 2. Arrray separated by ,: title, status
        // Check if user set some Columns_task_attributes and if these columns are allowed (exists)
        if (isset($data['columns_task_attributes'])) {
            $dataColumnsArray = $data['columns_task_attributes'];
            if (!\is_array($dataColumnsArray)) {
                $dataColumnsArray = json_decode($data['columns_task_attributes'], true);
                if (!\is_array($dataColumnsArray)) {
                    $dataColumnsArray = explode(',', $data['columns_task_attributes']);
                }
            }

            foreach ($dataColumnsArray as $col) {
                $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($col);

                if (!$taskAttribute instanceof TaskAttribute) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Requested task attribute with id ' . $col . ' does not exist!']));
                    return $response;
                }
            }

            $filter->setColumnsTaskAttributes($dataColumnsArray);
            unset($data['columns_task_attributes']);
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);
        $errors = $this->get('entity_processor')->processEntity($filter, $data);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($filter);
            $this->getDoctrine()->getManager()->flush();

            $filterArray = $this->get('filter_service')->getFilterResponse($filter->getId());
            $response = $response->setStatusCode($statusCode);
            $response = $response->setContent(json_encode($filterArray));
            return $response;
        } else {
            $data = [
                'errors' => $errors,
                'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
            ];
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode($data));
        }


    }
}