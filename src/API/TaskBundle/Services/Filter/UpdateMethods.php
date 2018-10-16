<?php

namespace API\TaskBundle\Services\Filter;


use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Security\Filter\FilterAttributeOptions;
use API\TaskBundle\Security\Filter\FilterColumnsOptions;
use Doctrine\ORM\EntityManager;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class UpdateMethods
 *
 * @package API\TaskBundle\Services
 */
class UpdateMethods
{
    /**
     * @var EntityManager
     */
    private $em;


    /**
     * ProjectService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param array $data
     * @param bool $userRoleAclPublicPermission
     * @param bool $userRoleAclReportPermission
     * @return array
     * @throws \ReflectionException
     */
    public function permissionAndFormatDataCheck(array $data, bool $userRoleAclPublicPermission, bool $userRoleAclReportPermission): array
    {
        $checkPublicFilterPermission = $this->checkPublicFilterPermission($data, $userRoleAclPublicPermission);
        if (isset($checkPublicFilterPermission['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::ACCESS_DENIED_CODE,
                'errorMessage' => $checkPublicFilterPermission['error']
            ];
        }

        $checkReportFilterPermission = $this->checkReportFilterPermission($data, $userRoleAclReportPermission);
        if (isset($checkReportFilterPermission['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::ACCESS_DENIED_CODE,
                'errorMessage' => $checkReportFilterPermission['error']
            ];
        }

        $checkFilterData = $this->checkFilterDataFormat($data);
        if (isset($checkFilterData['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => $checkFilterData['error']
            ];
        }

        $checkColumnsParamData = $this->checkColumnsParamDataFormat($data);
        if (isset($checkColumnsParamData['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => $checkColumnsParamData['error']
            ];
        }

        $checkColumnsTaskAttributeParamData = $this->checkColumnsTaskAttributesParamDataFormat($data);
        if (isset($checkColumnsTaskAttributeParamData['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => $checkColumnsTaskAttributeParamData['error']
            ];
        }

        $default = false;
        if (isset($data['default'])) {
            $default = $data['default'];
        }


        return [
            'status' => true,
            'public' => $checkPublicFilterPermission['public'],
            'report' => $checkReportFilterPermission['report'],
            'filterArray' => $checkFilterData['data'],
            'columns' => $checkColumnsParamData['data'],
            'columns_task_attributes' => $checkColumnsTaskAttributeParamData['data'],
            'default' => $default
        ];
    }

    /**
     * Check if user can create PUBLIC filter (it's role has SHARE_FILTER ACL)
     *
     * @param array $data
     * @param bool $userRoleAclPermission
     * @return array
     */
    private function checkPublicFilterPermission(array $data, bool $userRoleAclPermission): array
    {
        if (!isset($data['public'])) {
            return [
                'public' => false
            ];
        }

        if (true === $data['public'] || 'true' === $data['public'] || 1 === (int)$data['public']) {
            if (!$userRoleAclPermission) {
                return [
                    'public' => false,
                    'error' => 'You have not permission to create a PUBLIC filter!'
                ];
            }

            return [
                'public' => true
            ];
        }

        return [
            'public' => false
        ];
    }

    /**
     * Check if user can create REPORT Filter (it's role has to have ACL REPORT_FILTERS)
     *
     * @param array $data
     * @param bool $userRoleAclPermission
     * @return array
     */
    private function checkReportFilterPermission(array $data, bool $userRoleAclPermission): array
    {
        if (!isset($data['report'])) {
            return [
                'report' => false
            ];
        }

        if (true === $data['report'] || 'true' === $data['report'] || 1 === (int)$data['report']) {
            if (!$userRoleAclPermission) {
                return [
                    'report' => false,
                    'error' => 'You have not permission to create a REPORT filter!'
                ];
            }

            return [
                'report' => true
            ];
        }

        return [
            'report' => false
        ];
    }

    /**
     * Check if every key sent in a filter array is allowed in FilterOptions and decode data correctly
     * Possible ways how to send Filter data:
     *  json: e.g {"assigned":"210,211","taskCompany":"202"}
     *
     * @param array $data
     * @return array
     * @throws \ReflectionException
     */
    private function checkFilterDataFormat(array $data): array
    {
        if (!isset($data['filter'])) {
            return [
                'error' => 'Filter param is required!',
                'data' => []
            ];
        }

        $filtersArray = json_decode($data['filter'], true);
        if (!\is_array($filtersArray)) {
            return [
                'data' => [],
                'error' => 'Invalid filter parameter format!'
            ];
        }

        foreach ($filtersArray as $key => $value) {
            if (!\in_array($key, FilterAttributeOptions::getConstants(), true)) {
                return [
                    'data' => [],
                    'error' => 'Requested filter parameter ' . $key . ' is not allowed!'
                ];
            }
        }

        return [
            'data' => $filtersArray
        ];
    }

    /**
     * Check if user set some Columns and if these columns are allowed (exists)
     *  Possible ways how to send Columns data:
     *   JSON ARRAY: ["title","status"]
     *   Array separated by ,: title, status
     *
     * @param array $data
     * @return array
     * @throws \ReflectionException
     */
    private function checkColumnsParamDataFormat(array $data): array
    {
        if (!isset($data['columns'])) {
            return [
                'data' => []
            ];
        }

        $dataColumnsArray = $data['columns'];
        if (!\is_array($dataColumnsArray)) {
            $dataColumnsArray = json_decode($data['columns'], true);
            if (!\is_array($dataColumnsArray)) {
                $dataColumnsArray = explode(',', $data['columns']);
            }
        }

        if (!\is_array($dataColumnsArray)) {
            return [
                'data' => [],
                'error' => 'Invalid columns parameter format!'
            ];
        }

        foreach ($dataColumnsArray as $col) {
            if (!\in_array($col, FilterColumnsOptions::getConstants(), true)) {
                return [
                    'data' => [],
                    'error' => 'Requested column parameter ' . $col . ' is not allowed!'
                ];
            }
        }

        return [
            'data' => $dataColumnsArray
        ];
    }

    /**
     * Check if user set some Columns from Task Attributes and if these columns are allowed (exists)
     *  Possible ways how to send Columns data:
     *   JSON ARRAY: ["id1","id2"]
     *   Array separated by ,: id1, id2
     *
     * @param array $data
     * @return array
     */
    private function checkColumnsTaskAttributesParamDataFormat(array $data): array
    {
        if (!isset($data['columns_task_attributes'])) {
            return [
                'data' => []
            ];
        }

        $dataColumnsArray = $data['columns_task_attributes'];
        if (!\is_array($dataColumnsArray)) {
            $dataColumnsArray = json_decode($data['columns_task_attributes'], true);
            if (!\is_array($dataColumnsArray)) {
                $dataColumnsArray = explode(',', $data['columns_task_attributes']);
            }
        }

        if (!\is_array($dataColumnsArray)) {
            return [
                'data' => [],
                'error' => 'Invalid columns_task_attributes parameter format!'
            ];
        }

        foreach ($dataColumnsArray as $col) {
            $taskAttribute = $this->em->getRepository('APITaskBundle:TaskAttribute')->find($col);

            if (!$taskAttribute instanceof TaskAttribute) {
                return [
                    'data' => [],
                    'error' => 'Requested task attribute with id ' . $col . ' does not exist!'
                ];
            }
        }

        return [
            'data' => $dataColumnsArray
        ];
    }
}