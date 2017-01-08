<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\Filter;
use API\TaskBundle\Security\VoteOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FilterController
 *
 * @package API\TaskBundle\Controller
 */
class FilterController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *          {
     *             "id": 2,
     *             "title": "Admins PRIVATE Filter where status=new, creator = admin, user, archived = true",
     *             "public": false,
     *             "filter": "a:4:{s:6:\"status\";i:49;s:7:\"creator\";i:39;i:0;i:38;s:8:\"archived\";b:1;}",
     *             "report": false,
     *             "is_active": true,
     *             "default": false,
     *             "createdBy":
     *             {
     *                "id": 38,
     *                "username": "admin",
     *                "password": "$2y$13$NGzQjENbAf8ooYzIxqMhyuXjXjOMX/mxyJk3.0aO3wDjo6i8E8//m",
     *                "email": "admin@admin.sk",
     *                "roles": "[\"ROLE_ADMIN\"]",
     *                "is_active": true,
     *                "image": null
     *             },
     *             "project": null
     *          },
     *          {
     *              "id": 1,
     *              "title": "Users PUBLIC Filter where status=new, creator = admin, user, archived = true",
     *              "public": true,
     *              "filter": "a:4:{s:6:\"status\";i:49;s:7:\"creator\";i:39;i:0;i:38;s:8:\"archived\";b:1;}",
     *              "report": false,
     *              "is_active": true,
     *              "default": false,
     *              "createdBy":
     *             {
     *                "id": 39,
     *                "username": "user",
     *                "password": "$2y$13$cRIMO.MJJp1DrsB89ru97.4q2NftRbXBCiKBPSfcb/bUKgXCtuJ1q",
     *                "email": "user@user.sk",
     *                "roles": "[\"ROLE_USER\"]",
     *                "is_active": true,
     *                "image": null
     *             },
     *             "project":
     *             {
     *                "id": 58,
     *                "title": "Project of admin",
     *                "description": "Description of project of admin.",
     *                "is_active": true,
     *                "createdAt":
     *                {
     *                    "date": "2017-01-03 17:40:43.000000",
     *                    "timezone_type": 3,
     *                    "timezone": "Europe/Berlin"
     *                },
     *                "updatedAt":
     *                {
     *                    "date": "2017-01-03 17:40:43.000000",
     *                    "timezone_type": 3,
     *                    "timezone": "Europe/Berlin"
     *                }
     *             }
     *          }
     *       ],
     *       "_links":
     *       {
     *           "self": "/api/v1/task-bundle/filters?page=1",
     *           "first": "/api/v1/task-bundle/filters?page=1",
     *           "prev": false,
     *           "next": "/api/v1/task-bundle/filters?page=2",
     *            "last": "/api/v1/task-bundle/filters?page=3"
     *       },
     *       "total": 22,
     *       "page": 1,
     *       "numberOfPages": 3
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of Logged user's Filters",
     *  filters={
     *     {
     *       "name"="page",
     *       "description"="Pagination, limit is set to 10 records"
     *     },
     *     {
     *       "name"="public",
     *       "description"="Return's only PUBLIC filters if this param is TRUE, only logged user's filter's without
     *       PUBLIC filters, if param is FALSE. Else returns both: PUBLIC filters and filters created by logged user."
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE filters if this param is TRUE, only INACTIVE filters if param is FALSE"
     *     },
     *     {
     *       "name"="report",
     *       "description"="Return's only REPORT filters if this param is TRUE, only FILTER filters if param is FALSE"
     *     },
     *     {
     *       "name"="project",
     *       "description"="A list of coma separated ID's of Project f.i. 1,2,3.
     *        Another options:
     *          NOT - just filters without projects are returned,
     *          CURRENT-USER - just filters for actually logged user's projects are returned."
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
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     */
    public function listAction(Request $request)
    {
        $page = $request->get('page') ?: 1;
        $isActive = $request->get('isActive');
        $public = $request->get('public');
        $report = $request->get('report');
        $project = $request->get('project');

        $filtersForUrl = [];
        if (null !== $public) {
            $filtersForUrl['public'] = '&public=' . $public;
        }
        if (null !== $isActive) {
            $filtersForUrl['isActive'] = '&isActive=' . $isActive;
        }
        if (null !== $report) {
            $filtersForUrl['isActive'] = '&report=' . $report;
        }
        if (null !== $project) {
            $filtersForUrl['project'] = '&project=' . $project;
        }

        $options = [
            'loggedUserId' => $this->getUser()->getId(),
            'isActive' => strtolower($isActive),
            'public' => strtolower($public),
            'report' => strtolower($report),
            'project' => strtolower($project),
            'filtersForUrl' => $filtersForUrl
        ];

        return $this->json($this->get('filter_service')->getFiltersResponse($page, $options), StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2,
     *           "title": "Admins PRIVATE Filter where status=new, creator = admin, user, archived = true",
     *           "public": false,
     *           "filter": "a:4:{s:6:\"status\";i:49;s:7:\"creator\";i:39;i:0;i:38;s:8:\"archived\";b:1;}",
     *           "report": false,
     *           "is_active": true,
     *           "default": false,
     *           "created_by":
     *           {
     *              "id": 38,
     *              "username": "admin",
     *              "email": "admin@admin.sk",
     *              "roles": "[\"ROLE_ADMIN\"]",
     *              "is_active": true,
     *              "company":
     *              {
     *                 "id": 25,
     *                 "title": "Web-Solutions",
     *                 "ico": "1102587",
     *                 "dic": "12587459644",
     *                 "street": "Cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true,
     *                 "company_data":
     *                 {
     *                    "0":
     *                    {
     *                       "id": 25,
     *                       "value": "10",
     *                       "company_attribute":
     *                       {
     *                          "id": 39,
     *                          "title": "integer number company additional attribute",
     *                          "type": "integer_number",
     *                          "is_active": true
     *                       }
     *                    },
     *                    "1":
     *                   {
     *                      "id": 26,
     *                      "value": "String DATA",
     *                      "company_attribute":
     *                      {
     *                         "id": 37,
     *                         "title": "input company additional attribute",
     *                         "type": "input",
     *                         "is_active": true
     *                      }
     *                   }
     *                 }
     *              }
     *           },
     *           "project":
     *           {
     *              "id": 58,
     *              "title": "Project of admin",
     *              "description": "Description of project of admin.",
     *              "is_active": true,
     *              "created_by":
     *              {
     *                  "id": 38,
     *                  "username": "user222",
     *                  "email": "user222@admin.sk",
     *                  "roles": "[\"ROLE_USER\"]",
     *                  "is_active": true,
     *                  "company": null
     *               }
     *            }
     *           "created_at": "2017-01-03T17:40:43+0100",
     *           "updated_at": "2017-01-03T17:40:43+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a Filter entity",
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
     *  output="API\TaskBundle\Entity\Filter",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     *
     * @param int $id
     * @return Response
     * @throws \LogicException
     */
    public function getAction(int $id)
    {
        $filter = $this->getDoctrine()->getRepository('APITaskBundle:Filter')->find($id);

        if (!$filter instanceof Filter) {
            return $this->notFoundResponse();
        }

        if (!$this->get('filter_voter')->isGranted(VoteOptions::SHOW_FILTER, $filter)) {
            return $this->accessDeniedResponse();
        }

        $filterArray = $this->get('filter_service')->getFilterResponse($filter);
        return $this->createApiResponse($filterArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2,
     *           "title": "Admins PRIVATE Filter where status=new, creator = admin, user, archived = true",
     *           "public": false,
     *           "filter": "a:4:{s:6:\"status\";i:49;s:7:\"creator\";i:39;i:0;i:38;s:8:\"archived\";b:1;}",
     *           "report": false,
     *           "is_active": true,
     *           "default": false,
     *           "created_by":
     *           {
     *              "id": 38,
     *              "username": "admin",
     *              "email": "admin@admin.sk",
     *              "roles": "[\"ROLE_ADMIN\"]",
     *              "is_active": true,
     *              "company":
     *              {
     *                 "id": 25,
     *                 "title": "Web-Solutions",
     *                 "ico": "1102587",
     *                 "dic": "12587459644",
     *                 "street": "Cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true,
     *                 "company_data":
     *                 {
     *                    "0":
     *                    {
     *                       "id": 25,
     *                       "value": "10",
     *                       "company_attribute":
     *                       {
     *                          "id": 39,
     *                          "title": "integer number company additional attribute",
     *                          "type": "integer_number",
     *                          "is_active": true
     *                       }
     *                    },
     *                    "1":
     *                   {
     *                      "id": 26,
     *                      "value": "String DATA",
     *                      "company_attribute":
     *                      {
     *                         "id": 37,
     *                         "title": "input company additional attribute",
     *                         "type": "input",
     *                         "is_active": true
     *                      }
     *                   }
     *                 }
     *              }
     *           },
     *           "project":
     *           {
     *              "id": 58,
     *              "title": "Project of admin",
     *              "description": "Description of project of admin.",
     *              "is_active": true,
     *              "created_by":
     *              {
     *                  "id": 38,
     *                  "username": "user222",
     *                  "email": "user222@admin.sk",
     *                  "roles": "[\"ROLE_USER\"]",
     *                  "is_active": true,
     *                  "company": null
     *               }
     *            }
     *           "created_at": "2017-01-03T17:40:43+0100",
     *           "updated_at": "2017-01-03T17:40:43+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Filter Entity",
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        // TODO: Implement createAction() method.
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2,
     *           "title": "Admins PRIVATE Filter where status=new, creator = admin, user, archived = true",
     *           "public": false,
     *           "filter": "a:4:{s:6:\"status\";i:49;s:7:\"creator\";i:39;i:0;i:38;s:8:\"archived\";b:1;}",
     *           "report": false,
     *           "is_active": true,
     *           "default": false,
     *           "created_by":
     *           {
     *              "id": 38,
     *              "username": "admin",
     *              "email": "admin@admin.sk",
     *              "roles": "[\"ROLE_ADMIN\"]",
     *              "is_active": true,
     *              "company":
     *              {
     *                 "id": 25,
     *                 "title": "Web-Solutions",
     *                 "ico": "1102587",
     *                 "dic": "12587459644",
     *                 "street": "Cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true,
     *                 "company_data":
     *                 {
     *                    "0":
     *                    {
     *                       "id": 25,
     *                       "value": "10",
     *                       "company_attribute":
     *                       {
     *                          "id": 39,
     *                          "title": "integer number company additional attribute",
     *                          "type": "integer_number",
     *                          "is_active": true
     *                       }
     *                    },
     *                    "1":
     *                   {
     *                      "id": 26,
     *                      "value": "String DATA",
     *                      "company_attribute":
     *                      {
     *                         "id": 37,
     *                         "title": "input company additional attribute",
     *                         "type": "input",
     *                         "is_active": true
     *                      }
     *                   }
     *                 }
     *              }
     *           },
     *           "project":
     *           {
     *              "id": 58,
     *              "title": "Project of admin",
     *              "description": "Description of project of admin.",
     *              "is_active": true,
     *              "created_by":
     *              {
     *                  "id": 38,
     *                  "username": "user222",
     *                  "email": "user222@admin.sk",
     *                  "roles": "[\"ROLE_USER\"]",
     *                  "is_active": true,
     *                  "company": null
     *               }
     *            }
     *           "created_at": "2017-01-03T17:40:43+0100",
     *           "updated_at": "2017-01-03T17:40:43+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Filter Entity for Project",
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @param int $projectId
     * @return JsonResponse
     */
    public function createProjectsFilterAction(Request $request, int $projectId)
    {
        // TODO: Implement createAction() method.
    }

    /**
     * ### Response ###
     *      {
     *       "data":
     *        {
     *           "id": 2,
     *           "title": "Admins PRIVATE Filter where status=new, creator = admin, user, archived = true",
     *           "public": false,
     *           "filter": "a:4:{s:6:\"status\";i:49;s:7:\"creator\";i:39;i:0;i:38;s:8:\"archived\";b:1;}",
     *           "report": false,
     *           "is_active": true,
     *           "default": false,
     *           "created_by":
     *           {
     *              "id": 38,
     *              "username": "admin",
     *              "email": "admin@admin.sk",
     *              "roles": "[\"ROLE_ADMIN\"]",
     *              "is_active": true,
     *              "company":
     *              {
     *                 "id": 25,
     *                 "title": "Web-Solutions",
     *                 "ico": "1102587",
     *                 "dic": "12587459644",
     *                 "street": "Cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true,
     *                 "company_data":
     *                 {
     *                    "0":
     *                    {
     *                       "id": 25,
     *                       "value": "10",
     *                       "company_attribute":
     *                       {
     *                          "id": 39,
     *                          "title": "integer number company additional attribute",
     *                          "type": "integer_number",
     *                          "is_active": true
     *                       }
     *                    },
     *                    "1":
     *                   {
     *                      "id": 26,
     *                      "value": "String DATA",
     *                      "company_attribute":
     *                      {
     *                         "id": 37,
     *                         "title": "input company additional attribute",
     *                         "type": "input",
     *                         "is_active": true
     *                      }
     *                   }
     *                 }
     *              }
     *           },
     *           "project":
     *           {
     *              "id": 58,
     *              "title": "Project of admin",
     *              "description": "Description of project of admin.",
     *              "is_active": true,
     *              "created_by":
     *              {
     *                  "id": 38,
     *                  "username": "user222",
     *                  "email": "user222@admin.sk",
     *                  "roles": "[\"ROLE_USER\"]",
     *                  "is_active": true,
     *                  "company": null
     *               }
     *            }
     *           "created_at": "2017-01-03T17:40:43+0100",
     *           "updated_at": "2017-01-03T17:40:43+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the Filter Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
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
     * @return JsonResponse
     */
    public function updateAction(int $id, Request $request)
    {
        // TODO: Implement updateAction() method.
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2,
     *           "title": "Admins PRIVATE Filter where status=new, creator = admin, user, archived = true",
     *           "public": false,
     *           "filter": "a:4:{s:6:\"status\";i:49;s:7:\"creator\";i:39;i:0;i:38;s:8:\"archived\";b:1;}",
     *           "report": false,
     *           "is_active": true,
     *           "default": false,
     *           "created_by":
     *           {
     *              "id": 38,
     *              "username": "admin",
     *              "email": "admin@admin.sk",
     *              "roles": "[\"ROLE_ADMIN\"]",
     *              "is_active": true,
     *              "company":
     *              {
     *                 "id": 25,
     *                 "title": "Web-Solutions",
     *                 "ico": "1102587",
     *                 "dic": "12587459644",
     *                 "street": "Cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true,
     *                 "company_data":
     *                 {
     *                    "0":
     *                    {
     *                       "id": 25,
     *                       "value": "10",
     *                       "company_attribute":
     *                       {
     *                          "id": 39,
     *                          "title": "integer number company additional attribute",
     *                          "type": "integer_number",
     *                          "is_active": true
     *                       }
     *                    },
     *                    "1":
     *                   {
     *                      "id": 26,
     *                      "value": "String DATA",
     *                      "company_attribute":
     *                      {
     *                         "id": 37,
     *                         "title": "input company additional attribute",
     *                         "type": "input",
     *                         "is_active": true
     *                      }
     *                   }
     *                 }
     *              }
     *           },
     *           "project":
     *           {
     *              "id": 58,
     *              "title": "Project of admin",
     *              "description": "Description of project of admin.",
     *              "is_active": true,
     *              "created_by":
     *              {
     *                  "id": 38,
     *                  "username": "user222",
     *                  "email": "user222@admin.sk",
     *                  "roles": "[\"ROLE_USER\"]",
     *                  "is_active": true,
     *                  "company": null
     *               }
     *            }
     *           "created_at": "2017-01-03T17:40:43+0100",
     *           "updated_at": "2017-01-03T17:40:43+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the projects Filter Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
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
     * @param int $projectId
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProjectFilterAction(int $id, int $projectId, Request $request)
    {
        // TODO: Implement updateAction() method.
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 2,
     *           "title": "Admins PRIVATE Filter where status=new, creator = admin, user, archived = true",
     *           "public": false,
     *           "filter": "a:4:{s:6:\"status\";i:49;s:7:\"creator\";i:39;i:0;i:38;s:8:\"archived\";b:1;}",
     *           "report": false,
     *           "is_active": true,
     *           "default": false,
     *           "created_by":
     *           {
     *              "id": 38,
     *              "username": "admin",
     *              "email": "admin@admin.sk",
     *              "roles": "[\"ROLE_ADMIN\"]",
     *              "is_active": true,
     *              "company":
     *              {
     *                 "id": 25,
     *                 "title": "Web-Solutions",
     *                 "ico": "1102587",
     *                 "dic": "12587459644",
     *                 "street": "Cesta 125",
     *                 "city": "Bratislava",
     *                 "zip": "021478",
     *                 "country": "Slovenska Republika",
     *                 "is_active": true,
     *                 "company_data":
     *                 {
     *                    "0":
     *                    {
     *                       "id": 25,
     *                       "value": "10",
     *                       "company_attribute":
     *                       {
     *                          "id": 39,
     *                          "title": "integer number company additional attribute",
     *                          "type": "integer_number",
     *                          "is_active": true
     *                       }
     *                    },
     *                    "1":
     *                   {
     *                      "id": 26,
     *                      "value": "String DATA",
     *                      "company_attribute":
     *                      {
     *                         "id": 37,
     *                         "title": "input company additional attribute",
     *                         "type": "input",
     *                         "is_active": true
     *                      }
     *                   }
     *                 }
     *              }
     *           },
     *           "project":
     *           {
     *              "id": 58,
     *              "title": "Project of admin",
     *              "description": "Description of project of admin.",
     *              "is_active": true,
     *              "created_by":
     *              {
     *                  "id": 38,
     *                  "username": "user222",
     *                  "email": "user222@admin.sk",
     *                  "roles": "[\"ROLE_USER\"]",
     *                  "is_active": true,
     *                  "company": null
     *               }
     *            }
     *           "created_at": "2017-01-03T17:40:43+0100",
     *           "updated_at": "2017-01-03T17:40:43+0100"
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/filters/2",
     *           "patch": "/api/v1/task-bundle/filters/2",
     *           "delete": "/api/v1/task-bundle/filters/2"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the Filter Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Filter"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Filter"},
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
     * @return JsonResponse
     */
    public function updatePartialAction(int $id, Request $request)
    {
        // TODO: Implement updatePartialAction() method.
    }

    /**
     * @ApiDoc(
     *  description="Delete Filter Entity",
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
     * @return JsonResponse
     */
    public function deleteAction(int $id)
    {
        // TODO: Implement deleteAction() method.
    }
}
