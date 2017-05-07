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
     * @return JsonResponse
     * @throws \LogicException
     */
    public function listAction(Request $request)
    {
        $pageNum = $request->get('page');
        $pageNum = (int)$pageNum;
        $page = ($pageNum === 0) ? 1 : $pageNum;

        $filtersForUrl = [];

        $options = [
            'loggedUserId' => $this->getUser()->getId(),
            'isActive' => false,
            'filtersForUrl' => $filtersForUrl
        ];

        $tagArray = $this->get('tag_service')->getAttributesResponse($page, $options);
        return $this->json($tagArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *           "patch": "/api/v1/task-bundle/tags/2",
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
     * @return Response|JsonResponse
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        /** @var Tag $t */
        $t = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($id);

        if (!$t instanceof Tag) {
            return $this->notFoundResponse();
        }

        if (!$this->get('tag_voter')->isGranted(VoteOptions::SHOW_TAG, $t)) {
            return $this->accessDeniedResponse();
        }

        $tagArray = $this->get('tag_service')->getAttributeResponse($id);
        return $this->json($tagArray, StatusCodesHelper::SUCCESSFUL_CODE);
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
     *           "patch": "/api/v1/task-bundle/tags/2",
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
     * @return Response|JsonResponse
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAction(Request $request)
    {
        $requestData = $request->request->all();

        $tag = new Tag();
        $tag->setCreatedBy($this->getUser());

        return $this->processTag($tag, $requestData, true);
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
     *           "patch": "/api/v1/task-bundle/tags/2",
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request)
    {
        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
            'id' => $id,
        ]);

        if (!$tag instanceof Tag) {
            return $this->notFoundResponse();
        }

        // User can update just it's own tags
        if (!$this->get('tag_voter')->isGranted(VoteOptions::UPDATE_TAG, $tag)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateTag($tag, $requestData);
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
     *           "patch": "/api/v1/task-bundle/tags/2",
     *           "delete": "/api/v1/task-bundle/tags/2"
     *         }
     *      }
     *
     *
     * @ApiDoc(
     *  description="Partially update the Tag",
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($id);

        if (!$tag instanceof Tag) {
            return $this->notFoundResponse();
        }

        // User can update just it's own tags
        if (!$this->get('tag_voter')->isGranted(VoteOptions::UPDATE_TAG, $tag)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateTag($tag, $requestData);
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
     *      404 ="Not found Tag",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     * @throws \LogicException
     */
    public function deleteAction(int $id)
    {
        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($id);

        if (!$tag instanceof Tag) {
            return $this->notFoundResponse();
        }

        // User can delete just it's own tags
        if (!$this->get('tag_voter')->isGranted(VoteOptions::DELETE_TAG, $tag)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($tag);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param $tag
     * @param $requestData
     * @return Response|JsonResponse
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function updateTag($tag, $requestData)
    {
        if (!$tag instanceof Tag) {
            return $this->notFoundResponse();
        }

        return $this->processTag($tag, $requestData);
    }

    /**
     * @param Tag $tag
     * @param array $requestData
     * @param bool $create
     * @return Response|JsonResponse
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function processTag(Tag $tag, $requestData, $create = false)
    {
        $allowedUnitEntityParams = [
            'title',
            'color',
            'public'
        ];

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!in_array($key, $allowedUnitEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for Tag Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        if (isset($requestData['public']) && $requestData['public']) {
            $aclOptions = [
                'acl' => UserRoleAclOptions::SHARE_TAGS,
                'user' => $this->getUser()
            ];

            if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
                $tag->setPublic(false);
                return $this->accessDeniedResponse();
            } else {
                $tag->setPublic(true);
            }
            unset($requestData['public']);
        } else {
            $tag->setPublic(false);
        }

        $errors = $this->get('entity_processor')->processEntity($tag, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($tag);
            $this->getDoctrine()->getManager()->flush();

            $tagArray = $this->get('tag_service')->getAttributeResponse($tag->getId());
            return $this->json($tagArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
