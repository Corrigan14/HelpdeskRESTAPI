<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Security\UserRoleAclOptions;
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
     *            },
     *            "userHasProjects":
     *            [
     *               {
     *                  "id": 125,
     *                  "acl": "[\"create_task\"]",
     *                  "user":
     *                 {
     *                    "id": 1014,
     *                    "username": "admin",
     *                    "password": "$2y$13$oRdNpKY3bo/dj2WGDKGzCOdcX9VZuxnu2NZKfy3jV2dV8zIW8qHr6",
     *                    "email": "admin@admin.sk",
     *                    "roles": "[\"ROLE_ADMIN\"]",
     *                    "is_active": true,
     *                    "language": "AJ",
     *                    "image": null
     *                 }
     *               }
     *            ]
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
     *           "id": "1",
     *           "title": "Project 1",
     *           "description": "Description of Project 1",
     *           "createdAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "userHasProjects":
     *           [
     *              {
     *                 "id": 125,
     *                 "acl": "[\"create_task\"]",
     *                 "user":
     *                 {
     *                    "id": 1014,
     *                    "username": "admin",
     *                    "password": "$2y$13$oRdNpKY3bo/dj2WGDKGzCOdcX9VZuxnu2NZKfy3jV2dV8zIW8qHr6",
     *                    "email": "admin@admin.sk",
     *                    "roles": "[\"ROLE_ADMIN\"]",
     *                    "is_active": true,
     *                    "language": "AJ",
     *                    "image": null
     *                 }
     *              }
     *           ],
     *           "tasks":
     *           [
     *              {
     *                 "id": 20048,
     *                 "title": "Task 2",
     *                 "description": "Description of Task 2",
     *                 "deadline": null,
     *                 "startedAt": null,
     *                 "closedAt": null,
     *                 "important": false,
     *                 "work": null,
     *                 "work_time": null,
     *                 "createdAt":
     *                 {
     *                    "date": "2017-02-06 14:29:08.000000",
     *                    "timezone_type": 3,
     *                    "timezone": "Europe/Berlin"
     *                 },
     *                 "updatedAt":
     *                 {
     *                    "date": "2017-02-06 14:29:08.000000",
     *                    "timezone_type": 3,
     *                    "timezone": "Europe/Berlin"
     *                  }
     *               },
     *              {
     *                 "id": 20049,
     *                 "title": "Task 2",
     *                 "description": "Description of Task 2",
     *                 "deadline": null,
     *                 "startedAt": null,
     *                 "closedAt": null,
     *                 "important": false,
     *                 "work": null,
     *                 "work_time": null,
     *                 "createdAt":
     *                 {
     *                    "date": "2017-02-06 14:29:08.000000",
     *                    "timezone_type": 3,
     *                    "timezone": "Europe/Berlin"
     *                 },
     *                 "updatedAt":
     *                 {
     *                    "date": "2017-02-06 14:29:08.000000",
     *                    "timezone_type": 3,
     *                    "timezone": "Europe/Berlin"
     *                  }
     *              }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/projects/211",
     *           "patch": "/api/v1/task-bundle/projects/211",
     *           "delete": "/api/v1/task-bundle/projects/211"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Project Entity with list of projects tasks",
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

        if (!$this->get('project_voter')->isGranted(VoteOptions::VIEW_PROJECT, $project)) {
            return $this->accessDeniedResponse();
        }

        $projectArray = $this->get('project_service')->getEntityWithTaskResponse($id);
        return $this->json($projectArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "1",
     *           "title": "Project 1",
     *           "description": "Description of Project 1",
     *           "createdAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "userHasProjects":
     *           [
     *              {
     *                 "id": 125,
     *                 "acl": "[\"create_task\"]",
     *                 "user":
     *                 {
     *                    "id": 1014,
     *                    "username": "admin",
     *                    "password": "$2y$13$oRdNpKY3bo/dj2WGDKGzCOdcX9VZuxnu2NZKfy3jV2dV8zIW8qHr6",
     *                    "email": "admin@admin.sk",
     *                    "roles": "[\"ROLE_ADMIN\"]",
     *                    "is_active": true,
     *                    "language": "AJ",
     *                    "image": null
     *                 }
     *              }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/projects/211",
     *           "patch": "/api/v1/task-bundle/projects/211",
     *           "delete": "/api/v1/task-bundle/projects/211"
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
        $aclOptions = [
            'acl' => UserRoleAclOptions::CREATE_PROJECTS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
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
     *           "id": "1",
     *           "title": "Project 1",
     *           "description": "Description of Project 1",
     *           "createdAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "userHasProjects":
     *           [
     *              {
     *                 "id": 125,
     *                 "acl": "[\"create_task\"]",
     *                 "user":
     *                 {
     *                    "id": 1014,
     *                    "username": "admin",
     *                    "password": "$2y$13$oRdNpKY3bo/dj2WGDKGzCOdcX9VZuxnu2NZKfy3jV2dV8zIW8qHr6",
     *                    "email": "admin@admin.sk",
     *                    "roles": "[\"ROLE_ADMIN\"]",
     *                    "is_active": true,
     *                    "language": "AJ",
     *                    "image": null
     *                 }
     *              }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/projects/211",
     *           "patch": "/api/v1/task-bundle/projects/211",
     *           "delete": "/api/v1/task-bundle/projects/211"
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
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request)
    {
        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($id);

        if (!$project instanceof Project) {
            return $this->notFoundResponse();
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->getUser(),
            'project' => $project
        ]);
        if (!$this->get('project_voter')->isGranted(VoteOptions::EDIT_PROJECT, $userHasProject)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();
        return $this->updateProject($project, $requestData);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "1",
     *           "title": "Project 1",
     *           "description": "Description of Project 1",
     *           "createdAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "userHasProjects":
     *           [
     *              {
     *                 "id": 125,
     *                 "acl": "[\"create_task\"]",
     *                 "user":
     *                 {
     *                    "id": 1014,
     *                    "username": "admin",
     *                    "password": "$2y$13$oRdNpKY3bo/dj2WGDKGzCOdcX9VZuxnu2NZKfy3jV2dV8zIW8qHr6",
     *                    "email": "admin@admin.sk",
     *                    "roles": "[\"ROLE_ADMIN\"]",
     *                    "is_active": true,
     *                    "language": "AJ",
     *                    "image": null
     *                 }
     *              }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/projects/211",
     *           "patch": "/api/v1/task-bundle/projects/211",
     *           "delete": "/api/v1/task-bundle/projects/211"
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
     * @throws \LogicException
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($id);

        if (!$project instanceof Project) {
            return $this->notFoundResponse();
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->getUser(),
            'project' => $project
        ]);
        if (!$this->get('project_voter')->isGranted(VoteOptions::EDIT_PROJECT, $userHasProject)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();
        return $this->updateProject($project, $requestData);
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
     * @throws \LogicException
     */
    public function deleteAction(int $id)
    {
        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($id);

        if (!$project instanceof Project) {
            return $this->notFoundResponse();
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->getUser(),
            'project' => $project
        ]);
        if (!$this->get('project_voter')->isGranted(VoteOptions::EDIT_PROJECT, $userHasProject)) {
            return $this->accessDeniedResponse();
        }

        $project->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($project);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::UNACITVATE_MESSAGE,
        ], StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "1",
     *           "title": "Project 1",
     *           "description": "Description of Project 1",
     *           "createdAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "userHasProjects":
     *           [
     *              {
     *                 "id": 125,
     *                 "acl": "[\"create_task\"]",
     *                 "user":
     *                 {
     *                    "id": 1014,
     *                    "username": "admin",
     *                    "password": "$2y$13$oRdNpKY3bo/dj2WGDKGzCOdcX9VZuxnu2NZKfy3jV2dV8zIW8qHr6",
     *                    "email": "admin@admin.sk",
     *                    "roles": "[\"ROLE_ADMIN\"]",
     *                    "is_active": true,
     *                    "language": "AJ",
     *                    "image": null
     *                 }
     *              }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/projects/211",
     *           "patch": "/api/v1/task-bundle/projects/211",
     *           "delete": "/api/v1/task-bundle/projects/211"
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
     * @throws \LogicException
     */
    public function restoreAction(int $id)
    {
        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($id);

        if (!$project instanceof Project) {
            return $this->notFoundResponse();
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->getUser(),
            'project' => $project
        ]);
        if (!$this->get('project_voter')->isGranted(VoteOptions::EDIT_PROJECT, $userHasProject)) {
            return $this->accessDeniedResponse();
        }

        $project->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($project);
        $this->getDoctrine()->getManager()->flush();

        $response = $this->get('project_service')->getEntityResponse($project->getId());
        return $this->json($response, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *   ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "1",
     *           "title": "Project 1",
     *           "description": "Description of Project 1",
     *           "createdAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "userHasProjects":
     *           [
     *              {
     *                 "id": 125,
     *                 "acl": "[\"create_task\"]",
     *                 "user":
     *                 {
     *                    "id": 1014,
     *                    "username": "admin",
     *                    "password": "$2y$13$oRdNpKY3bo/dj2WGDKGzCOdcX9VZuxnu2NZKfy3jV2dV8zIW8qHr6",
     *                    "email": "admin@admin.sk",
     *                    "roles": "[\"ROLE_ADMIN\"]",
     *                    "is_active": true,
     *                    "language": "AJ",
     *                    "image": null
     *                 }
     *              }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/projects/211",
     *           "patch": "/api/v1/task-bundle/projects/211",
     *           "delete": "/api/v1/task-bundle/projects/211"
     *         }
     *      }
     *
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
     *      404 ="Not found user or project",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     *
     * @param int $projectId
     * @param int $userId
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function addUserToProjectAction(int $projectId, int $userId, Request $request)
    {
        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

        if (!$project instanceof Project) {
            return $this->createApiResponse([
                'message' => 'Project with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->getUser(),
            'project' => $project
        ]);
        if (!$this->get('project_voter')->isGranted(VoteOptions::EDIT_PROJECT, $userHasProject)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $existedUserHasProjectEntity = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'project' => $project,
            'user' => $user,
        ]);
        if ($existedUserHasProjectEntity instanceof UserHasProject) {
            $userHasProject = $existedUserHasProjectEntity;
        } else {
            $userHasProject = new UserHasProject();
            $userHasProject->setProject($project);
            $userHasProject->setUser($user);
        }

        return $this->updateUserHasProject($requestData, $userHasProject, true);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "1",
     *           "title": "Project 1",
     *           "description": "Description of Project 1",
     *           "createdAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "userHasProjects":
     *           [
     *              {
     *                 "id": 125,
     *                 "acl": "[\"create_task\"]",
     *                 "user":
     *                 {
     *                    "id": 1014,
     *                    "username": "admin",
     *                    "password": "$2y$13$oRdNpKY3bo/dj2WGDKGzCOdcX9VZuxnu2NZKfy3jV2dV8zIW8qHr6",
     *                    "email": "admin@admin.sk",
     *                    "roles": "[\"ROLE_ADMIN\"]",
     *                    "is_active": true,
     *                    "language": "AJ",
     *                    "image": null
     *                 }
     *              }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/projects/211",
     *           "patch": "/api/v1/task-bundle/projects/211",
     *           "delete": "/api/v1/task-bundle/projects/211"
     *         }
     *      }
     *
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
     *      404 ="Not found user or project",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     *
     * @param int $projectId
     * @param int $userId
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateUserProjectAclAction(int $projectId, int $userId, Request $request)
    {
        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

        if (!$project instanceof Project) {
            return $this->createApiResponse([
                'message' => 'Project with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->getUser(),
            'project' => $project
        ]);
        if (!$this->get('project_voter')->isGranted(VoteOptions::EDIT_PROJECT, $userHasProject)) {
            return $this->accessDeniedResponse();
        }

        $existedUserHasProjectEntity = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'project' => $project,
            'user' => $user,
        ]);

        if (!$existedUserHasProjectEntity instanceof UserHasProject) {
            return $this->createApiResponse([
                'message' => 'Users ACL in requested project was not found!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $requestData = $request->request->all();

        return $this->updateUserHasProject($requestData, $existedUserHasProjectEntity);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "1",
     *           "title": "Project 1",
     *           "description": "Description of Project 1",
     *           "createdAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *              "date": "2016-11-26 21:49:04.000000",
     *              "timezone_type": 3,
     *              "timezone": "Europe/Berlin"
     *           },
     *           "userHasProjects":
     *           [
     *              {
     *                 "id": 125,
     *                 "acl": "[\"create_task\"]",
     *                 "user":
     *                 {
     *                    "id": 1014,
     *                    "username": "admin",
     *                    "password": "$2y$13$oRdNpKY3bo/dj2WGDKGzCOdcX9VZuxnu2NZKfy3jV2dV8zIW8qHr6",
     *                    "email": "admin@admin.sk",
     *                    "roles": "[\"ROLE_ADMIN\"]",
     *                    "is_active": true,
     *                    "language": "AJ",
     *                    "image": null
     *                 }
     *              }
     *           ]
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/projects/211",
     *           "patch": "/api/v1/task-bundle/projects/211",
     *           "delete": "/api/v1/task-bundle/projects/211"
     *         }
     *      }
     *
     *
     * @ApiDoc(
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
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      204 ="The entity was successfully deleted",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found user or project"
     *  }
     * )
     *
     *
     * @param int $projectId
     * @param int $userId
     * @return JsonResponse|Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function removeUserFromProjectAction(int $projectId, int $userId)
    {
        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

        if (!$project instanceof Project) {
            return $this->createApiResponse([
                'message' => 'Project with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            return $this->createApiResponse([
                'message' => 'User with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->getUser(),
            'project' => $project
        ]);
        if (!$this->get('project_voter')->isGranted(VoteOptions::EDIT_PROJECT, $userHasProject)) {
            return $this->accessDeniedResponse();
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'project' => $project,
            'user' => $user
        ]);

        if ($userHasProject instanceof UserHasProject) {
            $this->getDoctrine()->getManager()->remove($userHasProject);
            $this->getDoctrine()->getManager()->flush();

            return $this->createApiResponse([
                'message' => StatusCodesHelper::DELETED_MESSAGE,
            ], StatusCodesHelper::DELETED_CODE);
        }

        return $this->createApiResponse([
            'message' => 'The requested User did not have permission to requested Project!',
        ], StatusCodesHelper::NOT_FOUND_CODE);

    }

    /**
     * @param Project $project
     * @param array $requestData
     * @param bool $create
     * @throws \LogicException
     *
     * @return Response|JsonResponse
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateProject(Project $project, $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($project, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            if ($create) {
                $this->addProjectAclPermmisionToCreatorOfProject($project);
            }

            $response = $this->get('project_service')->getEntityResponse($project->getId());
            return $this->json($response, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param array $requestData
     * @param UserHasProject $userHasProject
     * @param bool $create
     * @throws \LogicException
     *
     * @return Response|JsonResponse
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateUserHasProject(array $requestData, UserHasProject $userHasProject, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        if (array_key_exists('acl', $requestData)) {
            $acl = $requestData['acl'];

            if (!is_array($acl)) {
                $acl = explode(',', $acl);
                $requestData['acl'] = $acl;
            }

            // Check if all ACL are from allowed options
            foreach ($acl as $key => $value) {
                if (!in_array($value, ProjectAclOptions::getConstants(), true)) {
                    return $this->createApiResponse([
                        'message' => $value . ' ACL is not allowed!',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
        }

        $errors = $this->get('entity_processor')->processEntity($userHasProject, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($userHasProject);
            $this->getDoctrine()->getManager()->flush();

            $response = $this->get('project_service')->getEntityResponse($userHasProject->getProject()->getId());
            return $this->json($response, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param Project $project
     */
    private function addProjectAclPermmisionToCreatorOfProject(Project $project)
    {
        $userHasProject = new UserHasProject();
        $userHasProject->setProject($project);
        $userHasProject->setUser($this->getUser());
        $acl = [
            ProjectAclOptions::VIEW_OWN_TASKS,
            ProjectAclOptions::VIEW_TASKS_FROM_USERS_COMPANY,
            ProjectAclOptions::VIEW_ALL_TASKS,
            ProjectAclOptions::CREATE_TASK,
            ProjectAclOptions::RESOLVE_TASK,
            ProjectAclOptions::DELETE_TASK,
            ProjectAclOptions::VIEW_INTERNAL_NOTE,
            ProjectAclOptions::EDIT_INTERNAL_NOTE,
            ProjectAclOptions::EDIT_PROJECT,
        ];
        $userHasProject->setAcl($acl);
        $this->getDoctrine()->getManager()->persist($userHasProject);
        $this->getDoctrine()->getManager()->flush();
    }
}
