<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Controller\ApiBaseController;
use API\CoreBundle\Controller\ControllerInterface;
use API\TaskBundle\Entity\Tag;
use API\CoreBundle\Services\StatusCodesHelper;
use API\TaskBundle\Security\VoteOptions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
     *            "id": "2",
     *             "title": "Work",
     *             "color": "4871BF",
     *             "public": true
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/tags?page=1",
     *           "first": "/tags?page=1",
     *           "prev": false,
     *           "next": "/tags?page=2",
     *           "last": "/tags?page=3"
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
     */
    public function listAction(Request $request)
    {
        $page = $request->get('page') ?: 1;

        return $this->json($this->get('api_tag.service')->getTagsResponse($this->getUser()->getId(), $page), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Work",
     *           "color": "4871BF"
     *           "public": true
     *           "created_by":
     *           {
     *              "id": 19,
     *              "username": "user",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "acl": "[]"
     *          }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/tasks/tags/2",
     *           "patch": "/api/v1/tasks/tags/2",
     *           "delete": "/api/v1/tasks/tags/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Tag (tag has to be logged User's tag)",
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
     * @return JsonResponse
     */
    public function getAction(int $id)
    {
        /** @var Tag $t */
        $t = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->find($id);

        if (null === $t || !$t instanceof Tag) {
            return $this->createApiResponse([
                'message' => StatusCodesHelper::TAG_NOT_FOUND_MESSAGE,
            ], StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
        }

        if (!$this->get('tag_voter')->isGranted(VoteOptions::SHOW_TAG, $t)) {
            return $this->createApiResponse([
                'message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE,
            ], StatusCodesHelper::ACCESS_DENIED_CODE);
        }

        $tag = $this->get('api_tag.service')->getTagResponse($t);

        return $this->createApiResponse($tag, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "user_id": "18",
     *           "title": "Work",
     *           "color": "4871BF"
     *           "public": true
     *           "created_by":
     *           {
     *              "id": 19,
     *              "username": "user",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "acl": "[]"
     *          }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/tasks/tags/2",
     *           "patch": "/api/v1/tasks/tags/2",
     *           "delete": "/api/v1/tasks/tags/2"
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
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $requestData = $request->request->all();

        $tag = new Tag();
        $tag->setCreatedBy($this->getUser());

        return $this->processTag($tag, $requestData, true);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Work",
     *           "color": "4871BF",
     *           "public": true
     *           "created_by":
     *           {
     *              "id": 19,
     *              "username": "user",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "acl": "[]"
     *          }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/tasks/tags/2",
     *           "patch": "/api/v1/tasks/tags/2",
     *           "delete": "/api/v1/tasks/tags/2"
     *         }
     *      }
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
     * @return JsonResponse
     */
    public function updateAction(int $id, Request $request)
    {
        $requestData = $request->request->all();

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
            'id' => $id,
            'createdBy' => $this->getUser(),
        ]);

        return $this->updateTag($tag, $requestData);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "title": "Work",
     *           "color": "4871BF",
     *           "public": true
     *           "created_by":
     *           {
     *              "id": 19,
     *              "username": "user",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "acl": "[]"
     *          }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/tasks/tags/2",
     *           "patch": "/api/v1/tasks/tags/2",
     *           "delete": "/api/v1/tasks/tags/2"
     *         }
     *      }
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
     * @return JsonResponse
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $requestData = $request->request->all();

        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
            'id' => $id,
            'createdBy' => $this->getUser(),
        ]);

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
     *      404 ="Not found Tag",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteAction(int $id)
    {
        $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
            'id' => $id,
            'createdBy' => $this->getUser(),
        ]);

        if (null === $tag || !$tag instanceof Tag) {
            return $this->createApiResponse([
                'message' => StatusCodesHelper::TAG_NOT_FOUND_MESSAGE,
            ], StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
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
     * @return JsonResponse
     */
    private function updateTag($tag, $requestData)
    {
        if (null === $tag || !$tag instanceof Tag) {
            return $this->createApiResponse([
                'message' => StatusCodesHelper::TAG_NOT_FOUND_MESSAGE,
            ], StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
        }

        return $this->processTag($tag, $requestData);
    }

    /**
     * @param Tag $tag
     * @param array $requestData
     * @param bool $create
     * @return JsonResponse
     */
    private function processTag(Tag $tag, $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        if (isset($requestData['public']) && $requestData['public']) {
            if (!$this->get('tag_voter')->isGranted(VoteOptions::CREATE_PUBLIC_TAG)) {
                return $this->createApiResponse([
                    'message' => StatusCodesHelper::ACCESS_DENIED_TO_CREATE_PUBLIC_TAG,
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            }
        }

        $errors = $this->get('entity_processor')->processEntity($tag, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($tag);
            $this->getDoctrine()->getManager()->flush();

            return $this->createApiResponse($this->get('api_tag.service')->getTagResponse($tag), $statusCode);
        }

        return $this->createApiResponse(['message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE, 'errors' => $errors], StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
