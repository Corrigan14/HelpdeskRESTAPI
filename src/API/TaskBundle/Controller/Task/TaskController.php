<?php

namespace API\TaskBundle\Controller\Task;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Status;
use API\TaskBundle\Entity\Tag;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Entity\TaskData;
use API\TaskBundle\Entity\TaskHasAssignedUser;
use API\TaskBundle\Entity\UserHasProject;
use API\TaskBundle\Security\ProjectAclOptions;
use API\TaskBundle\Security\StatusOptions;
use API\TaskBundle\Security\UserRoleAclOptions;
use API\TaskBundle\Security\VoteOptions;
use API\TaskBundle\Services\FilterAttributeOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskController
 *
 * @package API\TaskBundle\Controller\Task
 */
class TaskController extends ApiBaseController
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 59,
     *             "title": "Task 1 - user is creator, user is requested",
     *             "description": "Description of Task 1",
     *             "deadline": null,
     *             "startedAt": null,
     *             "closedAt": null,
     *             "important": false,
     *             "createdAt":
     *             {
     *               "date": "2017-01-03 22:16:51.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *             },
     *             "updatedAt":
     *             {
     *               "date": "2017-01-03 14:16:51.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *             },
     *             "taskData":
     *             [
     *               {
     *                  "id": 39,
     *                  "value": "some input",
     *                  "taskAttribute":
     *                  {
     *                     "id": 52,
     *                     "title": "input task additional attribute",
     *                     "type": "input",
     *                     "options": null,
     *                     "is_active": true
     *                  }
     *               },
     *               {
     *                 "id": 40,
     *                 "value": "select1",
     *                 "taskAttribute":
     *                 {
     *                    "id": 53,
     *                    "title": "select task additional attribute",
     *                    "type": "simple_select",
     *                    "options":
     *                    "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                    "is_active": true
     *                 }
     *               }
     *             ],
     *             "project":
     *             {
     *                "id": 86,
     *                "title": "Project of user 1",
     *                "description": "Description of project 1.",
     *                "is_active": false,
     *                "createdAt":
     *                {
     *                   "date": "2017-01-03 14:16:51.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                },
     *                "updatedAt":
     *                {
     *                   "date": "2017-01-03 14:16:51.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                }
     *             },
     *             "createdBy":
     *             {
     *                "id": 65,
     *                "username": "user",
     *                "password": "$2y$13$upBgDlLWe7MhoAzma.kvoufabj4cNAZQ9BgG412hU7KohSLxxZ4NW",
     *                "email": "user@user.sk",
     *                "roles": "[\"ROLE_USER\"]",
     *                "is_active": true,
     *                "image": null,
     *                "company":
     *                {
     *                   "id": 65,
     *                   "title": "LanSystems",
     *                   "ico": "110258782",
     *                   "dic": "12587458996244",
     *                   "ic_dph": null,
     *                   "street": "Ina cesta 125",
     *                   "city": "Bratislava",
     *                   "zip": "021478",
     *                   "country": "Slovenska Republika",
     *                   "is_active": true
     *                 },
     *                 "user_role":
     *                 {
     *                   "id": 2,
     *                   "title": "MANAGER",
     *                   "description": null,
     *                   "homepage": "/",
     *                   "acl":
     *                   "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                   "is_active": true
     *                   "order": 2
     *                 }
     *             },
     *             "requestedBy":
     *             {
     *                "id": 65,
     *                "username": "user",
     *                "password": "$2y$13$upBgDlLWe7MhoAzma.kvoufabj4cNAZQ9BgG412hU7KohSLxxZ4NW",
     *                "email": "user@user.sk",
     *                "roles": "[\"ROLE_USER\"]",
     *                "is_active": true,
     *                "image": null,
     *                "user_role":
     *                 {
     *                   "id": 2,
     *                   "title": "MANAGER",
     *                   "description": null,
     *                   "homepage": "/",
     *                   "acl":
     *                   "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                   "is_active": true
     *                   "order": 2
     *                 }
     *             },
     *             "taskHasAssignedUsers":
     *             [
     *                {
     *                   "id": 28,
     *                   "status_date": null,
     *                   "time_spent": null,
     *                   "createdAt":
     *                   {
     *                      "date": "2017-01-03 14:16:51.000000",
     *                      "timezone_type": 3,
     *                      "timezone": "Europe/Berlin"
     *                   },
     *                   "updatedAt":
     *                   {
     *                      "date": "2017-01-03 14:16:51.000000",
     *                      "timezone_type": 3,
     *                      "timezone": "Europe/Berlin"
     *                   },
     *                  "status":
     *                  {
     *                     "id": 84,
     *                     "title": "Completed",
     *                     "is_active": true
     *                  },
     *                 "user":
     *                 {
     *                     "id": 65,
     *                     "username": "user",
     *                     "password": "$2y$13$upBgDlLWe7MhoAzma.kvoufabj4cNAZQ9BgG412hU7KohSLxxZ4NW",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "image": null,
     *                     "user_role":
     *                      {
     *                          "id": 2,
     *                          "title": "MANAGER",
     *                          "description": null,
     *                          "homepage": "/",
     *                          "acl":
     *                          "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                          "is_active": true
     *                          "order": 2
     *                      }
     *                  }
     *                }
     *             ],
     *             "company":
     *             {
     *                "id": 317,
     *                "title": "Web-Solutions",
     *                "ico": "1102587",
     *                "dic": "12587459644",
     *                "ic_dph": null,
     *                "street": "Cesta 125",
     *                "city": "Bratislava",
     *                "zip": "021478",
     *                "country": "Slovenska Republika",
     *                "is_active": true
     *             }
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/task?page=1&project=145&creator=21",
     *           "first": "/api/v1/task-bundle/task?page=1&project=145&creator=21",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/task?page=2&project=145&creator=21",
     *           "last": "/api/v1/task-bundle/task?page=3&project=145&creator=21"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of full Task Entities which includes extended Task Data",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="search",
     *       "description"="Search string - system is searching in ID and TITLE"
     *     },
     *     {
     *       "name"="status",
     *       "description"="A list of coma separated ID's of statuses f.i. 1,2,3,4"
     *     },
     *     {
     *       "name"="project",
     *       "description"="A list of coma separated ID's of Project f.i. 1,2,3.
     *        Another options:
     *          NOT - just tasks without projects are returned,
     *          CURRENT-USER - just tasks from actually logged user's projects are returned."
     *     },
     *     {
     *       "name"="creator",
     *       "description"="A list of coma separated ID's of Creator f.i. 1,2,3
     *        Another option:
     *          CURRENT-USER - just tasks created by actually logged user are returned."
     *     },
     *     {
     *       "name"="requester",
     *       "description"="A list of coma separated ID's of Requesters f.i. 1,2,3
     *        Another option:
     *          CURRENT-USER - just tasks requested by actually logged user are returned."
     *     },
     *     {
     *       "name"="company",
     *       "description"="A list of coma separated ID's of Companies f.i. 1,2,3
     *        Another options:
     *          CURRENT-USER - just tasks created by users with the same company like logged user are returned."
     *     },
     *     {
     *       "name"="assigned",
     *       "description"="A list of coma separated ID's of Users f.i. 1,2,3
     *        Another option:
     *          NOT - just tasks which aren't assigned to nobody are returned,
     *          CURRENT-USER - just tasks assigned to actually logged user are returned."
     *     },
     *     {
     *       "name"="tag",
     *       "description"="A list of coma separated ID's of Tags f.i. 1,2,3"
     *     },
     *     {
     *       "name"="follower",
     *       "description"="A list of coma separated ID's of Task Followers f.i. 1,2,3
     *        Another option:
     *          CURRENT-USER - just tasks followed by actually logged user are returned."
     *     },
     *     {
     *       "name"="createdTime",
     *       "description"="A coma separated dates in format
     *       FROM=2015-02-04T05:10:58+05:30,TO=2015-02-04T05:10:58+05:30
     *        Another option:
     *          TO=NOW - just tasks created to NOW datetime are returned."
     *     },
     *     {
     *       "name"="startedTime",
     *       "description"="A coma separated dates in format
     *       FROM=2015-02-04T05:10:58+05:30,TO=2015-02-04T05:10:58+05:30
     *        Another option:
     *          TO=NOW - just tasks started to NOW datetime are returned."
     *     },
     *     {
     *       "name"="deadlineTime",
     *       "description"="A coma separated dates in format
     *       FROM=2015-02-04T05:10:58+05:30,TO=2015-02-04T05:10:58+05:30
     *       Another option:
     *          TO=NOW - just tasks with deadline to NOW datetime are returned."
     *     },
     *     {
     *       "name"="closedTime",
     *       "description"="A coma separated dates in format
     *       FROM=2015-02-04T05:10:58+05:30,TO=2015-02-04T05:10:58+05:30
     *       Another option:
     *          TO=NOW - just tasks closed to NOW datetime are returned."
     *     },
     *     {
     *       "name"="archived",
     *       "description"="If TRUE, just tasks from archived projects are returned"
     *     },
     *     {
     *       "name"="important",
     *       "description"="If TRUE, just IMPORTANT tasks are returned"
     *     },
     *     {
     *       "name"="addedParameters",
     *       "description"="& separated data in form: taskAttributeId=value1,value2&taskAttributeId=value"
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
     *      401 ="Unauthorized request"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request)
    {
        $page = $request->get('page') ?: 1;
        $filterData = $this->getFilterData($request);

        $options = [
            'loggedUser' => $this->getUser(),
            'isAdmin' => $this->get('task_voter')->isAdmin(),
            'inFilter' => $filterData['inFilter'],
            'equalFilter' => $filterData['equalFilter'],
            'isNullFilter' => $filterData['isNullFilter'],
            'dateFilter' => $filterData['dateFilter'],
            'searchFilter' => $filterData['searchFilter'],
            'notAndCurrentFilter' => $filterData['notAndCurrentFilter'],
            'inFilterAddedParams' => $filterData['inFilterAddedParams'],
            'equalFilterAddedParams' => $filterData['equalFilterAddedParams'],
            'dateFilterAddedParams' => $filterData['dateFilterAddedParams'],
            'filtersForUrl' => $filterData['filterForUrl'],
        ];

        $tasksArray = $this->get('task_service')->getTasksResponse($page, $options);

        return $this->json($tasksArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 59,
     *             "title": "Task 1 - user is creator, user is requested",
     *             "description": "Description of Task 1",
     *             "deadline": null,
     *             "startedAt": null,
     *             "closedAt": null,
     *             "important": false,
     *             "createdAt":
     *             {
     *               "date": "2017-01-03 22:16:51.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *             },
     *             "updatedAt":
     *             {
     *               "date": "2017-01-03 14:16:51.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *             },
     *             "taskData":
     *             [
     *               {
     *                  "id": 39,
     *                  "value": "some input",
     *                  "taskAttribute":
     *                  {
     *                     "id": 52,
     *                     "title": "input task additional attribute",
     *                     "type": "input",
     *                     "options": null,
     *                     "is_active": true
     *                  }
     *               },
     *               {
     *                 "id": 40,
     *                 "value": "select1",
     *                 "taskAttribute":
     *                 {
     *                    "id": 53,
     *                    "title": "select task additional attribute",
     *                    "type": "simple_select",
     *                    "options":
     *                    "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                    "is_active": true
     *                 }
     *               }
     *             ],
     *             "project":
     *             {
     *                "id": 86,
     *                "title": "Project of user 1",
     *                "description": "Description of project 1.",
     *                "is_active": false,
     *                "createdAt":
     *                {
     *                   "date": "2017-01-03 14:16:51.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                },
     *                "updatedAt":
     *                {
     *                   "date": "2017-01-03 14:16:51.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                }
     *             },
     *             "createdBy":
     *             {
     *                "id": 65,
     *                "username": "user",
     *                "password": "$2y$13$upBgDlLWe7MhoAzma.kvoufabj4cNAZQ9BgG412hU7KohSLxxZ4NW",
     *                "email": "user@user.sk",
     *                "roles": "[\"ROLE_USER\"]",
     *                "is_active": true,
     *                "image": null,
     *                "detailData":
     *                {
     *                  "name": "Martina",
     *                  "surname": "Kollar",
     *                  "title_before": null,
     *                  "title_after": null,
     *                  "function": "developer",
     *                  "mobile": "00421 0987 544",
     *                  "tel": null,
     *                  "fax": null,
     *                  "signature": "Martina Kollar, Web-Solutions",
     *                  "street": "Nova 487",
     *                  "city": "Bratislava",
     *                  "zip": "025874",
     *                  "country": "SR"
     *                },
     *                "company":
     *                {
     *                   "id": 65,
     *                   "title": "LanSystems",
     *                   "ico": "110258782",
     *                   "dic": "12587458996244",
     *                   "ic_dph": null,
     *                   "street": "Ina cesta 125",
     *                   "city": "Bratislava",
     *                   "zip": "021478",
     *                   "country": "Slovenska Republika",
     *                   "is_active": true
     *                 },
     *                 "user_role":
     *                 {
     *                   "id": 2,
     *                   "title": "MANAGER",
     *                   "description": null,
     *                   "homepage": "/",
     *                   "acl":
     *                   "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                   "is_active": true
     *                   "order": 2
     *                 }
     *             },
     *             "requestedBy":
     *             {
     *                "id": 65,
     *                "username": "user",
     *                "password": "$2y$13$upBgDlLWe7MhoAzma.kvoufabj4cNAZQ9BgG412hU7KohSLxxZ4NW",
     *                "email": "user@user.sk",
     *                "roles": "[\"ROLE_USER\"]",
     *                "is_active": true,
     *                "image": null,
     *                "user_role":
     *                 {
     *                   "id": 2,
     *                   "title": "MANAGER",
     *                   "description": null,
     *                   "homepage": "/",
     *                   "acl":
     *                   "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                   "is_active": true
     *                   "order": 2
     *                 }
     *             },
     *             "taskHasAssignedUsers":
     *             [
     *                {
     *                   "id": 28,
     *                   "status_date": null,
     *                   "time_spent": null,
     *                   "createdAt":
     *                   {
     *                      "date": "2017-01-03 14:16:51.000000",
     *                      "timezone_type": 3,
     *                      "timezone": "Europe/Berlin"
     *                   },
     *                   "updatedAt":
     *                   {
     *                      "date": "2017-01-03 14:16:51.000000",
     *                      "timezone_type": 3,
     *                      "timezone": "Europe/Berlin"
     *                   },
     *                  "status":
     *                  {
     *                     "id": 84,
     *                     "title": "Completed",
     *                     "is_active": true
     *                  },
     *                 "user":
     *                 {
     *                     "id": 65,
     *                     "username": "user",
     *                     "password": "$2y$13$upBgDlLWe7MhoAzma.kvoufabj4cNAZQ9BgG412hU7KohSLxxZ4NW",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "image": null,
     *                     "user_role":
     *                     {
     *                        "id": 2,
     *                        "title": "MANAGER",
     *                        "description": null,
     *                        "homepage": "/",
     *                        "acl":
     *                        "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
     *                        "is_active": true
     *                        "order": 2
     *                     }
     *                  }
     *                }
     *             ]
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/task?page=1&filterId=145",
     *           "first": "/api/v1/task-bundle/task?page=1&filterId=145",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/task?page=2&filterId=145",
     *           "last": "/api/v1/task-bundle/task?page=3&filterId=145"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of full Task Entities selected by rules of requested Filter",
     *  requirements={
     *     {
     *       "name"="filterId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of filter"
     *     }
     *  },
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
     *      403 ="Access denied",
     *      404 ="Not found entity"
     *  }
     * )
     *
     * @param Request $request
     * @param int $filterId
     *
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listSavedFilterAction(Request $request, int $filterId)
    {
        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($filterId);

        if (!$filter instanceof Filter) {
            return $this->createApiResponse([
                'message' => 'Filter with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if logged user has permission to see requested filter
        if (!$this->get('filter_voter')->isGranted(VoteOptions::SHOW_FILTER, $filter)) {
            return $this->accessDeniedResponse();
        }

        $page = $request->get('page') ?: 1;
        $filterDataArray = $filter->getFilter();
        $filterData = $this->getFilterDataFromSavedFilterArray($filterDataArray);
        $options = [
            'loggedUser' => $this->getUser(),
            'isAdmin' => $this->get('task_voter')->isAdmin(),
            'inFilter' => $filterData['inFilter'],
            'equalFilter' => $filterData['equalFilter'],
            'isNullFilter' => $filterData['isNullFilter'],
            'dateFilter' => $filterData['dateFilter'],
            'searchFilter' => $filterData['searchFilter'],
            'notAndCurrentFilter' => $filterData['notAndCurrentFilter'],
            'inFilterAddedParams' => $filterData['inFilterAddedParams'],
            'equalFilterAddedParams' => $filterData['equalFilterAddedParams'],
            'dateFilterAddedParams' => $filterData['dateFilterAddedParams'],
            'filtersForUrl' => $filterData['filterForUrl'],
        ];

        $tasksArray = $this->get('task_service')->getTasksResponse($page, $options);

        return $this->json($tasksArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ]
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  description="Returns full Task Entity including extended about Task Data",
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
     *  output="API\TaskBundle\Entity\Task",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        // Check if user can update selected task
        if ($this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            $canEdit = true;
        } else {
            $canEdit = false;
        }

        if ($task->getProject() instanceof Project) {
            $projectId = $task->getProject()->getId();
        } else {
            $projectId = false;
        }

        $ids = [
            'id' => $task->getId(),
            'projectId' => $projectId,
            'requesterId' => $task->getRequestedBy()->getId(),
        ];

        $response = $this->get('task_service')->getTaskResponse($ids);
        $responseData['data'] = $response['data'][0];
        $responseData['data']['canEdit'] = $canEdit;
        $responseLinks['_links'] = $response['_links'];

        return $this->json(array_merge($responseData, $responseLinks), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ]
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Task Entity with extra Task Data.
     *  This can be added by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     },
     *     {
     *       "name"="requestedUserId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user requested task"
     *     },
     *     {
     *       "name"="companyId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of company"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Task"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param bool|int $projectId
     * @param bool|int $requestedUserId
     * @param bool|int $companyId
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAction(Request $request, $projectId = false, $requestedUserId = false, $companyId = false)
    {
        // Check if logged user has ACL to create task
        $aclOptions = [
            'acl' => UserRoleAclOptions::CREATE_TASKS,
            'user' => $this->getUser(),
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $task = new Task();
        $requestData = $request->request->all();

        if ($projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            // Check if user can create task in selected project
            if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK_IN_PROJECT, $project)) {
                return $this->createApiResponse([
                    'message' => 'Permission denied! Can not create task in selected project!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            }
            $task->setProject($project);
            unset($requestData['projectId']);
        }

        if ($requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $task->setRequestedBy($requestedUser);
            $requesterTaskId = $requestedUserId;
            unset($requestData['requestedUserId']);
        } else {
            $task->setRequestedBy($this->getUser());
            $requesterTaskId = $this->getUser()->getId();
        }

        if ($companyId) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($companyId);

            if (!$company instanceof Company) {
                return $this->createApiResponse([
                    'message' => 'Company with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $task->setCompany($company);
            $companyTaskId = $companyId;
            unset($requestData['companyId']);
        } else {
            $loggedUserCompany = $this->getUser()->getCompany();
            if ($loggedUserCompany instanceof Company) {
                $task->setCompany($loggedUserCompany);
            }
            $companyTaskId = $loggedUserCompany;
        }

        $task->setCreatedBy($this->getUser());
        $task->setImportant(false);

        $ids = [
            'id' => false,
            'projectId' => $projectId,
            'requesterId' => $requesterTaskId,
            'companyId' => $companyTaskId
        ];

        return $this->updateTaskEntity($task, $requestData, true, $ids);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ]
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  description="Update the Task Entity with extra Task Data.
     *  These could be updated by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.
     *  Project and Requested User could be updated by Id-s in URL.",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     },
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     },
     *     {
     *       "name"="requestedUserId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user requested task"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Task"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
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
     * @param bool|int $projectId
     * @param bool|int $requestedUserId
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request, $projectId = false, $requestedUserId = false)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        // Check if project and requested user exists
        if ($projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            // Check if user can create task in selected project
            if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK_IN_PROJECT, $project)) {
                return $this->createApiResponse([
                    'message' => 'Permission denied! Can not create task in selected project!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            }
            $task->setProject($project);
            $taskProjectId = $projectId;
            unset($requestData['projectId']);
        } elseif ($task->getProject()) {
            $taskProjectId = $task->getProject()->getId();
        } else {
            $taskProjectId = false;
        }

        if ($requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);
            $requesterTaskId = $requestedUserId;
            unset($requestData['requestedUserId']);
        } else {
            $requesterTaskId = $task->getRequestedBy()->getId();
        }

        $ids = [
            'id' => $id,
            'projectId' => $taskProjectId,
            'requesterId' => $requesterTaskId,
        ];

        return $this->updateTaskEntity($task, $requestData, false, $ids);
    }

    /**
     * ### Response ###
     *       *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ]
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  description="Partially update the Task Entity with extra Task Data.
     *  These could be updated by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.
     *  Project and Requested User could be updated by Id-s in URL.",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     },
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     },
     *     {
     *       "name"="requestedUserId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of user requested task"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Task"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
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
     * @param bool|int $projectId
     * @param bool|int $requestedUserId
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updatePartialAction(int $id, Request $request, $projectId = false, $requestedUserId = false)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        // Check if project and requested user exists
        if ($projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            // Check if user can create task in selected project
            if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK_IN_PROJECT, $project)) {
                return $this->createApiResponse([
                    'message' => 'Permission denied! Can not create task in selected project!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            }
            $task->setProject($project);
            $taskProjectId = $projectId;
            unset($requestData['projectId']);
        } elseif ($task->getProject()) {
            $taskProjectId = $task->getProject()->getId();
        } else {
            $taskProjectId = false;
        }

        if ($requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);
            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $task->setRequestedBy($requestedUser);
            $requesterTaskId = $requestedUserId;
            unset($requestData['requestedUserId']);
        } else {
            $requesterTaskId = $task->getRequestedBy()->getId();
        }

        $ids = [
            'id' => $id,
            'projectId' => $taskProjectId,
            'requesterId' => $requesterTaskId,
        ];

        return $this->updateTaskEntity($task, $requestData, false, $ids);
    }


    /**
     * @ApiDoc(
     *  description="Delete the Task entity",
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
     *      204 ="The Entity was successfully deleted",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function deleteAction(int $id)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        if (!$this->get('task_voter')->isGranted(VoteOptions::DELETE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $this->getDoctrine()->getManager()->remove($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2991,
     *           "title": "test 258",
     *           "description": "Description of Task 1",
     *           "deadline": null,
     *           "startedAt": null,
     *           "closedAt": null,
     *           "important": false,
     *           "createdAt":
     *           {
     *               "date": "2017-01-26 12:21:59.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *           },
     *           "updatedAt":
     *           {
     *               "date": "2017-01-26 14:34:48.000000",
     *               "timezone_type": 3,
     *               "timezone": "Europe/Berlin"
     *            },
     *           "taskData": [],
     *           "project":
     *           {
     *              "id": 6,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-01-26 12:21:59.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                  "date": "2017-01-26 12:21:59.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "Europe/Berlin"
     *               }
     *           },
     *           "createdBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null,
     *              "company":
     *              {
     *                 "id": 4,
     *                 "title": "LanSystems",
     *                 "ico": "110258782",
     *                 "dic": "12587458996244",
     *                 "ic_dph": null,
     *                 "street": "Ina cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true
     *              }
     *           },
     *           "requestedBy":
     *           {
     *              "id": 109,
     *              "username": "user",
     *              "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *              "email": "user@user.sk",
     *              "roles": "[\"ROLE_USER\"]",
     *              "is_active": true,
     *              "language": "AJ",
     *              "image": null,
     *              "detailData": null
     *           },
     *           "taskHasAssignedUsers":
     *           [
     *              {
     *                  "id": 2,
     *                  "status_date": null,
     *                  "time_spent": null,
     *                  "createdAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "updatedAt":
     *                  {
     *                     "date": "2017-01-26 12:22:00.000000",
     *                     "timezone_type": 3,
     *                     "timezone": "Europe/Berlin"
     *                  },
     *                  "status":
     *                  {
     *                     "id": 7,
     *                     "title": "Completed",
     *                     "description": "Completed task",
     *                     "color": "#FF4500",
     *                     "is_active": true
     *                  },
     *                  "user":
     *                  {
     *                     "id": 109,
     *                     "username": "user",
     *                     "password": "$2y$13$sSNk/RwtxwjKtesqSZ6Bx.mm5pGbmGxm3DsJTdIK7iZHkXALYihvq",
     *                     "email": "user@user.sk",
     *                     "roles": "[\"ROLE_USER\"]",
     *                     "is_active": true,
     *                     "language": "AJ",
     *                     "image": null
     *                  }
     *              }
     *           ],
     *           "tags":
     *           [
     *             {
     *                "id": 5,
     *                "title": "tag1",
     *                "color": "FFFF66",
     *                "public": false
     *             },
     *             {
     *               "id": 6,
     *               "title": "tag2",
     *               "color": "FFFF66",
     *               "public": false
     *             }
     *           ],
     *           "company":
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions",
     *              "ico": "1102587",
     *              "dic": "12587459644",
     *              "ic_dph": null,
     *              "street": "Cesta 125",
     *              "city": "Bratislava",
     *              "zip": "021478",
     *              "country": "Slovenska Republika",
     *              "is_active": true
     *           },
     *           "taskHasAttachments":
     *           [
     *             {
     *                "id": 1,
     *                "slug": "zsskcd-jpg-2016-12-17-15-36"
     *             }
     *           ]
     *        },
     *       "_links":
     *       {
     *         "put: task": "/api/v1/task-bundle/tasks/11970",
     *         "patch: task": "/api/v1/task-bundle/tasks/11970",
     *         "delete": "/api/v1/task-bundle/tasks/11970",
     *         "put: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "patch: tasks requester": "/api/v1/task-bundle/tasks/11970/requester/313",
     *         "put: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "patch: tasks project": "/api/v1/task-bundle/tasks/11970/project/18",
     *         "put: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313",
     *         "patch: tasks project and requester": "/api/v1/task-bundle/tasks/11970/project/18/requester/313"
     *       }
     *    }
     *
     * @ApiDoc(
     *  description="Partially update the Task Entity with extra Task Data.
     *  These could be updated by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.
     *  Project and Requested User could be updated by Id-s in URL.",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  parameters={
     *      {"name"="project", "dataType"="int", "required"=false, "description"="id of Project"},
     *      {"name"="requester", "dataType"="int", "required"=false,  "description"="id of User"},
     *      {"name"="company", "dataType"="int", "required"=false,  "description"="id of Company"},
     *      {"name"="assigned", "dataType"="array", "required"=false,  "description"="array of Users assigned to task: [userId => 12, statusId => 5]"},
     *      {"name"="startedAt", "dataType"="datetime", "required"=false,  "description"="the date of planned start"},
     *      {"name"="deadline", "dataType"="datetime", "required"=false,  "description"="the date of deadline"},
     *      {"name"="closedAt", "dataType"="datetime", "required"=false,  "description"="the date of closure"},
     *      {"name"="tag", "dataType"="array", "required"=false,  "description"="array of Tag titles: [tag1, tag2]"}
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Task"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param int $taskId
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function quickUpdateTaskAction(int $taskId, Request $request)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $requestData = json_decode($request->getContent(), true);

        if (isset($requestData['project'])) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($requestData['project']);
            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            // Check if user can create task in selected project
            if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK_IN_PROJECT, $project)) {
                return $this->createApiResponse([
                    'message' => 'Permission denied! Can not create task in selected project!',
                ], StatusCodesHelper::ACCESS_DENIED_CODE);
            }
            $task->setProject($project);
            $taskProjectId = $requestData['project'];

            // Remove users assigned to task which hasn't RESOLVE_TASK permission in selected project
            $taskHasAssignedUsers = $task->getTaskHasAssignedUsers();
            if (count($taskHasAssignedUsers) > 0) {
                /** @var TaskHasAssignedUser $entity */
                foreach ($taskHasAssignedUsers as $entity) {
                    if (!$this->checkIfUserHasResolveTaskAclPermission($entity, $project)) {
                        $this->getDoctrine()->getManager()->remove($taskHasAssignedUsers);
                        $this->getDoctrine()->getManager()->flush();
                    }
                }
            }
        } elseif ($task->getProject()) {
            $taskProjectId = $task->getProject()->getId();
        } else {
            $taskProjectId = false;
        }

        if (isset($requestData['requester'])) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestData['requester']);
            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $task->setRequestedBy($requestedUser);
            $requesterTaskId = $requestData['requester'];
        } else {
            $requesterTaskId = $task->getRequestedBy()->getId();
        }

        if (isset($requestData['company'])) {
            $company = $this->getDoctrine()->getRepository('APICoreBundle:Company')->find($requestData['company']);
            if (!$company instanceof Company) {
                return $this->createApiResponse([
                    'message' => 'Company with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $task->setCompany($company);
            $companyTaskId = $requestData['company'];
        } elseif ($task->getCompany()) {
            $companyTaskId = $task->getCompany()->getId();
        } else {
            $companyTaskId = false;
        }

        if (isset($requestData['assigned'])) {
            $assignedUsersArray = $requestData['assigned'];
            foreach ($assignedUsersArray as $data) {
                $assignedUserId = $data['userId'];
                $statusId = $data['statusId'];

                $user = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($assignedUserId);

                if (!$user instanceof User) {
                    return $this->createApiResponse([
                        'message' => 'User with requested Id does not exist!',
                    ], StatusCodesHelper::NOT_FOUND_CODE);
                }

                // Check if user is already assigned to task
                $userIsAssignedToTask = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAssignedUser')->findOneBy([
                    'user' => $user,
                    'task' => $task
                ]);

                if (!$userIsAssignedToTask instanceof TaskHasAssignedUser) {
                    // Check if user can be assigned to task
                    $options = [
                        'task' => $task,
                        'user' => $user
                    ];

                    if (!$this->get('task_voter')->isGranted(VoteOptions::ASSIGN_USER_TO_TASK, $options)) {
                        return $this->createApiResponse([
                            'message' => 'User with id: ' . $assignedUserId . 'has not permission to be assigned to requested task!',
                        ], StatusCodesHelper::NOT_FOUND_CODE);
                    }
                    $userIsAssignedToTask = new TaskHasAssignedUser();
                }

                if ($statusId) {
                    $status = $this->getDoctrine()->getRepository('APITaskBundle:Status')->find($statusId);

                    if (!$status instanceof Status) {
                        return $this->createApiResponse([
                            'message' => 'Status with requested Id does not exist!',
                        ], StatusCodesHelper::NOT_FOUND_CODE);
                    }
                } else {
                    $newStatus = $this->getDoctrine()->getRepository('APITaskBundle:Status')->findOneBy([
                        'title' => StatusOptions::NEW,
                    ]);
                    if (!$newStatus instanceof Status) {
                        return $this->createApiResponse([
                            'message' => 'New Status Entity does not exist!',
                        ], StatusCodesHelper::NOT_FOUND_CODE);
                    } else {
                        $status = $newStatus;
                    }
                }

                $userIsAssignedToTask->setTask($task);
                $userIsAssignedToTask->setStatus($status);
                $userIsAssignedToTask->setUser($user);
                $this->getDoctrine()->getManager()->persist($userIsAssignedToTask);
            }
        }

        if (isset($requestData['startedAt'])) {
            try {
                $startedAtDateTimeObject = new \Datetime($requestData['startedAt']);
                $task->setStartedAt($startedAtDateTimeObject);
            } catch (\Exception $e) {
                return $this->createApiResponse([
                    'message' => 'startedAt parameter is not in a valid format! Expected format: Unix',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }

        if (isset($requestData['deadline'])) {
            try {
                $startedAtDateTimeObject = new \Datetime($requestData['deadline']);
                $task->setDeadline($startedAtDateTimeObject);
            } catch (\Exception $e) {
                return $this->createApiResponse([
                    'message' => 'deadline parameter is not in a valid format! Expected format: Unix',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }

        if (isset($requestData['closedAt'])) {
            try {
                $startedAtDateTimeObject = new \Datetime($requestData['closedAt']);
                $task->setClosedAt($startedAtDateTimeObject);
            } catch (\Exception $e) {
                return $this->createApiResponse([
                    'message' => 'closedAt parameter is not in a valid format! Expected format: Unix',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }

        if (isset($requestData['tag'])) {
            $tagsArray = $requestData['tag'];
            foreach ($tagsArray as $data) {
                $tag = $this->getDoctrine()->getRepository('APITaskBundle:Tag')->findOneBy([
                    'title' => $data
                ]);

                if ($tag instanceof Tag) {
                    //Check if user can add tag to requested Task
                    $options = [
                        'task' => $task,
                        'tag' => $tag
                    ];

                    if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_TAG_TO_TASK, $options)) {
                        return $this->createApiResponse([
                            'message' => 'Tag with title: ' . $data . 'can not be added to requested task!',
                        ], StatusCodesHelper::NOT_FOUND_CODE);
                    }

                    //Check if tag is already added to task
                    $taskHasTags = $task->getTags();
                    if (in_array($tag, $taskHasTags->toArray(), true)) {
                        continue;
                    }
                } else {
                    //Create a new tag
                    $tag = new Tag();
                    $tag->setTitle($data);
                    $tag->setPublic(false);
                    $tag->setColor('FFFF66');
                    $tag->setCreatedBy($this->getUser());

                    $this->getDoctrine()->getManager()->persist($tag);
                    $this->getDoctrine()->getManager()->flush();
                }

                //Add tag to task
                $task->addTag($tag);
                $this->getDoctrine()->getManager()->persist($task);
            }
        }

        $this->getDoctrine()->getManager()->persist($task);
        $this->getDoctrine()->getManager()->flush();

        $ids = [
            'id' => $taskId,
            'projectId' => $taskProjectId,
            'requesterId' => $requesterTaskId,
            'companyId' => $companyTaskId
        ];
        $response = $this->get('task_service')->getTaskResponse($ids);
        $responseData['data'] = $response['data'][0];
        $responseLinks['_links'] = $response['_links'];
        return $this->json(array_merge($responseData, $responseLinks), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *         "status":
     *         [
     *            {
     *               "id": 178,
     *               "title": "new"
     *            },
     *            {
     *               "id": 179,
     *               "title": "In Progress"
     *            },
     *         ],
     *         "project":
     *         [
     *            {
     *               "id": 207,
     *               "title": "Project of user 2"
     *            },
     *            {
     *              "id": 208,
     *              "title": "Project of admin"
     *            },
     *         ],
     *         "requester":
     *         [
     *            {
     *               "id": 1014,
     *               "username": "admin"
     *            },
     *            {
     *               "id": 1015,
     *               "username": "manager"
     *            },
     *         ],
     *         "company":
     *         [
     *           {
     *              "id": 317,
     *              "title": "Web-Solutions"
     *           },
     *           {
     *              "id": 318,
     *              "title": "LanSystems"
     *           }
     *         ],
     *         "tag":
     *         [
     *           {
     *              "id": 9,
     *              "title": "Free Time"
     *            },
     *           {
     *              "id": 10,
     *              "title": "Work"
     *            },
     *            {
     *               "id": 12,
     *               "title": "Another Admin Public Tag"
     *             }
     *          ],
     *          "assigner":
     *          [
     *            {
     *               "id": 1014,
     *               "username": "admin"
     *            }
     *          ]
     *      }
     * @ApiDoc(
     *  description="Get all options for task: statuses, available projects, available requesters, available companies, available assigners, available tags",
     *  requirements={
     *     {
     *       "name"="taskId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed task"
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
     * @param int $taskId
     *
     * @return JsonResponse|Response
     * @throws \LogicException
     */
    public function getTaskOptionsAction(int $taskId)
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($taskId);

        if (!$task instanceof Task) {
            return $this->createApiResponse([
                'message' => 'Task with requested id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        // Check if user can view requested Task
        if (!$this->get('task_voter')->isGranted(VoteOptions::SHOW_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        // Return arrays of options
        $statusesArray = $this->get('status_service')->getListOfExistedStatuses();

        // Available projects are where logged user have CREATE_TASK ACL
        // If task is moved to project where assigned user has not permission to RESOLVE_TASK, this assigned user will be removed
        // Admin can use All existed projects
        $isAdmin = $this->get('task_voter')->isAdmin();
        $projectsArray = $this->get('project_service')->getListOfAvailableProjects($this->getUser(), $isAdmin, ProjectAclOptions::CREATE_TASK);
        // Every user can be requester
        $requesterArray = $this->get('api_user.service')->getListOfAllUsers();
        // Every company is available
        $companyArray = $this->get('api_company.service')->getListOfAllCompanies();
        // Public and logged user's tags are available
        $tagArray = $this->get('tag_service')->getListOfUsersTags($this->getUser()->getId());
        // Available assigners are based on project of task
        // If task has project, assigner has to have RESOLVE_TASK ACL in user_has_project
        // If task has not project, just creator of task can be assigned to it
        if ($task->getProject()) {
            $project = $task->getProject();
            $assignArray = $this->get('api_user.service')->getListOfAvailableProjectAssigners($project, ProjectAclOptions::RESOLVE_TASK);
        } else {
            $assignArray = [
                [
                    'id' => $task->getCreatedBy()->getId(),
                    'username' => $task->getCreatedBy()->getUsername()
                ]
            ];
        }

        $response = [
            'status' => $statusesArray,
            'project' => $projectsArray,
            'requester' => $requesterArray,
            'company' => $companyArray,
            'tag' => $tagArray,
            'assigner' => $assignArray
        ];
        return $this->json($response, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * @param Task $task
     * @param array $requestData
     * @param bool $create
     *
     * @param array $ids
     *
     * @return JsonResponse|Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    private function updateTaskEntity(Task $task, array $requestData, $create, array $ids)
    {
        $allowedUserEntityParams = [
            'title',
            'description',
            'deadline',
            'closed_at',
            'important',
            'started_at',
            'work',
            'work_time'
        ];

        $requestDetailData = false;
        if (isset($requestData['task_data']) && count($requestData['task_data']) > 0) {
            $requestDetailData = $requestData['task_data'];
            unset($requestData['task_data']);
        }

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!in_array($key, $allowedUserEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for Task Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        // Control Date time objects: started_at, closed_at, deadline
        // Expected Date is in string format: Day/Month/Year Hours:Minutes:Seconds
        if (isset($requestData['started_at'])) {
            try {
                $startedAtDateTimeObject = new \Datetime($requestData['started_at']);
                $task->setStartedAt($startedAtDateTimeObject);
                unset($requestData['started_at']);
            } catch (\Exception $e) {
                return $this->createApiResponse([
                    'message' => 'started_at parameter is not in a valid format! Expected format: Unix',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }
        if (isset($requestData['closed_at'])) {
            try {
                $startedAtDateTimeObject = new \Datetime($requestData['closed_at']);
                $task->setClosedAt($startedAtDateTimeObject);
                unset($requestData['closed_at']);
            } catch (\Exception $e) {
                return $this->createApiResponse([
                    'message' => 'closed_at parameter is not in a valid format! Expected format: Unix',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }
        if (isset($requestData['deadline'])) {
            try {
                $startedAtDateTimeObject = new \Datetime($requestData['deadline']);
                $task->setDeadline($startedAtDateTimeObject);
                unset($requestData['deadline']);
            } catch (\Exception $e) {
                return $this->createApiResponse([
                    'message' => 'deadline parameter is not in a valid format! Expected format: Unix',
                ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
            }
        }


        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($task, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            if (!$ids['id']) {
                $ids['id'] = $task->getId();
            }

            /**
             * Fill TaskData Entity if some of its parameters were sent
             */
            if ($requestDetailData) {
                /** @var array $taskData */
                $taskData = $requestDetailData;
                foreach ($taskData as $key => $value) {
                    $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($key);
                    if ($taskAttribute instanceof TaskAttribute) {
                        $cd = $this->getDoctrine()->getRepository('APITaskBundle:TaskData')->findOneBy([
                            'taskAttribute' => $taskAttribute,
                            'task' => $task,
                        ]);

                        if (!$cd instanceof TaskData) {
                            $cd = new TaskData();
                            $cd->setTask($task);
                            $cd->setTaskAttribute($taskAttribute);
                        }

                        $cdErrors = $this->get('entity_processor')->processEntity($cd, ['value' => $value]);
                        if (false === $cdErrors) {
                            $task->addTaskDatum($cd);
                            $this->getDoctrine()->getManager()->persist($task);
                            $this->getDoctrine()->getManager()->persist($cd);
                            $this->getDoctrine()->getManager()->flush();
                        } else {
                            $this->createApiResponse([
                                'message' => 'The value of task_data with key: ' . $key . ' is invalid',
                            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        }
                    } else {
                        return $this->createApiResponse([
                            'message' => 'The key: ' . $key . ' of Task Attribute is not valid (Task Attribute with this ID doesn\'t exist)',
                        ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    }
                }
            }

            $response = $this->get('task_service')->getTaskResponse($ids);
            $responseData['data'] = $response['data'][0];
            $responseLinks['_links'] = $response['_links'];

            return $this->json(array_merge($responseData, $responseLinks), $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE,
        ];

        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \LogicException
     */
    private function getFilterData(Request $request): array
    {
        $data = [];

        $search = $request->get('search');
        $status = $request->get('status');
        $project = $request->get('project');
        $creator = $request->get('creator');
        $requester = $request->get('requester');
        $company = $request->get('company');
        $assigned = $request->get('assigned');
        $tag = $request->get('tag');
        $follower = $request->get('follower');
        $created = $request->get('createdTime');
        $started = $request->get('startedTime');
        $deadline = $request->get('deadlineTime');
        $closed = $request->get('closedTime');
        $archived = $request->get('archived');
        $important = $request->get('important');
        $addedParameters = $request->get('addedParameters');

        if (null !== $search) {
            $data[FilterAttributeOptions::SEARCH] = $search;
        }
        if (null !== $status) {
            $data[FilterAttributeOptions::STATUS] = $status;
        }
        if (null !== $project) {
            $data[FilterAttributeOptions::PROJECT] = $project;
        }
        if (null !== $creator) {
            $data[FilterAttributeOptions::CREATOR] = $creator;
        }
        if (null !== $requester) {
            $data[FilterAttributeOptions::REQUESTER] = $requester;
        }
        if (null !== $company) {
            $data[FilterAttributeOptions::COMPANY] = $company;
        }
        if (null !== $assigned) {
            $data[FilterAttributeOptions::ASSIGNED] = $assigned;
        }
        if (null !== $tag) {
            $data[FilterAttributeOptions::TAG] = $tag;
        }
        if (null !== $follower) {
            $data[FilterAttributeOptions::FOLLOWER] = $follower;
        }
        if (null !== $created) {
            $data[FilterAttributeOptions::CREATED] = $created;
        }
        if (null !== $started) {
            $data[FilterAttributeOptions::STARTED] = $started;
        }
        if (null !== $deadline) {
            $data[FilterAttributeOptions::DEADLINE] = $deadline;
        }
        if (null !== $closed) {
            $data[FilterAttributeOptions::CLOSED] = $closed;
        }
        if ('true' === strtolower($archived)) {
            $data[FilterAttributeOptions::ARCHIVED] = $archived;
        }
        if ('true' === strtolower($important)) {
            $data[FilterAttributeOptions::IMPORTANT] = $important;
        }
        if (null !== $addedParameters) {
            $data[FilterAttributeOptions::ADDED_PARAMETERS] = $addedParameters;
        }

        return $this->processFilterData($data);
    }

    /**
     * @param array $filterDataArray
     *
     * @return array
     * @throws \LogicException
     */
    private function getFilterDataFromSavedFilterArray(array $filterDataArray): array
    {
        $data = [];

        if (isset($filterDataArray[FilterAttributeOptions::SEARCH])) {
            $data[FilterAttributeOptions::SEARCH] = $filterDataArray[FilterAttributeOptions::SEARCH];
        }
        if (isset($filterDataArray[FilterAttributeOptions::STATUS])) {
            $data[FilterAttributeOptions::STATUS] = $filterDataArray[FilterAttributeOptions::STATUS];
        }
        if (isset($filterDataArray[FilterAttributeOptions::PROJECT])) {
            $data[FilterAttributeOptions::PROJECT] = $filterDataArray[FilterAttributeOptions::PROJECT];
        }
        if (isset($filterDataArray[FilterAttributeOptions::CREATOR])) {
            $data[FilterAttributeOptions::CREATOR] = $filterDataArray[FilterAttributeOptions::CREATOR];
        }
        if (isset($filterDataArray[FilterAttributeOptions::REQUESTER])) {
            $data[FilterAttributeOptions::REQUESTER] = $filterDataArray[FilterAttributeOptions::REQUESTER];
        }
        if (isset($filterDataArray[FilterAttributeOptions::COMPANY])) {
            $data[FilterAttributeOptions::COMPANY] = $filterDataArray[FilterAttributeOptions::COMPANY];
        }
        if (isset($filterDataArray[FilterAttributeOptions::ASSIGNED])) {
            $data[FilterAttributeOptions::ASSIGNED] = $filterDataArray[FilterAttributeOptions::ASSIGNED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::TAG])) {
            $data[FilterAttributeOptions::TAG] = $filterDataArray[FilterAttributeOptions::TAG];
        }
        if (isset($filterDataArray[FilterAttributeOptions::FOLLOWER])) {
            $data[FilterAttributeOptions::FOLLOWER] = $filterDataArray[FilterAttributeOptions::FOLLOWER];
        }
        if (isset($filterDataArray[FilterAttributeOptions::CREATED])) {
            $data[FilterAttributeOptions::CREATED] = $filterDataArray[FilterAttributeOptions::CREATED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::STARTED])) {
            $data[FilterAttributeOptions::STARTED] = $filterDataArray[FilterAttributeOptions::STARTED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::DEADLINE])) {
            $data[FilterAttributeOptions::DEADLINE] = $filterDataArray[FilterAttributeOptions::DEADLINE];
        }
        if (isset($filterDataArray[FilterAttributeOptions::CLOSED])) {
            $data[FilterAttributeOptions::CLOSED] = $filterDataArray[FilterAttributeOptions::CLOSED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::ARCHIVED])) {
            $data[FilterAttributeOptions::ARCHIVED] = $filterDataArray[FilterAttributeOptions::ARCHIVED];
        }
        if (isset($filterDataArray[FilterAttributeOptions::IMPORTANT])) {
            $data[FilterAttributeOptions::IMPORTANT] = $filterDataArray[FilterAttributeOptions::IMPORTANT];
        }
        if (isset($filterDataArray[FilterAttributeOptions::ADDED_PARAMETERS])) {
            $data[FilterAttributeOptions::ADDED_PARAMETERS] = $filterDataArray[FilterAttributeOptions::ADDED_PARAMETERS];
        }

        return $this->processFilterData($data);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \LogicException
     */
    private function processFilterData(array $data): array
    {
        // Ina-beznejsia moznost ako zadavat pole hodnot v URL adrese, ktora vracia priamo pole: index.php?id[]=1&id[]=2&id[]=3&name=john
        // na zakodovanie dat do URL je mozne pouzit encodeURIComponent

        $inFilter = [];
        $dateFilter = [];
        $equalFilter = [];
        $isNullFilter = [];
        $notAndCurrentFilter = [];
        $searchFilter = null;

        $inFilterAddedParams = [];
        $dateFilterAddedParams = [];
        $equalFilterAddedParams = [];

        $filterForUrl = [];

        if (isset($data[FilterAttributeOptions::SEARCH])) {
            $searchFilter = $data[FilterAttributeOptions::SEARCH];
            $filterForUrl['search'] = '&search=' . $data['search'];
        }
        if (isset($data[FilterAttributeOptions::STATUS])) {
            $inFilter['status.id'] = explode(',', $data[FilterAttributeOptions::STATUS]);
            $filterForUrl['status'] = '&status=' . $data[FilterAttributeOptions::STATUS];
        }
        if (isset($data[FilterAttributeOptions::PROJECT])) {
            $project = $data[FilterAttributeOptions::PROJECT];
            if ('not' === strtolower($project)) {
                $isNullFilter[] = 'task.project';
            } elseif ('current-user' === strtolower($project)) {
                $equalFilter['projectCreator.id'] = $this->getUser()->getId();
            } else {
                $inFilter['project.id'] = explode(',', $project);
            }
            $filterForUrl['project'] = '&project=' . $project;
        }
        if (isset($data[FilterAttributeOptions::CREATOR])) {
            $creator = $data[FilterAttributeOptions::CREATOR];
            if ('current-user' === strtolower($creator)) {
                $equalFilter['createdBy.id'] = $this->getUser()->getId();
            } else {
                $inFilter['createdBy.id'] = explode(',', $creator);
            }
            $filterForUrl['createdBy'] = '&creator=' . $creator;
        }
        if (isset($data[FilterAttributeOptions::REQUESTER])) {
            $requester = $data[FilterAttributeOptions::REQUESTER];
            if ('current-user' === strtolower($requester)) {
                $equalFilter['requestedBy.id'] = $this->getUser()->getId();
            } else {
                $inFilter['requestedBy.id'] = explode(',', $requester);
            }
            $filterForUrl['requestedBy'] = '&requester=' . $requester;
        }
        if (isset($data[FilterAttributeOptions::COMPANY])) {
            $company = $data[FilterAttributeOptions::COMPANY];
            if ('current-user' === strtolower($company)) {
                $equalFilter['company.id'] = $this->getUser()->getId();
            } else {
                $inFilter['company.id'] = explode(',', $company);
            }
            $filterForUrl['company'] = '&company=' . $company;
        }
        if (isset($data[FilterAttributeOptions::ASSIGNED])) {
            $assigned = $data[FilterAttributeOptions::ASSIGNED];
            $assignedArray = explode(',', $assigned);

            if (in_array('not', $assignedArray, true) && in_array('current-user', $assignedArray, true)) {
                $notAndCurrentFilter[] = [
                    'not' => 'thau.user',
                    'equal' => [
                        'key' => 'assignedUser.id',
                        'value' => $this->getUser()->getId(),
                    ],
                ];
            } elseif ('not' === strtolower($assigned)) {
                $isNullFilter[] = 'thau.user';
            } elseif ('current-user' === strtolower($assigned)) {
                $equalFilter['assignedUser.id'] = $this->getUser()->getId();
            } else {
                $inFilter['assignedUser.id'] = explode(',', $assigned);
            }

            $filterForUrl['assigned'] = '&assigned=' . $assigned;
        }
        if (isset($data[FilterAttributeOptions::TAG])) {
            $tag = $data[FilterAttributeOptions::TAG];
            $inFilter['tags.id'] = explode(',', $tag);
            $filterForUrl['tag'] = '&tag=' . $tag;
        }
        if (isset($data[FilterAttributeOptions::FOLLOWER])) {
            $follower = $data[FilterAttributeOptions::FOLLOWER];
            if ('current-user' === $follower) {
                $equalFilter['followers.id'] = $this->getUser()->getId();
            } else {
                $inFilter['followers.id'] = explode(',', $follower);
            }
            $filterForUrl['followers'] = '&follower=' . $follower;
        }
        if (isset($data[FilterAttributeOptions::CREATED])) {
            $created = $data[FilterAttributeOptions::CREATED];
            $fromToData = $this->separateFromToDateData($created);
            $dateFilter['task.createdAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['created'] = '&createdTime=' . $created;
        }
        if (isset($data[FilterAttributeOptions::STARTED])) {
            $started = $data[FilterAttributeOptions::STARTED];
            $fromToData = $this->separateFromToDateData($started);
            $dateFilter['task.startedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['started'] = '&startedTime=' . $started;
        }
        if (isset($data[FilterAttributeOptions::DEADLINE])) {
            $deadline = $data[FilterAttributeOptions::DEADLINE];
            $fromToData = $this->separateFromToDateData($deadline);
            $dateFilter['task.deadline'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['deadline'] = '&deadlineTime=' . $deadline;
        }
        if (isset($data[FilterAttributeOptions::CLOSED])) {
            $closed = $data[FilterAttributeOptions::CLOSED];
            $fromToData = $this->separateFromToDateData($closed);
            $dateFilter['task.closedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to'],
            ];
            $filterForUrl['closed'] = '&closedTime=' . $closed;
        }
        if (isset($data[FilterAttributeOptions::ARCHIVED])) {
            if ('true' === strtolower($data[FilterAttributeOptions::ARCHIVED])) {
                $equalFilter['project.is_active'] = 0;
                $filterForUrl['archived'] = '&archived=TRUE';
            }
        }
        if (isset($data[FilterAttributeOptions::IMPORTANT])) {
            if ('true' === strtolower($data[FilterAttributeOptions::IMPORTANT])) {
                $equalFilter['task.important'] = 1;
                $filterForUrl['important'] = '&important=TRUE';
            }
        }
        if (isset($data[FilterAttributeOptions::ADDED_PARAMETERS])) {
            $addedParameters = $data[FilterAttributeOptions::ADDED_PARAMETERS];
            $arrayOfAddedParameters = explode('&', $addedParameters);

            if (!empty($arrayOfAddedParameters[0])) {
                $filterForUrl['addedParameters'] = '&addedParameters=' . $addedParameters;

                foreach ($arrayOfAddedParameters as $value) {
                    $strpos = explode('=', $value);
                    $attributeId = $strpos[0];

                    // Check if TaskAttribute exists, select filter type based on it's TYPE
                    $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($attributeId);
                    if ($taskAttribute instanceof TaskAttribute) {
                        $typeOfTaskAttribute = $taskAttribute->getType();
                        $attributeValues = explode(',', $strpos[1]);

                        if ('checkbox' === $typeOfTaskAttribute) {
                            if ('true' === strtolower($strpos[1])) {
                                $equalFilterAddedParams[$attributeId] = 1;
                            } elseif ('false' === strtolower($strpos[1])) {
                                $equalFilterAddedParams[$attributeId] = 0;
                            }
                        }

                        if ('date' === $typeOfTaskAttribute) {
                            $dateFilterAddedParams[$attributeId] = $attributeValues;
                        }

                        $inFilterAddedParams[$attributeId] = $attributeValues;
                    }
                }
            }
        }

        return [
            'inFilter' => $inFilter,
            'equalFilter' => $equalFilter,
            'dateFilter' => $dateFilter,
            'isNullFilter' => $isNullFilter,
            'searchFilter' => $searchFilter,
            'notAndCurrentFilter' => $notAndCurrentFilter,
            'inFilterAddedParams' => $inFilterAddedParams,
            'equalFilterAddedParams' => $equalFilterAddedParams,
            'dateFilterAddedParams' => $dateFilterAddedParams,
            'filterForUrl' => $filterForUrl,
        ];
    }

    /**
     * @param string $created
     *
     * @return array
     */
    private function separateFromToDateData(string $created): array
    {
        $fromPosition = strpos($created, 'FROM=');
        $toPosition = strpos($created, 'TO=');

        $toData = null;
        $fromData = null;
        if (false !== $fromPosition && false !== $toPosition) {
            $fromData = substr($created, $fromPosition + 5, $toPosition - 6);
            $toData = substr($created, $toPosition + 3);
        } elseif (false !== $fromPosition && false === $toPosition) {
            $fromData = substr($created, $fromPosition + 5);
        } elseif (false !== $toPosition && false === $fromPosition) {
            $toData = substr($created, $toPosition + 3);
        }

        return [
            'from' => $fromData,
            'to' => $toData,
        ];
    }

    /**
     * @param TaskHasAssignedUser $entity
     * @param Project $project
     * @return bool
     */
    private function checkIfUserHasResolveTaskAclPermission(TaskHasAssignedUser $entity, Project $project):bool
    {
        $user = $entity->getUser();
        $userHasProject = $this->getDoctrine()->getRepository('APITaskBundle:UserHasProject')->findOneBy([
            'user' => $user,
            'project' => $project
        ]);
        if ($userHasProject instanceof UserHasProject) {
            $acl = $userHasProject->getAcl();
            if (in_array(ProjectAclOptions::RESOLVE_TASK, $acl, true)) {
                return true;
            } else {
                false;
            }
        }
        return false;
    }
}
