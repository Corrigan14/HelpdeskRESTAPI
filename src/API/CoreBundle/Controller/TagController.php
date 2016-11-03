<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\Tag;
use API\CoreBundle\Entity\User;
use API\CoreBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class TagController
 *
 * @package API\CoreBundle\Controller
 */
class TagController extends ApiBaseController
{
    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *          "0":
     *          {
     *             "id": "2",
     *             "user_id": "18",
     *             "title": "Work",
     *             "color": "4871BF"
     *          }
     *        }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a list of Logged User's Tags",
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
     * @return JsonResponse
     *
     */
    public function listTagsAction()
    {
        if(!$this->get('user_voter')->isLogged()){
            return $this->unauthorizedResponse();
        }

        /** @var User $user */
        $user = $this->get('user_voter')->getLoggedUser();

        if($user){
            $tags = $this->get('api_tag.model')->getTags($user->getId());

            return $this->createApiResponse($tags, StatusCodesHelper::SUCCESSFUL_CODE);
        }

        return $this->unauthorizedResponse();
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *           "user_id": "18",
     *           "title": "Work",
     *           "color": "4871BF"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/tags/2",
     *           "patch": "/api/v1/tags/2",
     *           "delete": "/api/v1/tags/2"
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
     *  output="API\CoreBundle\Entity\Tag",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      404 ="Not found Tag",
     *  }
     * )
     *
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getTagAction(int $id)
    {
        if(!$this->get('user_voter')->isLogged()){
            return $this->unauthorizedResponse();
        }

        /** @var User $user */
        $user = $this->get('user_voter')->getLoggedUser();

        if($user){
            $tag = $this->get('api_tag.model')->getTagById($id, $user->getId());

            if (null === $tag || !$tag['data']) {
                return $this->createApiResponse([
                    'message' => StatusCodesHelper::TAG_NOT_FOUND_MESSAGE ,
                ] , StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
            }

            return $this->createApiResponse($tag, StatusCodesHelper::SUCCESSFUL_CODE);
        }

        return $this->unauthorizedResponse();
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
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/tags/2",
     *           "patch": "/api/v1/tags/2",
     *           "delete": "/api/v1/tags/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Tag Entity",
     *  input={"class"="API\CoreBundle\Entity\Tag"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\Tag"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createTagAction(Request $request)
    {
        if(!$this->get('user_voter')->isLogged()){
            return $this->unauthorizedResponse();
        }

        $user = $this->get('user_voter')->getLoggedUser();

        if($user)
        {
            $requestData = $request->request->all();

            $tag = new Tag();
            $tag->setUser($user);

            return $this->processTag($tag,$requestData, true);
        }

        return $this->unauthorizedResponse();
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
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/tags/2",
     *           "patch": "/api/v1/tags/2",
     *           "delete": "/api/v1/tags/2"
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
     *  input={"class"="API\CoreBundle\Entity\Tag"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\Tag"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
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
    public function updateTagAction(int $id, Request $request)
    {
        if(!$this->get('user_voter')->isLogged()){
            return $this->unauthorizedResponse();
        }

        $user = $this->get('user_voter')->getLoggedUser();

        if($user)
        {
            $requestData = $request->request->all();

            $tag = $this->getDoctrine()->getRepository('APICoreBundle:Tag')->findOneBy([
                'id' => $id,
                'user' => $user,
            ]);

            return $this->updateTag($tag,$requestData);
        }

        return $this->unauthorizedResponse();
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
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/tags/2",
     *           "patch": "/api/v1/tags/2",
     *           "delete": "/api/v1/tags/2"
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
     *  input={"class"="API\CoreBundle\Entity\Tag"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\Tag"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
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
    public function updatePartialTagAction(int $id, Request $request)
    {
        if(!$this->get('user_voter')->isLogged()){
            return $this->unauthorizedResponse();
        }

        $user = $this->get('user_voter')->getLoggedUser();

        if($user)
        {
            $requestData = $request->request->all();

            $tag = $this->getDoctrine()->getRepository('APICoreBundle:Tag')->findOneBy([
                'id' => $id,
                'user' => $user,
            ]);

            return $this->updateTag($tag,$requestData);
        }

        return $this->unauthorizedResponse();
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
    public function deleteTagAction(int $id)
    {
        if(!$this->get('user_voter')->isLogged()){
            return $this->unauthorizedResponse();
        }

        $user = $this->get('user_voter')->getLoggedUser();

        if($user) {
            $tag = $this->getDoctrine()->getRepository('APICoreBundle:Tag')->findOneBy([
                'id' => $id,
                'user' => $user,
            ]);

            if (null === $tag || !$tag instanceof Tag) {
                return $this->createApiResponse([
                    'message' => StatusCodesHelper::TAG_NOT_FOUND_MESSAGE,
                ], StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
            }

            $this->getDoctrine()->getManager()->remove($tag);
            $this->getDoctrine()->getManager()->flush();

            return $this->createApiResponse([
                'message' => StatusCodesHelper::DELETED_MESSAGE ,
            ] , StatusCodesHelper::DELETED_CODE);
        }

        return $this->unauthorizedResponse();
    }

    /**
     * @param $tag
     * @param $requestData
     * @return JsonResponse
     */
    private function updateTag($tag, $requestData)
    {
        if(null === $tag || !$tag instanceof Tag)
        {
            return $this->createApiResponse([
                'message' => StatusCodesHelper::TAG_NOT_FOUND_MESSAGE ,
            ] , StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
        }

        return $this->processTag($tag,$requestData);
    }

    /**
     * @param Tag $tag
     * @param array $requestData
     * @param bool $create
     * @return JsonResponse
     */
    private function processTag(Tag $tag, $requestData, $create = false)
    {
        if($create)
        {
            $statusCode = StatusCodesHelper::CREATED_CODE;
        }else{
            $statusCode = StatusCodesHelper::SUCCESSFUL_CODE;
        }

        $errors = $this->get('entity_processor')->processEntity($tag,$requestData);

        if (false === $errors)
        {
            $this->getDoctrine()->getManager()->persist($tag);
            $this->getDoctrine()->getManager()->flush();

            return $this->createApiResponse($this->get('api_tag.model')->getTagDataWithLinks($tag),$statusCode);
        }

        return $this->createApiResponse(['message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE , 'errors' => $errors] , StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
