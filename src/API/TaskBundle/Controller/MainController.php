<?php

namespace API\TaskBundle\Controller;

use Igsem\APIBundle\Controller\ApiBaseController;
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
     *               "id": 178,
     *               "title": "new"
     *            },
     *            {
     *               "id": 179,
     *               "title": "In Progress"
     *            },
     *         ],
     *         "projects":
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
     *         "tags":
     *         [
     *            {
     *               "id": 1014,
     *               "username": "admin"
     *            },
     *            {
     *               "id": 1015,
     *               "username": "manager"
     *            },
     *         ]
     *      }
     *
     * @ApiDoc(
     *  description="Returns a list of logged User's FILTERS, TAGS, PROJECTS",
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
     * @return array
     */
    public function getLeftNavigationParams()
    {
        return [];
    }
}
