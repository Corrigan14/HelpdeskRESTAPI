<?php

namespace API\TaskBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskAttribute;
use API\TaskBundle\Entity\TaskData;
use API\TaskBundle\Security\VoteOptions;
use API\TaskBundle\Services\FilterAttributeOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskController
 *
 * @package API\TaskBundle\Controller
 */
class TaskController extends ApiBaseController implements ControllerInterface
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
     *                    "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
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
     *                   "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
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
     *                   "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
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
     *                     "image": null
     *                  }
     *                }
     *             ]
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
     *       "description"="A coma separated dates in format FROM=2015-02-04T05:10:58+05:30,TO=2015-02-04T05:10:58+05:30
     *        Another option:
     *          TO=NOW - just tasks created to NOW datetime are returned."
     *     },
     *     {
     *       "name"="startedTime",
     *       "description"="A coma separated dates in format FROM=2015-02-04T05:10:58+05:30,TO=2015-02-04T05:10:58+05:30
     *        Another option:
     *          TO=NOW - just tasks started to NOW datetime are returned."
     *     },
     *     {
     *       "name"="deadlineTime",
     *       "description"="A coma separated dates in format FROM=2015-02-04T05:10:58+05:30,TO=2015-02-04T05:10:58+05:30
     *       Another option:
     *          TO=NOW - just tasks with deadline to NOW datetime are returned."
     *     },
     *     {
     *       "name"="closedTime",
     *       "description"="A coma separated dates in format FROM=2015-02-04T05:10:58+05:30,TO=2015-02-04T05:10:58+05:30
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
     *      401 ="Unauthorized request",
     *      404 ="Not found entity"
     *  }
     * )
     *
     * @param Request $request
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
            'filtersForUrl' => $filterData['filterForUrl']
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
     *                    "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
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
     *                   "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
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
     *                   "acl": "[\"login_to_system\",\"create_tasks\",\"create_projects\",\"create_user_with_role_customer\",\"company_settings\",\"report_filters\",\"sent_emails_from_comments\",\"update_all_tasks\"]",
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
     *                     "image": null
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
     * @return JsonResponse|Response
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
            'filtersForUrl' => $filterData['filterForUrl']
        ];

        $tasksArray = $this->get('task_service')->getTasksResponse($page, $options);
        return $this->json($tasksArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *          "0":
     *          {
     *             "id": 92,
     *             "title": "Task 1 - user is creator, user is requested",
     *             "description": "Description of Task 1",
     *             "important": false,
     *             "createdAt":
     *             {
     *                "date": "2017-01-03 14:16:51.000000",
     *                "timezone_type": 3,
     *                "timezone": "Europe/Berlin"
     *             },
     *             "updatedAt":
     *             {
     *                "date": "2017-01-03 14:16:51.000000",
     *                "timezone_type": 3,
     *                "timezone": "Europe/Berlin"
     *             },
     *             "taskData":
     *             {
     *               "0":
     *               {
     *                  "id": 61,
     *                  "value": "some input",
     *                  "taskAttribute":
     *                   {
     *                       "id": 85,
     *                       "title": "input task additional attribute",
     *                       "type": "input",
     *                       "is_active": true
     *                   }
     *               },
     *               "1":
     *               {
     *                  "id": 62,
     *                  "value": "select1",
     *                  "taskAttribute":
     *                  {
     *                     "id": 86,
     *                     "title": "select task additional attribute",
     *                     "type": "simple_select",
     *                     "options": "a:3:{s:7:\"select1\";s:7:\"select1\";s:7:\"select2\";s:7:\"select2\";s:7:\"select3\";s:7:\"select3\";}",
     *                     "is_active": true
     *                   }
     *                }
     *             },
     *             "project":
     *             {
     *                "id": 141,
     *                "title": "Project of user 1",
     *                "description": "Description of project 1.",
     *                "is_active": false,
     *                "createdAt": "2017-01-19T17:47:22+0100",
     *                "updatedAt": "2017-01-19T17:47:22+0100"
     *             },
     *             "createdBy":
     *             {
     *                "id": 116,
     *                "username": "user",
     *                "password": "$2y$13$uMaX2SHUoFErPHs2ojwe6.JCZrvCJlHKJ3D2O1BPBRWl/.TtZPzhK",
     *                "email": "user@user.sk",
     *                "roles": "[\"ROLE_USER\"]",
     *                "is_active": true,
     *                "acl": "[]",
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
     *                   "id": 87,
     *                   "title": "LanSystems",
     *                   "ico": "110258782",
     *                   "dic": "12587458996244",
     *                   "street": "Ina cesta 125",
     *                   "city": "Bratislava",
     *                   "zip": "021478",
     *                   "country": "Slovenska Republika",
     *                   "is_active": true
     *                 }
     *             },
     *             "requestedBy":
     *             {
     *                "id": 116,
     *                "username": "user",
     *                "password": "$2y$13$uMaX2SHUoFErPHs2ojwe6.JCZrvCJlHKJ3D2O1BPBRWl/.TtZPzhK",
     *                "email": "user@user.sk",
     *                "roles": "[\"ROLE_USER\"]",
     *                "is_active": true,
     *                "acl": "[]",
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
     *                   "id": 87,
     *                   "title": "LanSystems",
     *                   "ico": "110258782",
     *                   "dic": "12587458996244",
     *                   "street": "Ina cesta 125",
     *                   "city": "Bratislava",
     *                   "zip": "021478",
     *                   "country": "Slovenska Republika",
     *                   "is_active": true
     *                 }
     *              },
     *              "taskHasAssignedUsers":
     *              {
     *                 "0":
     *                 {
     *                    "id": 42,
     *                    "createdAt": "2017-01-19T17:47:22+0100",
     *                    "updatedAt": "2017-01-19T17:47:22+0100",
     *                    "status":
     *                    {
     *                       "id": 126,
     *                       "title": "new",
     *                       "is_active": true
     *                     },
     *                     "user":
     *                     {
     *                        "id": 116,
     *                        "username": "user",
     *                        "password": "$2y$13$uMaX2SHUoFErPHs2ojwe6.JCZrvCJlHKJ3D2O1BPBRWl/.TtZPzhK",
     *                        "email": "user@user.sk",
     *                        "roles": "[\"ROLE_USER\"]",
     *                        "is_active": true,
     *                        "acl": "[]"
     *                      }
     *                   }
     *                 }
     *              }
     *          }
     *        },
     *       "_links":
     *       {
     *         "put": "/api/v1/task-bundle/tasks/1/project/all/user/all",
     *         "patch": "/api/v1/task-bundle/tasks/1/project/all/user/all",
     *         "delete": "/api/v1/task-bundle/tasks/1"
     *       }
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

        $response = $this->get('task_service')->getTaskResponse($task);
        $responseData['data'] = $response['data'][0];
        $responseLinks['_links'] = $response['_links'];
        return $this->json(array_merge($responseData,$responseLinks), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *          "id": 1,
     *          "title": "Task 1 - user is creator, user is requested",
     *          "description": "Description of Task 1",
     *          "important": false,
     *          "created_by":⊕{...},
     *          "requested_by": ⊕{...},
     *          "project": ⊕{...},
     *          "task_data":
     *          {
     *             "0":
     *             {
     *               "id": 1,
     *               "value": "some input"
     *             },
     *            "1":
     *            {
     *              "id": 2,
     *              "value": "select1"
     *            }
     *          }
     *          "followers": ⊕{...}
     *          "tags": ⊕{...}
     *          "created_at": "2016-12-09T07:39:52+0100",
     *          "updated_at": "2016-12-09T07:39:52+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task/2",
     *           "patch": "/api/v1/task-bundle/task/2",
     *           "delete": "/api/v1/task-bundle/task/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Task Entity with extra Task Data.
     *  This can be added by attributes: task_data[task_attribute_id] = value,
     *  attributes must be defined in the TaskAttribute Entity.
     *  Project and/or Requested User can be added to this Task",
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
     * @param int|string $projectId
     * @param int|string $requestedUserId
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function createAction(Request $request, $projectId = 'all', $requestedUserId = 'all')
    {
        $task = new Task();

        // Check if project and requested user exists
        if ('all' !== $projectId && '{projectId}' !== $projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setProject($project);

        } else {
            $project = null;
        }

        if ('all' !== $requestedUserId && '{requestedUserId}' !== $requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);

        } else {
            $requestedUser = null;
            $task->setRequestedBy($this->getUser());
        }

        // Check if user can create task in selected project
        if (!$this->get('task_voter')->isGranted(VoteOptions::CREATE_TASK, $project)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        $task->setCreatedBy($this->getUser());
        $task->setImportant(false);

        return $this->updateTaskEntity($task, $requestData, true);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *          "id": 1,
     *          "title": "Task 1 - user is creator, user is requested",
     *          "description": "Description of Task 1",
     *          "important": false,
     *          "created_by":⊕{...},
     *          "requested_by": ⊕{...},
     *          "project": ⊕{...},
     *          "task_data":
     *          {
     *             "0":
     *             {
     *               "id": 1,
     *               "value": "some input"
     *             },
     *            "1":
     *            {
     *              "id": 2,
     *              "value": "select1"
     *            }
     *          }
     *          "followers": ⊕{...}
     *          "tags": ⊕{...}
     *          "created_at": "2016-12-09T07:39:52+0100",
     *          "updated_at": "2016-12-09T07:39:52+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task/2",
     *           "patch": "/api/v1/task-bundle/task/2",
     *           "delete": "/api/v1/task-bundle/task/2"
     *         }
     *      }
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
     * @param bool|string $projectId
     * @param bool|string $requestedUserId
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request, $projectId = 'all', $requestedUserId = 'all')
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        // Check if project and requested user exists
        if ('all' !== $projectId && '{projectId}' !== $projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setProject($project);
        }

        if ('all' !== $requestedUserId && '{requestedUserId}' !== $requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateTaskEntity($task, $requestData);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *          "id": 1,
     *          "title": "Task 1 - user is creator, user is requested",
     *          "description": "Description of Task 1",
     *          "important": false,
     *          "created_by":⊕{...},
     *          "requested_by": ⊕{...},
     *          "project": ⊕{...},
     *          "task_data":
     *          {
     *             "0":
     *             {
     *               "id": 1,
     *               "value": "some input"
     *             },
     *            "1":
     *            {
     *              "id": 2,
     *              "value": "select1"
     *            }
     *          }
     *          "followers": ⊕{...}
     *          "tags": ⊕{...}
     *          "created_at": "2016-12-09T07:39:52+0100",
     *          "updated_at": "2016-12-09T07:39:52+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/task/2",
     *           "patch": "/api/v1/task-bundle/task/2",
     *           "delete": "/api/v1/task-bundle/task/2"
     *         }
     *      }
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
     * @param bool|string $projectId
     * @param bool|string $requestedUserId
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updatePartialAction(int $id, Request $request, $projectId = 'all', $requestedUserId = 'all')
    {
        $task = $this->getDoctrine()->getRepository('APITaskBundle:Task')->find($id);

        if (!$task instanceof Task) {
            return $this->notFoundResponse();
        }

        // Check if project and requested user exists
        if ('all' !== $projectId && '{projectId}' !== $projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);

            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setProject($project);
        }

        if ('all' !== $requestedUserId && '{requestedUserId}' !== $requestedUserId) {
            $requestedUser = $this->getDoctrine()->getRepository('APICoreBundle:User')->find($requestedUserId);

            if (!$requestedUser instanceof User) {
                return $this->createApiResponse([
                    'message' => 'Requested user with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }

            $task->setRequestedBy($requestedUser);
        }

        // Check if user can update selected task
        if (!$this->get('task_voter')->isGranted(VoteOptions::UPDATE_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $requestData = $request->request->all();

        return $this->updateTaskEntity($task, $requestData);
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
     * @param Task $task
     * @param array $requestData
     * @param bool $create
     *
     * @return JsonResponse|Response
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    private function updateTaskEntity(Task $task, array $requestData, $create = false)
    {
        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($task, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            /**
             * Fill TaskData Entity if some of its parameters were sent
             */
            if (isset($requestData['task_data']) && count($requestData['task_data']) > 0) {
                /** @var array $taskData */
                $taskData = $requestData['task_data'];
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

            $response = $this->get('task_service')->getTaskResponse($task);
            return $this->createApiResponse($response, $statusCode);
        }

        return $this->createApiResponse($errors, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \LogicException
     */
    private function getFilterData(Request $request): array
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
            $searchFilter = $search;
            $filterForUrl['search'] = '&search=' . $search;
        }
        if (null !== $status) {
            $inFilter['status.id'] = explode(",", $status);
            $filterForUrl['status'] = '&status=' . $status;
        }
        if (null !== $project) {
            if ('not' === strtolower($project)) {
                $isNullFilter[] = 'task.project';
            } elseif ('current-user' === strtolower($project)) {
                $equalFilter['projectCreator.id'] = $this->getUser()->getId();
            } else {
                $inFilter['project.id'] = explode(",", $project);
            }
            $filterForUrl['project'] = '&project=' . $project;
        }
        if (null !== $creator) {
            if ('current-user' === strtolower($creator)) {
                $equalFilter['createdBy.id'] = $this->getUser()->getId();
            } else {
                $inFilter['createdBy.id'] = explode(",", $creator);
            }
            $filterForUrl['createdBy'] = '&creator=' . $creator;
        }
        if (null !== $requester) {
            if ('current-user' === strtolower($requester)) {
                $equalFilter['requestedBy.id'] = $this->getUser()->getId();
            } else {
                $inFilter['requestedBy.id'] = explode(",", $requester);
            }
            $filterForUrl['requestedBy'] = '&requester=' . $requester;
        }
        if (null !== $company) {
            if ('current-user' === strtolower($company)) {
                $equalFilter['company.id'] = $this->getUser()->getId();
            } else {
                $inFilter['company.id'] = explode(",", $company);
            }
            $filterForUrl['company'] = '&company=' . $company;
        }
        if (null !== $assigned) {
            $assignedArray = explode(",", $assigned);

            if (in_array('not', $assignedArray) && in_array('current-user', $assignedArray)) {
                $notAndCurrentFilter[] = [
                    'not' => 'thau.user',
                    'equal' => [
                        'key' => 'assignedUser.id',
                        'value' => $this->getUser()->getId()
                    ],
                ];
            } elseif ('not' === strtolower($assigned)) {
                $isNullFilter[] = 'thau.user';
            } elseif ('current-user' === strtolower($assigned)) {
                $equalFilter['assignedUser.id'] = $this->getUser()->getId();
            } else {
                $inFilter['assignedUser.id'] = explode(",", $assigned);
            }

            $filterForUrl['assigned'] = '&assigned=' . $assigned;
        }
        if (null !== $tag) {
            $inFilter['tags.id'] = explode(",", $tag);
            $filterForUrl['tag'] = '&tag=' . $tag;
        }
        if (null !== $follower) {
            if ('current-user' === $follower) {
                $equalFilter['followers.id'] = $this->getUser()->getId();
            } else {
                $inFilter['followers.id'] = explode(",", $follower);
            }
            $filterForUrl['followers'] = '&follower=' . $follower;
        }
        if (null !== $created) {
            $fromToData = $this->separateFromToDateData($created);
            $dateFilter['task.createdAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to']
            ];
            $filterForUrl['created'] = '&createdTime=' . $created;
        }
        if (null !== $started) {
            $fromToData = $this->separateFromToDateData($started);
            $dateFilter['task.startedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to']
            ];
            $filterForUrl['started'] = '&startedTime=' . $started;
        }
        if (null !== $deadline) {
            $fromToData = $this->separateFromToDateData($deadline);
            $dateFilter['task.deadline'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to']
            ];
            $filterForUrl['deadline'] = '&deadlineTime=' . $deadline;
        }
        if (null !== $closed) {
            $fromToData = $this->separateFromToDateData($closed);
            $dateFilter['task.closedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to']
            ];
            $filterForUrl['closed'] = '&closedTime=' . $closed;
        }
        if ('true' === strtolower($archived)) {
            $equalFilter['project.is_active'] = 0;
            $filterForUrl['archived'] = '&archived=TRUE';
        }
        if ('true' === strtolower($important)) {
            $equalFilter['task.important'] = 1;
            $filterForUrl['important'] = '&important=TRUE';
        }
        if (null !== $addedParameters) {
            $arrayOfAddedParameters = explode("&", $addedParameters);

            if (!empty($arrayOfAddedParameters[0])) {
                $filterForUrl['addedParameters'] = '&addedParameters=' . $addedParameters;

                foreach ($arrayOfAddedParameters as $value) {
                    $strpos = explode('=', $value);
                    $attributeId = $strpos[0];

                    // Check if TaskAttribute exists, select filter type based on it's TYPE
                    $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($attributeId);
                    if ($taskAttribute instanceof TaskAttribute) {
                        $typeOfTaskAttribute = $taskAttribute->getType();
                        $attributeValues = explode(",", $strpos[1]);

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
            'filterForUrl' => $filterForUrl
        ];
    }

    /**
     * @param array $filterDataArray
     * @return array
     */
    private function getFilterDataFromSavedFilterArray(array $filterDataArray): array
    {
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

        if (isset($filterDataArray[FilterAttributeOptions::SEARCH])) {
            $searchFilter = $filterDataArray[FilterAttributeOptions::SEARCH];
            $filterForUrl['search'] = '&search=' . $filterDataArray[FilterAttributeOptions::SEARCH];
        }
        if (isset($filterDataArray[FilterAttributeOptions::STATUS])) {
            $inFilter['status.id'] = explode(",", $filterDataArray[FilterAttributeOptions::STATUS]);
            $filterForUrl['status'] = '&status=' . $filterDataArray[FilterAttributeOptions::STATUS];
        }
        if (isset($filterDataArray[FilterAttributeOptions::PROJECT])) {
            $project = $filterDataArray[FilterAttributeOptions::PROJECT];
            if ('not' === strtolower($project)) {
                $isNullFilter[] = 'task.project';
            } elseif ('current-user' === strtolower($project)) {
                $equalFilter['projectCreator.id'] = $this->getUser()->getId();
            } else {
                $inFilter['project.id'] = explode(",", $project);
            }
            $filterForUrl['project'] = '&project=' . $project;
        }
        if (isset($filterDataArray[FilterAttributeOptions::CREATOR])) {
            $creator = $filterDataArray[FilterAttributeOptions::CREATOR];
            if ('current-user' === strtolower($creator)) {
                $equalFilter['createdBy.id'] = $this->getUser()->getId();
            } else {
                $inFilter['createdBy.id'] = explode(",", $creator);
            }
            $filterForUrl['createdBy'] = '&creator=' . $creator;
        }
        if (isset($filterDataArray[FilterAttributeOptions::REQUESTER])) {
            $requester = $filterDataArray[FilterAttributeOptions::REQUESTER];
            if ('current-user' === strtolower($requester)) {
                $equalFilter['requestedBy.id'] = $this->getUser()->getId();
            } else {
                $inFilter['requestedBy.id'] = explode(",", $requester);
            }
            $filterForUrl['requestedBy'] = '&requester=' . $requester;
        }
        if (isset($filterDataArray[FilterAttributeOptions::COMPANY])) {
            $company = $filterDataArray[FilterAttributeOptions::COMPANY];
            if ('current-user' === strtolower($company)) {
                $equalFilter['company.id'] = $this->getUser()->getId();
            } else {
                $inFilter['company.id'] = explode(",", $company);
            }
            $filterForUrl['company'] = '&company=' . $company;
        }
        if (isset($filterDataArray[FilterAttributeOptions::ASSIGNED])) {
            $assigned = $filterDataArray[FilterAttributeOptions::ASSIGNED];
            $assignedArray = explode(",", $assigned);

            if (in_array('not', $assignedArray) && in_array('current-user', $assignedArray)) {
                $notAndCurrentFilter[] = [
                    'not' => 'thau.user',
                    'equal' => [
                        'key' => 'assignedUser.id',
                        'value' => $this->getUser()->getId()
                    ],
                ];
            } elseif ('not' === strtolower($assigned)) {
                $isNullFilter[] = 'thau.user';
            } elseif ('current-user' === strtolower($assigned)) {
                $equalFilter['assignedUser.id'] = $this->getUser()->getId();
            } else {
                $inFilter['assignedUser.id'] = explode(",", $assigned);
            }

            $filterForUrl['assigned'] = '&assigned=' . $assigned;
        }
        if (isset($filterDataArray[FilterAttributeOptions::TAG])) {
            $tag = $filterDataArray[FilterAttributeOptions::TAG];
            $inFilter['tags.id'] = explode(",", $tag);
            $filterForUrl['tag'] = '&tag=' . $tag;
        }
        if (isset($filterDataArray[FilterAttributeOptions::FOLLOWER])) {
            $follower = $filterDataArray[FilterAttributeOptions::FOLLOWER];
            if ('current-user' === $follower) {
                $equalFilter['followers.id'] = $this->getUser()->getId();
            } else {
                $inFilter['followers.id'] = explode(",", $follower);
            }
            $filterForUrl['followers'] = '&follower=' . $follower;
        }
        if (isset($filterDataArray[FilterAttributeOptions::CREATED])) {
            $created = $filterDataArray[FilterAttributeOptions::CREATED];
            $fromToData = $this->separateFromToDateData($created);
            $dateFilter['task.createdAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to']
            ];
            $filterForUrl['created'] = '&createdTime=' . $created;
        }
        if (isset($filterDataArray[FilterAttributeOptions::STARTED])) {
            $started = $filterDataArray[FilterAttributeOptions::STARTED];
            $fromToData = $this->separateFromToDateData($started);
            $dateFilter['task.startedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to']
            ];
            $filterForUrl['started'] = '&startedTime=' . $started;
        }
        if (isset($filterDataArray[FilterAttributeOptions::DEADLINE])) {
            $deadline = $filterDataArray[FilterAttributeOptions::DEADLINE];
            $fromToData = $this->separateFromToDateData($deadline);
            $dateFilter['task.deadline'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to']
            ];
            $filterForUrl['deadline'] = '&deadlineTime=' . $deadline;
        }
        if (isset($filterDataArray[FilterAttributeOptions::CLOSED])) {
            $closed = $filterDataArray[FilterAttributeOptions::CLOSED];
            $fromToData = $this->separateFromToDateData($closed);
            $dateFilter['task.closedAt'] = [
                'from' => $fromToData['from'],
                'to' => $fromToData['to']
            ];
            $filterForUrl['closed'] = '&closedTime=' . $closed;
        }
        if (isset($filterDataArray[FilterAttributeOptions::ARCHIVED])) {
            if ('true' === strtolower($filterDataArray[FilterAttributeOptions::ARCHIVED])) {
                $equalFilter['project.is_active'] = 0;
                $filterForUrl['archived'] = '&archived=TRUE';
            }
        }
        if (isset($filterDataArray[FilterAttributeOptions::IMPORTANT])) {
            if ('true' === strtolower($filterDataArray[FilterAttributeOptions::IMPORTANT])) {
                $equalFilter['task.important'] = 1;
                $filterForUrl['important'] = '&important=TRUE';
            }
        }
        if (isset($filterDataArray[FilterAttributeOptions::ADDED_PARAMETERS])) {
            $addedParameters = $filterDataArray[FilterAttributeOptions::ADDED_PARAMETERS];
            $arrayOfAddedParameters = explode("&", $addedParameters);

            if (!empty($arrayOfAddedParameters[0])) {
                $filterForUrl['addedParameters'] = '&addedParameters=' . $addedParameters;

                foreach ($arrayOfAddedParameters as $value) {
                    $strpos = explode('=', $value);
                    $attributeId = $strpos[0];

                    // Check if TaskAttribute exists, select filter type based on it's TYPE
                    $taskAttribute = $this->getDoctrine()->getRepository('APITaskBundle:TaskAttribute')->find($attributeId);
                    if ($taskAttribute instanceof TaskAttribute) {
                        $typeOfTaskAttribute = $taskAttribute->getType();
                        $attributeValues = explode(",", $strpos[1]);

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
            'filterForUrl' => $filterForUrl
        ];
    }

    /**
     * @param string $created
     * @return array
     */
    private function separateFromToDateData(string $created):array
    {
        $fromPosition = strpos($created, "FROM=");
        $toPosition = strpos($created, "TO=");

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
            'to' => $toData
        ];
    }
}
