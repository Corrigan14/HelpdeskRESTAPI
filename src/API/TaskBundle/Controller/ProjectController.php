<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
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
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ]
     *          },
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
     *  description="Returns a list of logged User's Projects: created and based on his ACL (user_has_project: every project where user has some ACL),
     * Admin can see all projects.",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE project if this param is TRUE, only INACTIVE projects if param is FALSE"
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
     *      403 ="Access denied"
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('projects_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        if (!$this->get('project_voter')->isGranted(VoteOptions::LIST_PROJECTS)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);

            $page = $processedFilterParams['page'];
            $limit = $processedFilterParams['limit'];
            $order = $processedFilterParams['order'];
            $isActive = $processedFilterParams['isActive'];

            $filtersForUrl = [
                'isActive' => '&isActive=' . $isActive,
                'order' => '&order=' . $order,
            ];

            $options = [
                'isAdmin' => $this->get('project_voter')->isAdmin(),
                'loggedUserId' => $this->getUser()->getId(),
                'isActive' => $isActive,
                'limit' => $limit,
                'order' => $order,
                'filtersForUrl' => $filtersForUrl
            ];

            $projectsArray = $this->get('project_service')->getProjectsResponse($page, $options);
            $response = $response->setContent(json_encode($projectsArray));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }

        return $response;
    }


    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *          },
     *          {
     *             "id": 38,
     *             "title": "INBOX",
     *             "description": "INBOX - main project",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active": true
     *          },
     *       ]
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of logged User's Active Projects where he can create tasks (user_has_project: CREATE_TASK ACL).
     *  USAGE: list of project when task is being created. For ADMIN all projects are returned.",
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
     * @return JsonResponse|Response
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listOfProjectsWhereCanCreateTasksLoggedUserAction()
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('projects_list_where_logged_user_can_create_tasks');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $loggedUsersProjects = $loggedUser->getUserHasProjects();
        $isAdmin = $this->get('project_voter')->isAdmin();

        $projectWhereLoggedUserCanCreateTasks = [];
        if (!$isAdmin) {
            if (\count($loggedUsersProjects) > 0) {
                /** @var UserHasProject $userHasProject */
                foreach ($loggedUsersProjects as $userHasProject) {
                    $projectAcl = $userHasProject->getAcl();
                    $project = $userHasProject->getProject();
                    if (\in_array(ProjectAclOptions::CREATE_TASK, $projectAcl, true) && $project->getIsActive() === true) {
                        $projectWhereLoggedUserCanCreateTasks[] = [
                            'id' => $project->getId(),
                            'title' => $project->getTitle(),
                            'description' => $project->getDescription(),
                            'createdAt' => $project->getCreatedAt(),
                            'updatedAt' => $project->getUpdatedAt(),
                            'is_active' => $project->getIsActive()
                        ];
                    }
                }
            }
        } else {
            $existedActiveProjects = $this->getDoctrine()->getRepository('APITaskBundle:Project')->findBy([
                'is_active' => true
            ]);
            foreach ($existedActiveProjects as $project) {
                $projectWhereLoggedUserCanCreateTasks[] = [
                    'id' => $project->getId(),
                    'title' => $project->getTitle(),
                    'description' => $project->getDescription(),
                    'createdAt' => $project->getCreatedAt(),
                    'updatedAt' => $project->getUpdatedAt(),
                    'is_active' => $project->getIsActive()
                ];
            }
        }
        $responseArray['data'] = $projectWhereLoggedUserCanCreateTasks;

        $response = $response->setContent(json_encode($responseArray));
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ],
     *             "canEdit": true
     *         }
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/projects/211",
     *           "patch": "/api/v1/task-bundle/projects/211",
     *           "delete": "/api/v1/task-bundle/projects/211"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Project Entity.",
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

        $canEdit = $this->canEditProject($project);

        $projectArray = $this->get('project_service')->getEntityResponse($id, $canEdit);
        return $this->json($projectArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ],
     *             "canEdit": true
     *          }
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
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
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
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ],
     *             "canEdit": true
     *         },
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
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
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
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ],
     *             "canEdit": true
     *         },
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
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
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
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ],
     *             "canEdit": true
     *         },
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

        $canEdit = true;
        $response = $this->get('project_service')->getEntityResponse($project->getId(), $canEdit);
        return $this->json($response, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *   ### Response ###
     *      {
     *        "data":
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ],
     *             "canEdit": true
     *         }
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

        //Check if logged user can Update ACL in requested project
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
            $userHasProjectAdd = $existedUserHasProjectEntity;
        } else {
            $userHasProjectAdd = new UserHasProject();
            $userHasProjectAdd->setProject($project);
            $userHasProjectAdd->setUser($user);
        }

        return $this->updateUserHasProject($requestData, $userHasProjectAdd, true);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ],
     *             "canEdit": true
     *         }
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
     *  description="Update users ACL of project",
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
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ],
     *             "canEdit": true
     *         }
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
     *  ### Response ###
     *      {
     *        "data":
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of Project 1",
     *             "createdAt": 1507968483,
     *             "updatedAt": 1507968483,
     *             "is_active" => true,
     *             "userHasProjects":
     *             [
     *                {
     *                   "id": 300,
     *                   "user":
     *                   {
     *                      "id": 2575,
     *                      "username": "admin",
     *                      "email": "admin@admin.sk"
     *                    },
     *                    "acl":
     *                    [
     *                      "view_own_tasks"
     *                    ]
     *                },
     *                {
     *                   "id": 301,
     *                   "user":
     *                   {
     *                      "id": 2576,
     *                      "username": "manager",
     *                      "email": "manager@manager.sk"
     *                    },
     *                   "acl":
     *                   [
     *                      "view_own_tasks"
     *                   ]
     *                },
     *             ],
     *             "canEdit": true
     *         }
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
     *  description="Add or update users ACL of project. Input: [userId => [projectAcl1, projectAcl2]].
     *               Return project details with list of users with their project ACL.",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     }
     *  },
     *  parameters={
     *      {"name"="usersAcl", "dataType"="string", "required"=true, "description"="Array of users with array of its project ACL: [userId => [projectAcl1, projectAcl2]]"}
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="Data were successfully processed",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found project",
     *      409 ="Invalid parameters"
     *  }
     * )
     *
     * @param Request $request
     * @param int $projectId
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function processProjectAclForUsersAction(Request $request, int $projectId)
    {
        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

        if (!$project instanceof Project) {
            return $this->createApiResponse([
                'message' => 'Project with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->getUser(),
            'project' => $project
        ]);
        if (!$this->get('project_voter')->isGranted(VoteOptions::EDIT_PROJECT, $userHasProject)) {
            return $this->accessDeniedResponse();
        }

        // Create or update UserHasProject Entity for every sent User
        $requestData = json_decode($request->getContent(), true);

        if (count($requestData) > 0) {
            foreach ($requestData as $key => $aclArray) {
                $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($key);
                if (!$user instanceof User) {
                    return $this->createApiResponse([
                        'message' => 'User with requested Id ' . $key . ' does not exist!',
                    ], StatusCodesHelper::NOT_FOUND_CODE);
                }

                // Check if all ACL are from allowed options
                if (is_array($aclArray)) {
                    foreach ($aclArray as $acl) {
                        if (!in_array($acl, ProjectAclOptions::getConstants(), true)) {
                            return $this->createApiResponse([
                                'message' => $acl . ' ACL is not allowed!',
                            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        }
                    }

                    // Check if requested user is ADMIN. If yes, his EDIT_PROJECT permission can't be changed
                    $userRoles = $user->getRoles();
                    if (in_array('ROLE_ADMIN', $userRoles, true)) {
                        if (!in_array(ProjectAclOptions::EDIT_PROJECT, $aclArray)) {
                            return $this->createApiResponse([
                                'message' => 'EDIT_RPOJECT ACL is for ADMIN required!',
                            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        }
                    }

                    // Check if it is an UPDATE of existed user's ACL
                    $userHasProjectNew = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
                        'user' => $user,
                        'project' => $project
                    ]);
                    if ($userHasProjectNew instanceof UserHasProject) {
                        $userHasProjectNew->setAcl($aclArray);
                        $this->getDoctrine()->getManager()->persist($userHasProjectNew);
                    } else {
                        $userHasProjectNewAdd = new UserHasProject();
                        $userHasProjectNewAdd->setProject($project);
                        $userHasProjectNewAdd->setUser($user);
                        $userHasProjectNewAdd->setAcl($aclArray);
                        $this->getDoctrine()->getManager()->persist($userHasProjectNewAdd);
                    }
                } else {
                    return $this->createApiResponse([
                        'message' => 'ACL for every user has to be an array, which includes only allowed parameters from ProjectAclOptions!',
                    ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                }
            }
            $this->getDoctrine()->getManager()->flush();
        }
        $canEdit = true;
        $response = $this->get('project_service')->getEntityResponse($project->getId(), $canEdit);
        return $this->json($response, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *
     *          "assigner":
     *          [
     *            {
     *               "id": 1014,
     *               "username": "admin"
     *            }
     *          ],
     *
     *      }
     * @ApiDoc(
     *  description="Get all possible assigners for project/task",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of the project"
     *     },
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of the task"
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
     *      403 ="Access denied",
     *      404 ="Not found Entity"
     *  })
     *
     * @param int|bool $projectId
     * @param int|bool $taskId
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function getProjectAssignersAction($projectId = false, $taskId = false)
    {
        $assignArray = [];

        if ($taskId) {
            $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

            if (!$task instanceof Task) {
                return $this->createApiResponse([
                    'message' => 'Task with requested id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            // Check if user can update selected task
            if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
                return $this->accessDeniedResponse();
            }

            // Available assigners are based on project of the task
            $projectOfTask = $task->getProject();

            if (!$projectOfTask instanceof Project) {
                // If task has not project, just creator of the task can be assigned to it
                $assignArray = [
                    [
                        'id' => $task->getCreatedBy()->getId(),
                        'username' => $task->getCreatedBy()->getUsername()
                    ]
                ];
            } else {
                // If task has project, assigner has to have RESOLVE_TASK ACL in user_has_project
                $assignArray = $this->get('api_user.service')->getListOfAvailableProjectAssigners($projectOfTask, ProjectAclOptions::RESOLVE_TASK);
            }
        }

        if ($projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            // Check if user can view requested Project
            if (!$this->get('project_voter')->isGranted(VoteOptions::VIEW_PROJECT, $project)) {
                return $this->accessDeniedResponse();
            }

            // Available assigners are based on project of the task
            // If task has project, assigner has to have RESOLVE_TASK ACL in user_has_project
            $assignArray = $this->get('api_user.service')->getListOfAvailableProjectAssigners($project, ProjectAclOptions::RESOLVE_TASK);
        }

        $response = [
            'assigner' => $assignArray,
        ];
        return $this->json($response, StatusCodesHelper::SUCCESSFUL_CODE);
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
        $allowedUnitEntityParams = [
            'title',
            'description',
            'is_active'
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

        $errors = $this->get('entity_processor')->processEntity($project, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($project);

            $this->getDoctrine()->getManager()->flush();

            if ($create) {
                // Every creator has full Project ACL automatically added
                $addedAcl = [];
                $addedAcl[] = $this->addProjectAclPermmisionToCreatorOfProject($project);

                // Admin has full Project ACL automatically added and his ACL is not possible to delete
//                $addedAdminAcl = $this->addProjectAclPermissionToAdmin($project);

                $canEdit = true;
                $response = $this->get('project_service')->getEntityResponse($project->getId(), $canEdit);
                $response['data']['userHasProjects'] = $addedAcl;
                return $this->json($response, $statusCode);
            } else {
                $canEdit = true;
                $response = $this->get('project_service')->getEntityResponse($project->getId(), $canEdit);
                return $this->json($response, $statusCode);
            }
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
                if (!\in_array($value, ProjectAclOptions::getConstants(), true)) {
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

            $response = $this->get('project_service')->getEntityResponse($userHasProject->getProject()->getId(), true);
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
     * @return array
     * @throws \LogicException
     * @throws \InvalidArgumentException
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
        $this->getDoctrine()->getManager()->persist($project);
        $this->getDoctrine()->getManager()->flush();

        return [
            'id' => $userHasProject->getId(),
            'user' => [
                'id' => $userHasProject->getUser()->getId(),
                'username' => $userHasProject->getUser()->getUsername(),
                'email' => $userHasProject->getUser()->getEmail()
            ],
            'acl' => $userHasProject->getAcl()
        ];
    }

    /**
     * @param Project $project
     * @return array
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function addProjectAclPermissionToAdmin(Project $project)
    {
        $addedAdminAcl = [];
        $adminArray = [];

        //Find All ADMIN User(s)
        $admins = $this->getDoctrine()->getRepository('APICoreBundle:User')->findAll();
        foreach ($admins as $admin) {
            $roles = $admin->getRoles();
            if (in_array('ROLE_ADMIN', $roles, true)) {
                $adminArray [] = $admin;
            }
        }

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

        if (count($adminArray) > 0) {
            /** @var User $admin */
            foreach ($adminArray as $admin) {
                $userHasProject = new UserHasProject();
                $userHasProject->setProject($project);
                $userHasProject->setUser($admin);
                $userHasProject->setAcl($acl);
                $this->getDoctrine()->getManager()->persist($userHasProject);
                $this->getDoctrine()->getManager()->persist($project);
                $this->getDoctrine()->getManager()->flush();
                $addedAdminAcl[] = [
                    'id' => $userHasProject->getId(),
                    'user' => [
                        'id' => $admin->getId(),
                        'username' => $admin->getUsername(),
                        'email' => $admin->getEmail()
                    ],
                    'acl' => $acl
                ];
            }
        }
        return $addedAdminAcl;
    }

    /**
     * @param Project $project
     * @return bool
     * @throws \LogicException
     */
    private function canEditProject(Project $project): bool
    {
        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $this->getUser(),
            'project' => $project
        ]);

        if ($userHasProject instanceof UserHasProject) {
            if ($this->get('project_voter')->isGranted(VoteOptions::EDIT_PROJECT, $userHasProject)) {
                return true;
            }
        }

        return false;
    }
}
