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
     *             "description": null,
     *             "is_active": false,
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
     *       "description"="ASC or DESC order by Inbox email title"
     *     },
     *     {
     *       "name"="isActive",
     *       "description"="Return's only ACTIVE IMAP Entities if this param is TRUE, only INACTIVE user roles if param is FALSE"
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('imap_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        if (false !== $requestBody) {
            $processedFilterParams = $this->get('api_base.service')->processFilterParams($requestBody);
            $order = $processedFilterParams['order'];
            $isActive = $processedFilterParams['isActive'];

            $options = [
                'isActive' => $isActive,
                'order' => $order
            ];

            $imapArray = $this->get('imap_service')->getAttributesResponse($options);
            $response = $response->setContent(json_encode($imapArray));
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }

        return $response;
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
     *           "description": null,
     *           "is_active": false,
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
     *           "inactivate": "/api/v1/task-bundle/imap/6/inactivate",
     *           "restore": "/api/v1/task-bundle/imap/6/restore",
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
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('imap', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $imap = $this->getDoctrine()->getRepository('APITaskBundle:Imap')->find($id);

        if (!$imap instanceof Imap) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::NOT_FOUND_MESSAGE]));
            return $response;
        }

        $imapArray = $this->get('imap_service')->getAttributeResponse($id);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($imapArray));
        return $response;
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
     *           "description": null,
     *           "is_active": false,
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
     *           "inactivate": "/api/v1/task-bundle/imap/6/inactivate",
     *           "restore": "/api/v1/task-bundle/imap/6/restore",
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
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request, int $projectId): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('imap_create', ['projectId' => $projectId]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
        if (!$project instanceof Project) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        $imap = new Imap();
        $imap->setIsActive(true);
        $imap->setProject($project);

        return $this->updateImapEntity($imap, $requestBody, true, $locationURL);
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
     *           "description": null,
     *           "is_active": false,
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
     *           "inactivate": "/api/v1/task-bundle/imap/6/inactivate",
     *           "restore": "/api/v1/task-bundle/imap/6/restore",
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
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function updateAction(int $id, Request $request, $projectId = false): Response
    {
        // JSON API Response - Content type and Location settings
        if ($projectId) {
            $locationURL = $this->generateUrl('imap_update_with_project', ['id' => $id, 'projectId' => $projectId]);
        } else {
            $locationURL = $this->generateUrl('imap_update', ['id' => $id]);
        }
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $imap = $this->getDoctrine()->getRepository('APITaskBundle:Imap')->find($id);
        if (!$imap instanceof Imap) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Imap with requested Id does not exist!']));
            return $response;
        }

        if ($projectId) {
            $project = $this->getDoctrine()->getRepository('APITaskBundle:Project')->find($projectId);
            if (!$project instanceof Project) {
                $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
                $response = $response->setContent(json_encode(['message' => 'Project with requested Id does not exist!']));
                return $response;
            }
            $imap->setProject($project);
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateImapEntity($imap, $requestBody, false, $locationURL);
    }

    /**
     * @ApiDoc(
     *  description="Inactivate IMAP Entity",
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
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function inactivateAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('imap_inactivate', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $imap = $this->getDoctrine()->getRepository('APITaskBundle:Imap')->find($id);
        if (!$imap instanceof Imap) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Imap with requested Id does not exist!']));
            return $response;
        }

        $imap->setIsActive(false);
        $this->getDoctrine()->getManager()->persist($imap);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode(['message' => 'is_active param of Entity was successfully changed to inactive: 0']));
        return $response;
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
     *           "description": null,
     *           "is_active": false,
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
     *           "inactivate": "/api/v1/task-bundle/imap/6/inactivate",
     *           "restore": "/api/v1/task-bundle/imap/6/restore",
     *           "delete": "/api/v1/task-bundle/imap/1"
     *         }
     *      }
     *
     * @ApiDoc(
     *  description="Restore IMAP Entity",
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
     *      200 ="is_active param of Entity was successfully changed to active: 1",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  })
     *
     * @param int $id
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function restoreAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('imap_restore', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $imap = $this->getDoctrine()->getRepository('APITaskBundle:Imap')->find($id);
        if (!$imap instanceof Imap) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'Imap with requested Id does not exist!']));
            return $response;
        }

        $imap->setIsActive(true);
        $this->getDoctrine()->getManager()->persist($imap);
        $this->getDoctrine()->getManager()->flush();

        $imapArray = $this->get('imap_service')->getAttributeResponse($id);

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($imapArray));
        return $response;
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
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(int $id): Response
    {
        $locationURL = $this->generateUrl('imap_delete', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::IMAP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $imap = $this->getDoctrine()->getRepository('APITaskBundle:Imap')->find($id);
        if (!$imap instanceof Imap) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'IMAP Entity with requested Id does not exist!']));
            return $response;
        }

        $this->getDoctrine()->getManager()->remove($imap);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::DELETED_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::DELETED_MESSAGE]));
        return $response;
    }

    /**
     * @param Imap $imap
     * @param array $requestData
     * @param bool $create
     * @param string $locationUrl
     *
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function updateImapEntity(Imap $imap, array $requestData, $create = false, $locationUrl): Response
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
            'description'
        ];

        // JSON API Response - Content type and Location settings
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationUrl);

        if (false !== $requestData) {
            if (array_key_exists('_format', $requestData)) {
                unset($requestData['_format']);
            }

            foreach ($requestData as $key => $value) {
                if (!\in_array($key, $allowedEntityParams, true)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => $key . ' is not allowed parameter for User Entity!']));
                    return $response;
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

                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($imapArray));
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
            $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
        }

        return $response;
    }
}
