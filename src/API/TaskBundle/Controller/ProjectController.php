<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProjectController
 *
 * @package API\TaskBundle\Controller
 */
class ProjectController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *            "id": "1",
     *            "title": "Project 1",
     *            "description": "Description of Project 1",
     *            "createdAt":
     *            {
     *               "date": "2016-11-26 21:49:04.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "updatedAt":
     *            {
     *               "date": "2016-11-26 21:49:04.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            }
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/project?page=1&isActive=true",
     *           "first": "/api/v1/task-bundle/project?page=1&isActive=true",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/project?page=2&isActive=true",
     *           "last": "/api/v1/task-bundle/project?page=3&isActive=true"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of logged User's Projects: created and based on ACL (user_has_project: every project where user has some ACL)",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE project if this param is TRUE, only INACTIVE projects if param is FALSE"
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
     *      403 ="Access denied"
     *  }
     * )
     *
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request)
    {
        if (!$this->get('project_voter')->isGranted(VoteOptions::LIST_PROJECTS)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;
        $isActive = $request->get('isActive') ?: 'all';

        $options = [
            'isAdmin' => $this->get('project_voter')->isAdmin(),
            'loggedUser' => $this->getUser(),
            'isActive' => strtolower($isActive),
        ];

        return $this->json($this->get('project_service')->getProjectsResponse($page, $options), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "2",
     *            "title": "Project 1",
     *            "description": "Description of Project 1",
     *            "is_active": true,
     *            "created_by":
     *           {
     *              "id": 19,
     *              "username": "user",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "acl": "[]",
     *              "company":
     *              {
     *                 "id": 86,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *               }
     *          }
     *          "created_at": "2016-11-26T21:49:04+0100",
     *          "updated_at": "2016-11-26T21:49:04+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/projects/id",
     *           "patch": "/api/v1/projects/id",
     *           "delete": "/api/v1/projects/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Project Entity",
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
     *  output="API\TaskBundle\Entity\Project",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     * @return Response|JsonResponse
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($id);

        if (!$project instanceof Project) {
            return $this->notFoundResponse();
        }

        if (!$this->get('project_voter')->isGranted(VoteOptions::SHOW_PROJECT, $project)) {
            return $this->accessDeniedResponse();
        }

        $projectArray = $this->get('project_service')->getProjectResponse($project);

        return $this->createApiResponse($projectArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "2",
     *            "title": "Project 1",
     *            "description": "Description of Project 1",
     *            "is_active": true,
     *            "created_by":
     *           {
     *              "id": 19,
     *              "username": "user",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "acl": "[]",
     *              "company":
     *              {
     *                 "id": 86,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *               }
     *          }
     *          "created_at": "2016-11-26T21:49:04+0100",
     *          "updated_at": "2016-11-26T21:49:04+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/projects/id",
     *           "patch": "/api/v1/projects/id",
     *           "delete": "/api/v1/projects/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Project Entity",
     *  input={"class"="API\TaskBundle\Entity\Project"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Project"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return Response|JsonResponse
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request)
    {
        if (!$this->get('project_voter')->isGranted(VoteOptions::CREATE_PROJECT)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $project = new Project();
        $project->setCreatedBy($this->getUser());
        $project->setIsActive(true);

        return $this->updateProject($project, $requestData, true);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "2",
     *            "title": "Project 1",
     *            "description": "Description of Project 1",
     *            "is_active": true,
     *            "created_by":
     *           {
     *              "id": 19,
     *              "username": "user",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "acl": "[]",
     *              "company":
     *              {
     *                 "id": 86,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *               }
     *          }
     *          "created_at": "2016-11-26T21:49:04+0100",
     *          "updated_at": "2016-11-26T21:49:04+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/projects/id",
     *           "patch": "/api/v1/projects/id",
     *           "delete": "/api/v1/projects/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the Project Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Project"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Project"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function updateAction(int $id, Request $request)
    {
        // TODO: Implement updateAction() method.
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "2",
     *            "title": "Project 1",
     *            "description": "Description of Project 1",
     *            "is_active": true,
     *            "created_by":
     *           {
     *              "id": 19,
     *              "username": "user",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "acl": "[]",
     *              "company":
     *              {
     *                 "id": 86,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *               }
     *          }
     *          "created_at": "2016-11-26T21:49:04+0100",
     *          "updated_at": "2016-11-26T21:49:04+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/projects/id",
     *           "patch": "/api/v1/projects/id",
     *           "delete": "/api/v1/projects/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the Project Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Project"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Project"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function updatePartialAction(int $id, Request $request)
    {
        // TODO: Implement updatePartialAction() method.
    }

    /**
     * @ApiDoc(
     *  description="Delete Project Entity",
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
     *      200 ="is_active param of Entity was successfully changed to inactive: 0",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     * @return Response|JsonResponse
     */
    public function deleteAction(int $id)
    {
        // TODO: Implement deleteAction() method.
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "2",
     *            "title": "Project 1",
     *            "description": "Description of Project 1",
     *            "is_active": true,
     *            "created_by":
     *           {
     *              "id": 19,
     *              "username": "user",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "acl": "[]",
     *              "company":
     *              {
     *                 "id": 86,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *               }
     *          }
     *          "created_at": "2016-11-26T21:49:04+0100",
     *          "updated_at": "2016-11-26T21:49:04+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/projects/id",
     *           "patch": "/api/v1/projects/id",
     *           "delete": "/api/v1/projects/id"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Restore Project Entity",
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
     *  output={"class"="API\TaskBundle\Entity\Project"},
     *  statusCodes={
     *      200 ="is_active param of Entity was successfully changed to active: 1",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found user",
     *  })
     *
     * @param int $id
     *
     * @return Response|JsonResponse
     */
    public function restoreAction(int $id)
    {

    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "2",
     *
     *        }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Add users ACL to project",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     },
     *     {
     *       "name"="userId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user"
     *     },
     *  },
     *  input={"class"="API\TaskBundle\Entity\UserHasProject"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\UserHasProject"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     *
     * @param int $projectId
     * @param int $userId
     * @return JsonResponse|Response
     */
    public function addUserToProjectAction(int $projectId, int $userId)
    {

    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "2",
     *
     *        }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Remove users ACL from project",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     },
     *     {
     *       "name"="userId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user"
     *     },
     *  },
     *  input={"class"="API\TaskBundle\Entity\UserHasProject"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\UserHasProject"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     *
     * @param int $projectId
     * @param int $userId
     * @return JsonResponse|Response
     */
    public function updateUserProjectAclAction(int $projectId, int $userId)
    {

    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *            "id": "2",
     *
     *        }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Remove users ACL from project",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     },
     *     {
     *       "name"="userId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user"
     *     },
     *  },
     *  input={"class"="API\TaskBundle\Entity\UserHasProject"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\UserHasProject"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     *
     * @param int $projectId
     * @param int $userId
     * @return JsonResponse|Response
     */
    public function removeUserFromProjectAction(int $projectId, int $userId)
    {

    }

    /**
     * @param Project $project
     * @param array $requestData
     * @param bool $create
     * @throws \LogicException
     *
     * @return Response|JsonResponse
     */
    private function updateProject(Project $project, $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($project, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            $response = $this->get('project_service')->getProjectResponse($project);
            return $this->createApiResponse($response, $statusCode);
        }

        return $this->invalidParametersResponse();
    }
}
