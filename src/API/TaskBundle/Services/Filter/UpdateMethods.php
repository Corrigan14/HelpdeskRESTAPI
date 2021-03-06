<?php

namespace API\TaskBundle\Services\Filter;


use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Security\Filter\FilterAttributeOptions;
use API\TaskBundle\Security\Filter\FilterColumnsOptions;
use Doctrine\ORM\EntityManager;
use Igsem\APIBundle\Services\StatusCodesHelper;

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
     * @param bool $userCanUpdateFilter
     * @param Filter $filter
     * @return array
     * @throws \ReflectionException
     */
    public function permissionAndFormatDataCheck(array $data, bool $userRoleAclPublicPermission, bool $userRoleAclReportPermission, bool $userCanUpdateFilter, Filter $filter): array
    {
        if (!$userCanUpdateFilter) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::ACCESS_DENIED_CODE,
                'errorMessage' => 'You have not a permission to update this filter!'
            ];
        }

        $checkPublicFilterPermission = $this->checkPublicFilterPermission($data, $userRoleAclPublicPermission, $filter);
        if (isset($checkPublicFilterPermission['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::ACCESS_DENIED_CODE,
                'errorMessage' => $checkPublicFilterPermission['error']
            ];
        }

        $checkReportFilterPermission = $this->checkReportFilterPermission($data, $userRoleAclReportPermission, $filter);
        if (isset($checkReportFilterPermission['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::ACCESS_DENIED_CODE,
                'errorMessage' => $checkReportFilterPermission['error']
            ];
        }

        $checkFilterData = $this->checkFilterDataFormat($data, $filter);
        if (isset($checkFilterData['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => $checkFilterData['error']
            ];
        }

        $checkColumnsParamData = $this->checkColumnsParamDataFormat($data, $filter);
        if (isset($checkColumnsParamData['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => $checkColumnsParamData['error']
            ];
        }

        $checkColumnsTaskAttributeParamData = $this->checkColumnsTaskAttributesParamDataFormat($data, $filter);
        if (isset($checkColumnsTaskAttributeParamData['error'])) {
            return [
                'status' => false,
                'errorCode' => StatusCodesHelper::INVALID_PARAMETERS_CODE,
                'errorMessage' => $checkColumnsTaskAttributeParamData['error']
            ];
        }

        $default = $filter->getDefault();
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
     * @param Filter $filter
     * @return array
     */
    private function checkPublicFilterPermission(array $data, bool $userRoleAclPermission, Filter $filter): array
    {
        if (!isset($data['public'])) {
            return [
                'public' => $filter->getPublic()
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

        if (false === $data['public'] || 'false' === $data['public'] || 0 === (int)$data['public']) {
            return [
                'public' => false
            ];
        }

        return [
            'public' => $filter->getPublic()
        ];
    }

    /**
     * Check if user can create REPORT Filter (it's role has to have ACL REPORT_FILTERS)
     *
     * @param array $data
     * @param bool $userRoleAclPermission
     * @return array
     */
    private function checkReportFilterPermission(array $data, bool $userRoleAclPermission, Filter $filter): array
    {
        if (!isset($data['report'])) {
            return [
                'report' => $filter->getReport()
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

        if (false === $data['report'] || 'false' === $data['report'] || 0 === (int)$data['report']) {
            return [
                'report' => false
            ];
        }

        return [
            'report' => $filter->getReport()
        ];
    }

    /**
     * Check if every key sent in a filter array is allowed in FilterOptions and decode data correctly
     * Possible ways how to send Filter data:
     *  json: e.g {"assigned":"210,211","taskCompany":"202"}
     *
     * @param array $data
     * @param Filter $filter
     * @return array
     * @throws \ReflectionException
     */
    private function checkFilterDataFormat(array $data, Filter $filter): array
    {
        if (!isset($data['filter'])) {
            return [
                'data' => $filter->getFilter()
            ];
        }

        $filtersArray = json_decode($data['filter'], true);
        if (!\is_array($filtersArray)) {
            return [
                'data' => $filter->getFilter(),
                'error' => 'Invalid filter parameter format!'
            ];
        }

        foreach ($filtersArray as $key => $value) {
            if (!\in_array($key, FilterAttributeOptions::getConstants(), true)) {
                return [
                    'data' => $filter->getFilter(),
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
     * @param Filter $filter
     * @return array
     * @throws \ReflectionException
     */
    private function checkColumnsParamDataFormat(array $data, Filter $filter): array
    {
        if (!isset($data['columns'])) {
            return [
                'data' => $filter->getColumns()
            ];
        }

        if (\is_string($data['columns']) && 'null' === strtolower($data['columns'])) {
            return [
                'data' => null
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
                'data' => $filter->getColumns(),
                'error' => 'Invalid columns parameter format!'
            ];
        }

        foreach ($dataColumnsArray as $col) {
            if (!\in_array($col, FilterColumnsOptions::getConstants(), true)) {
                return [
                    'data' => $filter->getColumns(),
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
    private function checkColumnsTaskAttributesParamDataFormat(array $data, Filter $filter): array
    {
        if (!isset($data['columns_task_attributes'])) {
            return [
                'data' => $filter->getColumnsTaskAttributes()
            ];
        }

        if (\is_string($data['columns_task_attributes']) && 'null' === strtolower($data['columns_task_attributes'])) {
            return [
                'data' => null
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
                'data' => $filter->getColumnsTaskAttributes(),
                'error' => 'Invalid columns_task_attributes parameter format!'
            ];
        }

        foreach ($dataColumnsArray as $col) {
            $taskAttribute = $this->em->getRepository('APITaskBundle:TaskAttribute')->find($col);

            if (!$taskAttribute instanceof TaskAttribute) {
                return [
                    'data' => $filter->getColumnsTaskAttributes(),
                    'error' => 'Requested task attribute with id ' . $col . ' does not exist!'
                ];
            }
        }

        return [
            'data' => $dataColumnsArray
        ];
    }
}