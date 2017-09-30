<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     *                  "filter": "{\"status\":\"new\"}",
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
     *                  "columns": null
     *                },
     *            }
     *         ],
     *         "projects":
     *         [
     *            "id": 54,
     *            "title": "Test 77",
     *            "description": "just test 2",
     *            "createdAt":
     *            {
     *               "date": "2017-07-14 03:05:28.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "updatedAt":
     *            {
     *               "date": "2017-07-14 03:05:28.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
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
     *                "acl": "[\"view_own_tasks\",\"view_tasks_from_users_company\",\"view_all_tasks\",\"create_task\",\"resolve_task\",\"delete_task\",\"view_internal_note\",\"edit_internal_note\",\"edit_project\"]"
     *              },
     *              {
     *                "id": 359,
     *                "user":
     *                {
     *                   "id": 743,
     *                   "username": "customer5",
     *                   "email": "customer@customer5.sk"
     *                },
     *                "acl": "[\"view_own_tasks\",\"view_tasks_from_users_company\",\"view_all_tasks\",\"create_task\",\"resolve_task\",\"delete_task\",\"view_internal_note\",\"edit_internal_note\",\"edit_project\"]"
     *              }
     *            ]
     *         ],
     *         "archived":
     *          [
     *            "id": 54,
     *            "title": "Test 77",
     *            "description": "just test 2",
     *            "createdAt":
     *            {
     *               "date": "2017-07-14 03:05:28.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *            "updatedAt":
     *            {
     *               "date": "2017-07-14 03:05:28.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
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
     *                "acl": "[\"view_own_tasks\",\"view_tasks_from_users_company\",\"view_all_tasks\",\"create_task\",\"resolve_task\",\"delete_task\",\"view_internal_note\",\"edit_internal_note\",\"edit_project\"]"
     *              },
     *              {
     *                "id": 359,
     *                "user":
     *                {
     *                   "id": 743,
     *                   "username": "customer5",
     *                   "email": "customer@customer5.sk"
     *                },
     *                "acl": "[\"view_own_tasks\",\"view_tasks_from_users_company\",\"view_all_tasks\",\"create_task\",\"resolve_task\",\"delete_task\",\"view_internal_note\",\"edit_internal_note\",\"edit_project\"]"
     *              }
     *            ]
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
     *                  "filter": "{\"status\":\"new\"}",
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
     * @return JsonResponse
     * @throws \LogicException
     */
    public function getLeftNavigationParamsAction()
    {
        $doctrine = $this->getDoctrine();
        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $isAdmin = $this->get('project_voter')->isAdmin();

        // Returns a list of Logged user's active Projects
        $options = [
            'isAdmin' => $this->get('project_voter')->isAdmin(),
            'loggedUser' => $loggedUser,
            'isActive' => true,
        ];
        $loggedUserProjects = $doctrine->getRepository('APITaskBundle:Project')->getAllUsersProjectsWithoutPagination($options);
        // Add to every project canEdit value based on logged user's project ACL. ADMIN can edit every project
        $modifiedLoggedUserProjects = [];
        foreach ($loggedUserProjects as $project) {
            if ($isAdmin) {
                $canEditProject = true;
            } else {
                $projectEntityFromDb = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($project['id']);
                $canEditProject = $this->canEditProject($projectEntityFromDb);
            }
            $project['canEdit'] = $canEditProject;
            $modifiedLoggedUserProjects[] = $project;
        }

        // Returns a list of Logged user's not-active Projects
        $optionsArchived = [
            'isAdmin' => $isAdmin,
            'loggedUser' => $loggedUser,
            'isActive' => false,
        ];
        $loggedUserArchivedProjects = $doctrine->getRepository('APITaskBundle:Project')->getAllUsersProjectsWithoutPagination($optionsArchived);
        // Add to every project canEdit value based on logged user's project ACL. ADMIN can edit every project
        $modifiedLoggedUserNotActiveProjects = [];
        foreach ($loggedUserArchivedProjects as $project) {
            if ($isAdmin) {
                $canEditProject = true;
            } else {
                $projectEntityFromDb = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($project['id']);
                $canEditProject = $this->canEditProject($projectEntityFromDb);
            }
            $project['canEdit'] = $canEditProject;
            $modifiedLoggedUserNotActiveProjects[] = $project;
        }

        // Returns a list of Logged User's Tags + public tags
        $loggedUserTags = $doctrine->getRepository('APITaskBundle:Tag')->getAllUsersTagsWithoutPagination($loggedUser->getId());


        // Returns a list of Logged user's Filters
        $filterOptions = [
            'loggedUserId' => $loggedUser->getId(),
            'isActive' => true,
            'report' => false,
            'public' => null,
        ];
        $loggedUserFilters = $doctrine->getRepository('APITaskBundle:Filter')->getAllUsersFiltersWithoutPagination($filterOptions);

        $response = [
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
            $response['reports'] = $loggedUserReports;
        }

        return $this->json($response, StatusCodesHelper::SUCCESSFUL_CODE);
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
