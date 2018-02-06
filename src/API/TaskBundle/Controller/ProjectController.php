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
     *            "put": "/api/v1/task-bundle/projects/23",
     *            "inactivate": "/api/v1/task-bundle/projects/23",
     *            "restore": "/api/v1/task-bundle/projects/restore/23"
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('project', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($id);

        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        // User can see a project if he has VIEW_TASKS rights in this project
        if (!$this->get('project_voter')->isGranted(VoteOptions::VIEW_PROJECT, $project)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        // User can edit project if he has EDIT_PROJECT ACL right or he ia a ADMIN
        $canEdit = $this->canEditProject($project);

        $projectArray = $this->get('project_service')->getEntityResponse($id, $canEdit);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($projectArray));
        return $response;
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of a Project 1",
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
     *           "put": "/api/v1/task-bundle/projects/23",
     *           "inactivate": "/api/v1/task-bundle/projects/23",
     *           "restore": "/api/v1/task-bundle/projects/restore/23"
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
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('projects_create');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::CREATE_PROJECTS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $project = new Project();
        $project->setCreatedBy($this->getUser());
        $project->setIsActive(true);

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        return $this->updateProject($project, $requestBody, true, $locationURL);
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
     *           "put": "/api/v1/task-bundle/projects/23",
     *           "inactivate": "/api/v1/task-bundle/projects/23",
     *           "restore": "/api/v1/task-bundle/projects/restore/23"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update a Project Entity",
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('projects_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($id);

        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        if (!$this->canEditProject($project)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateProject($project, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Inactivate Project Entity",
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
     * @return Response
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function deleteAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('projects_delete', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($id);

        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        if (!$this->canEditProject($project)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $project->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($project);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::UNACITVATE_MESSAGE]));
        return $response;
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
     *           "put": "/api/v1/task-bundle/projects/23",
     *           "inactivate": "/api/v1/task-bundle/projects/23",
     *           "restore": "/api/v1/task-bundle/projects/restore/23"
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function restoreAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('projects_restore', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($id);

        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        if (!$this->canEditProject($project)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $project->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($project);
        $this->getDoctrine()->getManager()->flush();

        $canEdit = true;
        $projectArray = $this->get('project_service')->getEntityResponse($project->getId(), $canEdit);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($projectArray));
        return $response;
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
     *           "put": "/api/v1/task-bundle/projects/23",
     *           "inactivate": "/api/v1/task-bundle/projects/23",
     *           "restore": "/api/v1/task-bundle/projects/restore/23"
     *         }
     *      }
     *
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Add/Update user's ACL to a selected project.",
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
     *  output={"class"="API\TaskBundle\Entity\Project"},
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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function addUserToProjectAction(int $projectId, int $userId, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('project_add_to_user', ['projectId' => $projectId, 'userId' => $userId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User with requested Id does not exist!']));
            return $response;
        }

        //Check if logged user can Update ACL in requested project
        if (!$this->canEditProject($project)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

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

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        return $this->updateUserHasProject($requestBody, $userHasProjectAdd, true, $locationURL);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of a Project 1",
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
     *           "put": "/api/v1/task-bundle/projects/23",
     *           "inactivate": "/api/v1/task-bundle/projects/23",
     *           "restore": "/api/v1/task-bundle/projects/restore/23"
     *         }
     *      }
     *
     *
     * @ApiDoc(
     *  description="Remove users ACL from a project",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a project"
     *     },
     *     {
     *       "name"="userId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of a user"
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
     *      200 ="The user was successfully removed from the project",
     *      400 ="Bad Request",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found user or project"
     *  }
     * )
     *
     *
     * @param int $projectId
     * @param int $userId
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function removeUserFromProjectAction(int $projectId, int $userId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('project_remove_from_user', ['projectId' => $projectId, 'userId' => $userId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($userId);

        if (!$user instanceof User) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'User with requested Id does not exist!']));
            return $response;
        }

        //Check if logged user can Update ACL in requested project
        if (!$this->canEditProject($project)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'project' => $project,
            'user' => $user
        ]);

        if ($userHasProject instanceof UserHasProject) {
            $this->getDoctrine()->getManager()->remove($userHasProject);
            $this->getDoctrine()->getManager()->flush();

            $projectArray = $this->get('project_service')->getEntityResponse($projectId, true);

            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode($projectArray));
            return $response;
        }

        $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
        $response = $response->setContent(json_encode(['message' => 'Requested user does not have an ACL permission to the requested project!']));
        return $response;

    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *         {
     *             "id": "1",
     *             "title": "Project 1",
     *             "description": "Description of a Project 1",
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
     *           "put": "/api/v1/task-bundle/projects/23",
     *           "inactivate": "/api/v1/task-bundle/projects/23",
     *           "restore": "/api/v1/task-bundle/projects/restore/23"
     *         }
     *      }
     *
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Add/Update MORE than one user's ACL to a project. Expected Input: [userId => [projectAcl1, projectAcl2]].
     *               Return project details with the list of users with their project ACL.",
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function processProjectAclForMoreUsersAction(Request $request, int $projectId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('project_process_users_acl', ['projectId' => $projectId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        //Check if logged user can Update ACL in requested project
        if (!$this->canEditProject($project)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        // Create or update UserHasProject Entity for every sent User
        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {

            if (isset($requestBody['usersAcl'])) {
                $usersACL = json_decode($requestBody['usersAcl'], true);
                $projectAclOptions = ProjectAclOptions::getConstants();

                $correctData = $this->checkIfIsCorrectArray($usersACL);

                if ($correctData) {
                    foreach ($usersACL as $key => $aclArray) {
                        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($key);
                        if (!$user instanceof User) {
                            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                            $response = $response->setContent(json_encode(['message' => 'User with requested Id: ' . $key . ' does not exist!']));
                            return $response;
                        }

                        // Check if all requested ACL are from allowed options

                        foreach ($aclArray as $acl) {
                            if (!\in_array($acl, $projectAclOptions, true)) {
                                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                                $response = $response->setContent(json_encode(['message' => $acl . ' ACL is not allowed!' . 'Allowed ACL: ' . implode(',', $projectAclOptions)]));
                                return $response;
                            }
                        }

                        // Check if requested user is ADMIN. If yes, his EDIT_PROJECT permission can't be changed
                        $userRoles = $user->getRoles();
                        if (\in_array('ROLE_ADMIN', $userRoles, true)) {
                            if (!\in_array(ProjectAclOptions::EDIT_PROJECT, $aclArray, true)) {
                                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                                $response = $response->setContent(json_encode(['message' => 'EDIT_PROJECT ACL is for ADMIN required!']));
                                return $response;
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

                    }
                    $this->getDoctrine()->getManager()->flush();

                    $projectArray = $this->get('project_service')->getEntityResponse($projectId, true);

                    $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
                    $response = $response->setContent(json_encode($projectArray));
                    return $response;
                } else {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'usersAcl array for processing is required!']));
                    return $response;
                }
            } else {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'usersAcl array for processing is required!']));
                return $response;
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;

    }

    /**
     * @param Project $project
     * @return array
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function addProjectAclPermissionToCreatorOfProject(Project $project): array
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
     * @param array $requestData
     * @param bool $create
     * @param $locationUrl
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return Response
     */
    private function updateProject(Project $project, $requestData, $create = false, $locationUrl): Response
    {
        $allowedUnitEntityParams = [
            'title',
            'description'
        ];

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);

        if (false !== $requestData) {
            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
            }

            foreach ($requestData as $key => $value) {
                if (!\in_array($key, $allowedUnitEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for a Project Entity!']));
                    return $response;
                }
            }

            $statusCode = $this->getCreateUpdateStatusCode($create);

            $errors = $this->get('entity_processor')->processEntity($project, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($project);
                $this->getDoctrine()->getManager()->flush();

                if ($create) {
                    // Every creator has full Project ACL automatically added
                    $addedAcl[] = $this->addProjectAclPermissionToCreatorOfProject($project);

                    $canEdit = true;
                    $projectArray = $this->get('project_service')->getEntityResponse($project->getId(), $canEdit);
                    $projectArray['data']['userHasProjects'] = $addedAcl;

                    $response = $response->setStatusCode($statusCode);
                    $response = $response->setContent(json_encode($projectArray));
                    return $response;
                } else {
                    $canEdit = true;
                    $projectArray = $this->get('project_service')->getEntityResponse($project->getId(), $canEdit);

                    $response = $response->setStatusCode($statusCode);
                    $response = $response->setContent(json_encode($projectArray));
                    return $response;
                }
            }
            $data = [
                'errors' => $errors,
                'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
            ];
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode($data));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;
    }

    /**
     * @param array $requestData
     * @param $locationUrl
     * @param UserHasProject $userHasProject
     * @param bool $create
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateUserHasProject(array $requestData, UserHasProject $userHasProject, $create = false, $locationUrl): Response
    {
        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);

        $statusCode = $this->getCreateUpdateStatusCode($create);
        if (false !== $requestData) {
            if (array_key_exists('acl', $requestData)) {
                $aclData = json_decode($requestData['acl'], true);
                if (!\is_array($aclData)) {
                    $aclData = explode(',', $requestData['acl']);
                }

                // Check if all ACL are from allowed options
                if (!empty($aclData)) {
                    foreach ($aclData as $key => $value) {
                        $projectAclOptions = ProjectAclOptions::getConstants();
                        if (!\in_array($value, $projectAclOptions, true)) {
                            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                            $response = $response->setContent(json_encode(['message' => $value . ' ACL is not allowed! Allowed params are: ' . implode(',', $projectAclOptions)]));
                            return $response;
                        }
                    }
                }
                $requestData['acl'] = $aclData;
            }

            $errors = $this->get('entity_processor')->processEntity($userHasProject, $requestData);

            if (false === $errors) {
                $this->getDoctrine()->getManager()->persist($userHasProject);
                $this->getDoctrine()->getManager()->flush();

                $projectArray = $this->get('project_service')->getEntityResponse($userHasProject->getProject()->getId(), true);
                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($projectArray));
                return $response;
            } else {
                $data = [
                    'errors' => $errors,
                    'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
                ];
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode($data));
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT]));
        }
        return $response;
    }

    /**
     * @param Project $project
     * @return bool
     * @throws \LogicException
     */
    private function canEditProject(Project $project): bool
    {
        if ($this->get('project_voter')->isAdmin()) {
            return true;
        }

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

    /**
     * @param $dataArray
     * @return bool
     */
    private function checkIfIsCorrectArray($dataArray): bool
    {
        if (\count($dataArray) === 0) {
            return false;
        }

        foreach ($dataArray as $key => $aclArray) {
            if (!\is_array($aclArray) || \count($aclArray) === 0) {
                return false;
            }
        }

        return true;
    }
}
