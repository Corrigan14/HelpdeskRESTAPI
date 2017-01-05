<?php

namespace API\TaskBundle\Services;

/**
 * Class PaginationHelper
 *
 * @package API\TaskBundle\Services
 */
class PaginationHelper
{
    /**
     * @param string $url
     * @param int $limit
     * @param int $page
     * @param int $count
     * @param array $filters
     * @return array
     */
    public static function getPagination(string $url, int $limit, int $page, int $count, array $filters):array
    {
        $totalNumberOfPages = ceil($count / $limit);
        $previousPage = $page > 1 ? $page - 1 : false;
        $nextPage = $page < $totalNumberOfPages ? $page + 1 : false;

        $params = '';
        foreach ($filters as $filter) {
            $params .= $filter;
        }

        return [
            '_links' => [
                'self' => $url . '?page=' . $page . $params,
                'first' => $url . '?page=' . 1 . $params,
                'prev' => $previousPage ? $url . '?page=' . $previousPage . $params : false,
                'next' => $nextPage ? $url . '?page=' . $nextPage . $params : false,
                'last' => $url . '?page=' . $totalNumberOfPages . $params,
            ],
            'total' => $count,
            'page' => $page,
            'numberOfPages' => $totalNumberOfPages,
        ];
    }
}