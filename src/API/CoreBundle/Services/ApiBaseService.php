<?php

namespace API\CoreBundle\Services;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Repository\RepositoryInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiBaseService
 *
 * @package API\CoreBundle\Services
 */
class ApiBaseService
{
    const PAGINATION_LIMIT = 10;


    /** @var Router */
    protected $router;

    /**
     * ApiBaseService constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @return array|bool|mixed|null
     * @throws \LogicException
     */
    public function encodeRequest(Request $request)
    {
        $contentType = $request->headers->get('Content-Type');
        $method = $request->getMethod();

        // The content type declared JSON or FORM data is expecting in the content type header
        $decodeContentType = explode(';', $contentType);
        $contentType = $decodeContentType[0];

        switch ($method) {
            case 'POST':
                // Data in both: JSON and FORM x-www-form-urlencoded are supported by API
                // Based on Content-type header we need different encoding standard
                if ('application/json' === $contentType) {
                    $requestBody = json_decode($request->getContent(), true);
                    return $requestBody;
                }
                if ('application/x-www-form-urlencoded' === $contentType) {
                    $requestBody = $request->request->all();
                    return $requestBody;
                }
                break;
            case 'PUT':
                // Data in both: JSON and FORM x-www-form-urlencoded are supported by API
                // Based on Content-type header we need different encoding standard
                if ('application/json' === $contentType) {
                    $requestBody = json_decode($request->getContent(), true);
                    return $requestBody;
                }
                if ('application/x-www-form-urlencoded' === $contentType) {
                    $requestBody = $request->request->all();
                    return $requestBody;
                }
                break;
            case 'GET':
                // GET does not require Content - type, as content is not expected
                // GET Contains only FILTER PARAMETERS and these always go to query string and are sent via URL,
                // because they are a part of finding the right data
                $requestBody = $request->query->all();
                return $requestBody;

                break;
            case 'DELETE':
                // Data in both: JSON and FORM x-www-form-urlencoded are supported by API
                // Based on Content-type header we need different encoding standard
                if ('application/json' === $contentType) {
                    $requestBody = json_decode($request->getContent(), true);
                    return $requestBody;
                }
                if ('application/x-www-form-urlencoded' === $contentType) {
                    $requestBody = $request->request->all();
                    return $requestBody;
                }
                break;
                break;
            default:
                return false;
        }
        return false;
    }

    /**
     * @param $request
     * @param array $allowedEntityParams
     * @return array|bool
     * @throws \LogicException
     */
    public function checkRequestData($request, array $allowedEntityParams)
    {
        $response = [];
        $requestData = $this->encodeRequest($request);

        if (false === $requestData) {
            $response['error'] = ['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT];
            return $response;
        }

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        $errorsArray = false;
        foreach ($requestData as $key => $value) {
            if (!\in_array($key, $allowedEntityParams, true)) {
                $errorsArray[] = $key . ' is not allowed parameter!';
            }
        }
        if ($errorsArray) {
            $response['error'] = $errorsArray;
            return $response;
        }

        $response['requestData'] = $requestData;
        return $response;
    }

    /**
     * @param $locationURL
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function createResponseEntityWithSettings(string $locationURL): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Location', $locationURL);

        return $response;
    }

    /**
     * Filter params: limit, page, order and isActive
     *
     * @param array $requestBody
     * @param bool $specialConditions
     * @param bool|User $loggedUser
     * @param bool $isAdmin
     * @return array
     */
    public function processFilterParams(array $requestBody, $specialConditions = false, $loggedUser = false, $isAdmin = false): array
    {
        $filtersForUrl = [];

        // Filter params processing
        if (isset($requestBody['page'])) {
            $pageNum = $requestBody['page'];
            $page = (int)$pageNum;
        } else {
            $page = 1;
        }

        if (isset($requestBody['limit'])) {
            $limitNum = $requestBody['limit'];
            $limit = (int)$limitNum;
        } else {
            $limit = 10;
        }

        if (999 === $limit) {
            $page = 1;
        }
        $filtersForUrl ['page'] = '&page=' . $page;
        $filtersForUrl ['limit'] = '&limit=' . $limit;

        $order = 'ASC';
        if ($specialConditions && isset($requestBody['order'])) {
            $orderString = $requestBody['order'];
            $order = $orderString;
        } elseif ($specialConditions) {
            $order = null;
        } elseif (isset($requestBody['order'])) {
            $orderString = $requestBody['order'];
            $orderString = strtoupper($orderString);
            if ($orderString === 'ASC' || $orderString === 'DESC') {
                $order = $orderString;
            }
        }
        $filtersForUrl ['order'] = '&order=' . $order;

        if (isset($requestBody['isActive'])) {
            $isActive = strtolower($requestBody['isActive']);
        } else {
            $isActive = 'all';
        }
        $filtersForUrl ['isActive'] = '&isActive=' . $isActive;

        if (isset($requestBody['term'])) {
            $term = strtolower($requestBody['term']);
            $filtersForUrl ['term'] = '&term=' . $term;
        } else {
            $term = false;
        }

        if (isset($requestBody['internal'])) {
            $internal = strtolower($requestBody['internal']);
            $filtersForUrl ['internal'] = '&internal=' . $internal;
        } else {
            $internal = 'all';
        }

        if (isset($requestBody['public'])) {
            $public = strtolower($requestBody['public']);
            $filtersForUrl ['public'] = '&public=' . $public;
        } else {
            $public = 'all';
        }


        if (isset($requestBody['report'])) {
            $report = strtolower($requestBody['report']);
            $filtersForUrl ['report'] = '&report=' . $report;
        } else {
            $report = 'all';
        }

        if (isset($requestBody['project'])) {
            $project = strtolower($requestBody['project']);
            $filtersForUrl ['project'] = '&project=' . $project;
        } else {
            $project = null;
        }

        if (isset($requestBody['default'])) {
            $default = strtolower($requestBody['default']);
            $filtersForUrl ['default'] = '&default=' . $default;
        } else {
            $default = null;
        }

        if (isset($requestBody['read'])) {
            $read = strtolower($requestBody['read']);
            if ('true' === $read || true === $read) {
                $read = true;
            } elseif ('false' === $read || false === $read) {
                $read = false;
            }
        } else {
            $read = 'all';
        }

        return [
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
            'isActive' => $isActive,
            'term' => $term,
            'internal' => $internal,
            'public' => $public,
            'report' => $report,
            'project' => $project,
            'default' => $default,
            'read' => $read,
            'filtersForUrl' => $filtersForUrl,
            'loggedUser' => $loggedUser,
            'isAdmin' => $isAdmin
        ];
    }

    /**
     * Return all Entities which includes Data and Links
     *
     * @param RepositoryInterface $entityRepository
     * @param int $page
     * @param string $routeName
     * @param array $options
     *
     * @return array
     */
    public function getEntitiesResponse(RepositoryInterface $entityRepository, int $page, string $routeName, array $options = []): array
    {
        $entities = $entityRepository->getAllEntities($page, $options);

        $response = [
            'data' => $entities
        ];

        $pagination = HateoasHelper::getPagination(
            $this->router->generate($routeName),
            $page,
            $entityRepository->countEntities($options),
            self::PAGINATION_LIMIT
        );

        return array_merge($response, $pagination);
    }

    /**
     * Return Entity Response which includes all data about Entity and Links to update/partialUpdate/delete
     *
     * @param object $entity
     * @param string $entityName
     *
     * @return array
     */
    public function getEntityResponse($entity, string $entityName): array
    {
        return [
            'data' => $entity,
            '_links' => $this->getEntityLinks($entity->getId(), $entityName),
        ];
    }

    /**
     * Return Entity Response which includes all data about Entity and Links to update/partialUpdate/delete
     *
     * @param RepositoryInterface $entityRepository
     * @param $entityId
     * @param string $entityName
     * @return array
     */
    public function getFullEntityResponse(RepositoryInterface $entityRepository, $entityId, string $entityName): array
    {
        $entity = $entityRepository->getEntity($entityId);

        return [
            'data' => $entity[0],
            '_links' => $this->getEntityLinks($entityId, $entityName),
        ];
    }

    /**
     * @param int $id
     *
     * @param string $entityName
     * @return array
     */
    private function getEntityLinks(int $id, string $entityName): array
    {
        return [
            'put' => $this->router->generate($entityName . '_update', ['id' => $id]),
            'patch' => $this->router->generate($entityName . '_partial_update', ['id' => $id]),
            'delete' => $this->router->generate($entityName . '_delete', ['id' => $id]),
        ];
    }

}