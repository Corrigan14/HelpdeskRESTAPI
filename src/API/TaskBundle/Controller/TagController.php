<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Security\VoteOptions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TagController
 *
 * @package API\TaskBundle\Controller
 */
class TagController extends ApiBaseController implements ControllerInterface
{
    /**
     * ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 37,
     *             "title": "Free Time",
     *             "color": "BF4848",
     *             "public": true,
     *             "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             }
     *          },
     *          {
     *              "id": 38,
     *              "title": "Work",
     *              "color": "4871BF",
     *              "public": true,
     *              "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             }
     *          },
     *          {
     *              "id": 40,
     *              "title": "Another Admin Public Tag",
     *              "color": "DFD115",
     *              "public": false,
     *              "createdBy":
     *             {
     *                "id": 2575,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *             }
     *           }
     *        ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/tags?page=1",
     *           "first": "/api/v1/task-bundle/tags?page=1",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/tags?page=2",
     *           "last": "/api/v1/task-bundle/tags?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Logged User's Tags + public tags",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="limit",
     *       "description"="Limit for Pagination: 999 - returns all entities, null - returns 10 entities"
     *     },
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Title"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tag_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];

            $filtersForUrl = [
                'order' => '&order=' . $order,
            ];

            $options = [
                'loggedUserId' => $this->getUser()->getId(),
                'isActive' => false,
                'filtersForUrl' => $filtersForUrl,
                'limit' => $limit,
                'order' => $order
            ];

            $tagArray = $this->get('tag_service')->getAttributesResponse($page, $options);
            $response = $response->setContent(json_encode($tagArray));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }

        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 37,
     *           "title": "Free Time",
     *           "color": "BF4848",
     *           "public": true,
     *           "createdBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk"
     *            }
     *        }
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/tags/2",
     *           "delete": "/api/v1/task-bundle/tags/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Tag (tag has to be public or logged User's tag)",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output="API\TaskBundle\Entity\Tag",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Tag",
     *  }
     * )
     *
     *
     * @param int $id
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tag', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        /** @var Tag $t */
        $t = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($id);

        if (!$this->get('tag_voter')->isGranted(VoteOptions::SHOW_TAG, $t)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        if (!$t instanceof Tag) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Tag with requested Id does not exist!']));
            return $response;
        }

        $tagArray = $this->get('tag_service')->getAttributeResponse($id);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($tagArray));
        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 37,
     *           "title": "Free Time",
     *           "color": "BF4848",
     *           "public": true,
     *           "createdBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk"
     *            }
     *        }
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/tags/2",
     *           "delete": "/api/v1/task-bundle/tags/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Tag Entity",
     *  input={"class"="API\TaskBundle\Entity\Tag"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Tag"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied to create PUBLIC tag",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     *
     * @param Request $request
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAction(Request $request): Response
    {
        $locationURL = $this->generateUrl('tag_create');

        $tag = new Tag();
        $tag->setCreatedBy($this->getUser());

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateTag($tag, $requestBody, true, $locationURL);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 37,
     *           "title": "Free Time",
     *           "color": "BF4848",
     *           "public": true,
     *           "createdBy":
     *            {
     *               "id": 2575,
     *               "username": "admin",
     *               "email": "admin@admin.sk"
     *            }
     *        }
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/tags/2",
     *           "delete": "/api/v1/task-bundle/tags/2"
     *         }
     *      }
     *
     *
     * @ApiDoc(
     *  description="Update the Tag",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Tag"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Tag"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied to create PUBLIC tag",
     *      404 ="Not found Tag",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     *
     * @param int $id
     * @param Request $request
     * @return Response|JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request)
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tag_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
            'id' => $id,
        ]);

        if (!$tag instanceof Tag) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Tag with requested Id does not exist!']));
            return $response;
        }

        // User can update just it's own tags AND
        // User can update PUBLIC tags if he has general ACL SHARE_TAGS
        if (!$this->get('tag_voter')->isGranted(VoteOptions::UPDATE_TAG, $tag)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateTag($tag, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Delete Tag Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      204 ="The Tag was successfully deleted",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Tag"
     *  })
     *
     * @param int $id
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function deleteAction(int $id):Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('tag_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($id);

        if (!$tag instanceof Tag) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Tag with requested Id does not exist!']));
            return $response;
        }

        // User can update just it's own tags AND
        // User can update PUBLIC tags if he has general ACL SHARE_TAGS
        if (!$this->get('tag_voter')->isGranted(VoteOptions::UPDATE_TAG, $tag)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $this->getDoctrine()->getManager()->remove($tag);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::DELETED_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::DELETED_MESSAGE]));
        return $response;
    }

    /**
     * @param Tag $tag
     * @param array $requestData
     * @param bool $create
     * @param $locationUrl
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function updateTag(Tag $tag, $requestData, $create = false, $locationUrl): Response
    {
        $allowedUnitEntityParams = [
            'title',
            'color',
            'public'
        ];

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);

        if (!$tag instanceof Tag) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Tag with requested Id does not exist!']));
            return $response;
        }

        if (false !== $requestData) {
            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
            }

            foreach ($requestData as $key => $value) {
                if (!\in_array($key, $allowedUnitEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for a Tag Entity!']));
                    return $response;
                }
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            // Check PUBLIC TAG creation possibility
            if (isset($requestData['public']) && (true === $requestData['public']  || 'true' === strtolower($requestData['public']))) {
                $aclOptions = [
                    'acl' => UserRoleAclOptions::SHARE_TAGS,
                    'user' => $this->getUser()
                ];

                if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                    $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                    $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
                    return $response;
                }
                $tag->setPublic(true);
                unset($requestData['public']);
            } else {
                $tag->setPublic(false);
            }

            $errors = $this->get('entity_processor')->processEntity($tag, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($tag);
                $this->getDoctrine()->getManager()->flush();

                $tagArray = $this->get('tag_service')->getAttributeResponse($tag->getId());
                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($tagArray));
                return $response;
            }else {
                $data = [
                    'errors' => $errors,
                    'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
                ];
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode($data));
                return $response;
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;
    }
}
