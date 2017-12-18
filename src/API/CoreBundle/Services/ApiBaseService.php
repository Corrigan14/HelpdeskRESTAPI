<?php

namespace API\CoreBundle\Services;

use API\CoreBundle\Repository\RepositoryInterface;
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
        $decodeContentType = explode(';',$contentType);
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
                // Data in both: JSON and FORM x-www-form-urlencoded are supported by API
                // GET Contains only FILTER PARAMETERS and these always go to query string and are sent via URL,
                // because while they are a part of finding the right data
                if ('application/json' === $contentType || 'application/x-www-form-urlencoded' === $contentType) {
                    $requestBody = $request->query->all();
                    return $requestBody;
                }
                break;
            default:
                return false;
        }
        return false;
    }

    /**
     * @param $locationURL
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function createResponseEntityWithSettings(string $locationURL):Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Location', $locationURL);

        return $response;
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
    public function getEntitiesResponse(RepositoryInterface $entityRepository, int $page, string $routeName, array $options = [])
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
    public function getEntityResponse($entity, string $entityName)
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
    public function getFullEntityResponse(RepositoryInterface $entityRepository, $entityId, string $entityName)
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
    private function getEntityLinks(int $id, string $entityName)
    {
        return [
            'put' => $this->router->generate($entityName . '_update', ['id' => $id]),
            'patch' => $this->router->generate($entityName . '_partial_update', ['id' => $id]),
            'delete' => $this->router->generate($entityName . '_delete', ['id' => $id]),
        ];
    }

}