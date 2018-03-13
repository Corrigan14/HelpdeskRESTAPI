<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MainController
 * @package API\TaskBundle\Controller
 */
class MainController extends ApiBaseController
{
    /**
     *  ### Response ###
     *      {
     *         "filters":
     *         [
     *            {
     *               {
     *                  "id": 39,
     *                  "title": "Test",
     *                  "public": true,
     *                  "filter":
     *                  {
     *                     "status": "1,2",
     *                     "assigned": "not,current-user"
     *                  },
     *                  "report": false,
     *                  "is_active": true,
     *                  "default": false,
     *                  "icon_class": "ggg",
     *                  "order": 4,
     *                  "createdBy":
     *                  {
     *                    "id": 734,
     *                    "username": "admin",
     *                    "email": "admin@admin.sk"
     *                  },
     *                  "project": null,
     *                  "columns":
     *                  [
     *                     "title",
     *                     "creator",
     *                     "company",
     *                     "assigned",
     *                     "createdTime",
     *                     "deadlineTime",
     *                     "status"
     *                   ]
     *                },
     *            }
     *         ],
     *         "projects":
     *         [
     *            "id": 54,
     *            "title": "Test 77",
     *            "description": "just test 2",
     *            "createdAt":1507968483,
     *            "updatedAt":1507968483,
     *            "is_active": true,
     *            "userHasProjects":
     *            [
     *              {
     *                "id": 358,
     *                "user":
     *                {
     *                   "id": 734,
     *                   "username": "admin",
     *                   "email": "admin@admin.sk"
     *                },
     *                "acl":
     *                [
     *                   "edit_project",
     *                   "create_task",
     *                   "resolve_task",
     *                   "delete_task",
     *                   "view_internal_note",
     *                   "view_all_tasks",
     *                   "view_own_tasks",
     *                   "view_tasks_from_users_company"
     *                ]
     *              },
     *              {
     *                "id": 359,
     *                "user":
     *                {
     *                   "id": 743,
     *                   "username": "customer5",
     *                   "email": "customer@customer5.sk"
     *                },
     *                "acl":
     *                [
     *                   "edit_project",
     *                   "create_task",
     *                   "resolve_task",
     *                   "delete_task",
     *                   "view_internal_note",
     *                   "view_all_tasks",
     *                   "view_own_tasks",
     *                   "view_tasks_from_users_company"
     *                 ]
     *              }
     *            ],
     *            "canEdit": false,
     *            "numberOfTasks": 3
     *         ],
     *         "archived":
     *          [
     *            "id": 54,
     *            "title": "Test 77",
     *            "description": "just test 2",
     *            "createdAt":1507968483,
     *            "updatedAt":1507968483,
     *            "is_active": false,
     *            "userHasProjects":
     *            [
     *              {
     *                "id": 358,
     *                "user":
     *                {
     *                   "id": 734,
     *                   "username": "admin",
     *                   "email": "admin@admin.sk"
     *                },
     *                "acl":
     *                [
     *                   "edit_project",
     *                   "create_task",
     *                   "resolve_task",
     *                   "delete_task",
     *                   "view_internal_note",
     *                   "view_all_tasks",
     *                   "view_own_tasks",
     *                   "view_tasks_from_users_company"
     *                 ]
     *              },
     *              {
     *                "id": 359,
     *                "user":
     *                {
     *                   "id": 743,
     *                   "username": "customer5",
     *                   "email": "customer@customer5.sk"
     *                },
     *                "acl":
     *                [
     *                   "edit_project",
     *                   "create_task",
     *                   "resolve_task",
     *                   "delete_task",
     *                   "view_internal_note",
     *                   "view_all_tasks",
     *                   "view_own_tasks",
     *                   "view_tasks_from_users_company"
     *                 ]
     *              }
     *            ],
     *            "canEdit": false,
     *            "numberOfTasks": 3
     *         ],
     *         "tags":
     *         [
     *           {
     *              "id": 20,
     *              "title": "Another Admin Public Tag",
     *              "color": "DFD115",
     *              "public": false,
     *              "createdBy":
     *              {
     *                "id": 734,
     *                "username": "admin",
     *                "email": "admin@admin.sk"
     *              }
     *           },
     *           {
     *              "id": 18,
     *              "title": "Work",
     *              "color": "4871BF",
     *              "public": true,
     *              "createdBy":
     *              {
     *                 "id": 734,
     *                 "username": "admin",
     *                 "email": "admin@admin.sk"
     *              }
     *            }
     *         ],
     *         "reports":
     *         [
     *            {
     *               {
     *                  "id": 39,
     *                  "title": "Test",
     *                  "public": true,
     *                  "filter":
     *                  {
     *                     "status": "1,2",
     *                     "assigned": "not,current-user"
     *                  },
     *                  "report": true,
     *                  "is_active": true,
     *                  "default": false,
     *                  "icon_class": "ggg",
     *                  "order": 4,
     *                  "createdBy":
     *                  {
     *                    "id": 734,
     *                    "username": "admin",
     *                    "email": "admin@admin.sk"
     *                  },
     *                  "project": null,
     *                  "columns": null
     *                },
     *            }
     *         ],
     *      }
     *
     * @ApiDoc(
     *  description="Returns a list of logged User's FILTERS, TAGS, PROJECTS, ARCHIVED and REPORTS for ADMIN",
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request"
     *  }
     * )
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function getLeftNavigationParamsAction(): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('left_menu_param_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $doctrine = $this->getDoctrine();
        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $isAdmin = $this->get('project_voter')->isAdmin();

        // Returns a list of Logged user's active Projects
        $loggedUsersAvailableProjects = $this->getDoctrine()->getRepository('APITaskBundle:Project')->getAllUsersAvailableProjects($isAdmin, $loggedUser->getId(), true);

        // Add to every project canEdit value based on logged user's project ACL. ADMIN can edit every project
        // Add to every project the number of Tasks
        $modifiedLoggedUserProjects = [];
        foreach ($loggedUsersAvailableProjects as $project) {
            $projectEntityFromDb = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($project['id']);
            if ($isAdmin) {
                $canEditProject = true;
            } else {
                $canEditProject = $this->canEditProject($projectEntityFromDb);
            }
            $project['canEdit'] = $canEditProject;
            $project['numberOfTasks'] = $this->getDoctrine()->getRepository('APITaskBundle:Task')->getNumberOfTasksFromProject($projectEntityFromDb);
            $modifiedLoggedUserProjects[] = $project;
        }

        // Returns a list of Logged user's not-active Projects
        $loggedUsersAvailableArchivedProjects = $this->getDoctrine()->getRepository('APITaskBundle:Project')->getAllUsersAvailableProjects($isAdmin, $loggedUser->getId(), false);

        // Add to every project canEdit value based on logged user's project ACL. ADMIN can edit every project
        $modifiedLoggedUserNotActiveProjects = [];
        foreach ($loggedUsersAvailableArchivedProjects as $project) {
            $projectEntityFromDb = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($project['id']);
            if ($isAdmin) {
                $canEditProject = true;
            } else {
                $canEditProject = $this->canEditProject($projectEntityFromDb);
            }
            $project['canEdit'] = $canEditProject;
            $project['numberOfTasks'] = $this->getDoctrine()->getRepository('APITaskBundle:Task')->getNumberOfTasksFromProject($projectEntityFromDb);
            $modifiedLoggedUserNotActiveProjects[] = $project;
        }

        // Returns a list of Logged User's Tags + public tags
        $tagOptions = [
            'loggedUserId' => $loggedUser->getId(),
            'limit' => 999,
            'order' => 'ASC'
        ];
        $loggedUserTagsArray = $doctrine->getRepository('APITaskBundle:Tag')->getAllEntities(1, $tagOptions);
        $loggedUserTags = $loggedUserTagsArray['array'];

        // Returns a list of Logged user's Filters
        $filterOptions = [
            'loggedUserId' => $loggedUser->getId(),
            'isActive' => true,
            'report' => false,
            'public' => null,
        ];
        $loggedUserFilters = $doctrine->getRepository('APITaskBundle:Filter')->getAllUsersFiltersWithoutPagination($filterOptions);

        $responseArray = [
            'filters' => $loggedUserFilters,
            'projects' => $modifiedLoggedUserProjects,
            'archived' => $modifiedLoggedUserNotActiveProjects,
            'tags' => $loggedUserTags,
        ];

        // Returns a list of admin's Reports
        if ($isAdmin) {
            $reportOptions = [
                'loggedUserId' => $loggedUser->getId(),
                'isActive' => true,
                'report' => true,
                'public' => null,
            ];
            $loggedUserReports = $doctrine->getRepository('APITaskBundle:Filter')->getAllUsersFiltersWithoutPagination($reportOptions);
            $responseArray['reports'] = $loggedUserReports;
        }

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($responseArray));
        return $response;
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
