<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\Imap;
use API\TaskBundle\Entity\Project;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ImapController
 *
 * @package API\TaskBundle\Controller
 */
class ImapController extends ApiBaseController
{
    /**
     *  ### Response ###
     *     {
     *        "data":
     *        [
     *          {
     *             "id": 1,
     *             "host": "test",
     *             "port": 3306,
     *             "name": "test",
     *             "password": "test",
     *             "ssl": true,
     *             "inbox_email": "test@test.sk",
     *             "move_email": "test@test.sk",
     *             "ignore_certificate": false,
     *             "project":
     *             {
     *                "id": 258,
     *                "title": "Project of user 1",
     *                "description": "Description of project 1.",
     *                "is_active": false,
     *                "createdAt":
     *                {
     *                   "date": "2017-02-20 09:18:42.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                },
     *                "updatedAt":
     *                {
     *                   "date": "2017-02-20 09:18:42.000000",
     *                   "timezone_type": 3,
     *                   "timezone": "Europe/Berlin"
     *                }
     *             }
     *          }
     *        ],
     *        "_links": [],
     *        "total": 1
     *     }
     *
     * @ApiDoc(
     *  description="Returns a list of IMAP Entities",
     *  filters={
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Inbox email titile"
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
     */
    public function listAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $order = $request->get('order') ?: 'ASC';

        $imapArray = $this->get('imap_service')->getAttributesResponse($order);
        return $this->json($imapArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 1,
     *           "host": "test",
     *           "port": 3306,
     *           "name": "test",
     *           "password": "test",
     *           "ssl": true,
     *           "inbox_email": "test@test.sk",
     *           "move_email": "test@test.sk",
     *           "ignore_certificate": false,
     *           "project":
     *           {
     *              "id": 258,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-02-20 09:18:42.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                 "date": "2017-02-20 09:18:42.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *               }
     *            }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/imap/1",
     *           "patch": "/api/v1/task-bundle/imap/1",
     *           "delete": "/api/v1/task-bundle/imap/1"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Returns an IMAP Entity",
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
     *  output="API\TaskBundle\Entity\Imap",
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
     */
    public function getAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $imap = $this->getDoctrine()->getRepository('APITaskBundle:Imap')->find($id);
        if (!$imap instanceof Imap) {
            return $this->createApiResponse([
                'message' => 'Imap with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $imapArray = $this->get('imap_service')->getAttributeResponse($id);
        return $this->json($imapArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 1,
     *           "host": "test",
     *           "port": 3306,
     *           "name": "test",
     *           "password": "test",
     *           "ssl": true,
     *           "inbox_email": "test@test.sk",
     *           "move_email": "test@test.sk",
     *           "ignore_certificate": false,
     *           "project":
     *           {
     *              "id": 258,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-02-20 09:18:42.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                 "date": "2017-02-20 09:18:42.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *               }
     *            }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/imap/1",
     *           "patch": "/api/v1/task-bundle/imap/1",
     *           "delete": "/api/v1/task-bundle/imap/1"
     *         }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new IMAP Entity",
     *  requirements={
     *     {
     *       "name"="projectId",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of project"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Imap"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Imap"},
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
     * @return Response
     */
    public function createAction(Request $request, int $projectId)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
        if (!$project instanceof Project) {
            return $this->createApiResponse([
                'message' => 'Project with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $requestData = $request->request->all();

        $imap = new Imap();
        $imap->setProject($project);

        return $this->updateImapEntity($imap, $requestData, true);
    }

    /**
     * ### Response ###
     *      {
     *        "data":
     *        {
     *           "id": 1,
     *           "host": "test",
     *           "port": 3306,
     *           "name": "test",
     *           "password": "test",
     *           "ssl": true,
     *           "inbox_email": "test@test.sk",
     *           "move_email": "test@test.sk",
     *           "ignore_certificate": false,
     *           "project":
     *           {
     *              "id": 258,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-02-20 09:18:42.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                 "date": "2017-02-20 09:18:42.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *               }
     *            }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/imap/1",
     *           "patch": "/api/v1/task-bundle/imap/1",
     *           "delete": "/api/v1/task-bundle/imap/1"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Update the IMAP Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Imap"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Imap"},
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
     * @param int|bool $projectId
     * @return Response
     */
    public function updateAction(int $id, Request $request, $projectId = false)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $imap = $this->getDoctrine()->getRepository('APITaskBundle:Imap')->find($id);
        if (!$imap instanceof Imap) {
            return $this->createApiResponse([
                'message' => 'Imap with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if ($projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $imap->setProject($project);
        }

        $requestData = $request->request->all();
        return $this->updateImapEntity($imap, $requestData, false);
    }

    /**
     * ### Response ###
     *     {
     *        "data":
     *        {
     *           "id": 1,
     *           "host": "test",
     *           "port": 3306,
     *           "name": "test",
     *           "password": "test",
     *           "ssl": true,
     *           "inbox_email": "test@test.sk",
     *           "move_email": "test@test.sk",
     *           "ignore_certificate": false,
     *           "project":
     *           {
     *              "id": 258,
     *              "title": "Project of user 1",
     *              "description": "Description of project 1.",
     *              "is_active": false,
     *              "createdAt":
     *              {
     *                 "date": "2017-02-20 09:18:42.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *              },
     *              "updatedAt":
     *              {
     *                 "date": "2017-02-20 09:18:42.000000",
     *                 "timezone_type": 3,
     *                 "timezone": "Europe/Berlin"
     *               }
     *            }
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/task-bundle/imap/1",
     *           "patch": "/api/v1/task-bundle/imap/1",
     *           "delete": "/api/v1/task-bundle/imap/1"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the IMAP Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Imap"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Imap"},
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
     * @param int|bool $projectId
     * @return Response
     */
    public function updatePartialAction(int $id, Request $request, $projectId = false)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $imap = $this->getDoctrine()->getRepository('APITaskBundle:Imap')->find($id);
        if (!$imap instanceof Imap) {
            return $this->createApiResponse([
                'message' => 'Imap with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        if ($projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
            if (!$project instanceof Project) {
                return $this->createApiResponse([
                    'message' => 'Project with requested Id does not exist!',
                ], StatusCodesHelper::NOT_FOUND_CODE);
            }
            $imap->setProject($project);
        }

        $requestData = $request->request->all();
        return $this->updateImapEntity($imap, $requestData, false);
    }

    /**
     * @ApiDoc(
     *  description="Delete IMAP Entity",
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
     * @return Response
     */
    public function deleteAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $imap = $this->getDoctrine()->getRepository('APITaskBundle:Imap')->find($id);
        if (!$imap instanceof Imap) {
            return $this->createApiResponse([
                'message' => 'Imap with requested Id does not exist!',
            ], StatusCodesHelper::NOT_FOUND_CODE);
        }

        $this->getDoctrine()->getManager()->remove($imap);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
    }

    /**
     * @param Imap $imap
     * @param array $requestData
     * @param bool $create
     * @return Response|JsonResponse
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function updateImapEntity(Imap $imap, array $requestData, $create = false)
    {
        $allowedEntityParams = [
            'host',
            'port',
            'name',
            'password',
            'inbox_email',
            'move_email',
            'ignore_certificate',
            'ssl',
        ];

        if (array_key_exists('_format', $requestData)) {
            unset($requestData['_format']);
        }

        foreach ($requestData as $key => $value) {
            if (!in_array($key, $allowedEntityParams, true)) {
                return $this->createApiResponse(
                    ['message' => $key . ' is not allowed parameter for Tag Entity!'],
                    StatusCodesHelper::INVALID_PARAMETERS_CODE
                );
            }
        }

        // Check if values for ssl and ignore_certificate are sent. If not, default value = false
        if ($create) {
            if (!array_key_exists('ssl', $requestData)) {
                $imap->setSsl(false);
            }
            if (!array_key_exists('ignore_certificate', $requestData)) {
                $imap->setIgnoreCertificate(false);
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($imap, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($imap);
            $this->getDoctrine()->getManager()->flush();

            $imapArray = $this->get('imap_service')->getAttributeResponse($imap->getId());
            return $this->json($imapArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
