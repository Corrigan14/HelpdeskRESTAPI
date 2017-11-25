<?php

namespace API\CoreBundle\Services;

/**
 * Class PaginationHelper
 * Generate response for REST call
 *
 * @package API\CoreBundle\Services
 */
class HateoasHelper
{
    /**
     * @param string $url
     * @param int $page
     * @param int $count
     * @param int $limit
     *
     * @param array $fields
     *
     * @param string|bool $isActive
     * @return array
     */
    public static function getPagination(string $url, int $page, int $count, int $limit, array $fields = [], $isActive = false)
    {
        if (999 !== $limit) {
            $totalNumberOfPages = ceil($count / $limit);
            $previousPage = $page > 1 ? $page - 1 : false;
            $nextPage = $page < $totalNumberOfPages ? $page + 1 : false;
        } else {
            $totalNumberOfPages = 1;
            $previousPage = false;
            $nextPage = false;
        }

        $fieldsParam = '';
        if (\count($fields) > 0) {
            $fieldsParam .= '&fields=' . implode(',', $fields);
        }

        if ('all' !== $isActive) {
            $isActiveParam = '&isActive=' . $isActive;
        } else {
            $isActiveParam = false;
        }

        return [
            '_links' => [
                'self' => $url . '?page=' . $page . $fieldsParam . $isActiveParam,
                'first' => $url . '?page=' . 1 . $fieldsParam . $isActiveParam,
                'prev' => $previousPage ? $url . '?page=' . $previousPage . $fieldsParam . $isActiveParam : false,
                'next' => $nextPage ? $url . '?page=' . $nextPage . $fieldsParam . $isActiveParam : false,
                'last' => $url . '?page=' . $totalNumberOfPages . $fieldsParam . $isActiveParam,
            ],
            'total' => $count,
            'page' => $page,
            'numberOfPages' => $totalNumberOfPages,
        ];
    }
}