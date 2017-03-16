<?php

namespace API\TaskBundle\Controller;

use API\TaskBundle\Entity\Smtp;
use API\TaskBundle\Security\UserRoleAclOptions;
use Igsem\APIBundle\Controller\ApiBaseController;
use Igsem\APIBundle\Controller\ControllerInterface;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SmtpController
 *
 * @package API\TaskBundle\Controller
 */
class SmtpController extends ApiBaseController implements ControllerInterface
{
    /**
     *  ### Response ###
     *     {
     *       "data":
     *       [
     *         {
     *            "id": 1,
     *            "host": "Host",
     *            "port": 3306,
     *            "email": "mb@web-solutions.sk",
     *            "name": "test",
     *            "password": "test",
     *            "ssl": true,
     *            "tls": false
     *          }
     *        ],
     *        "_links": [],
     *        "total": 1
     *     }
     *
     *
     * @ApiDoc(
     *  description="Returns a list of SMTP Entities",
     *  filters={
     *     {
     *       "name"="order",
     *       "description"="ASC or DESC order by Email"
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $orderString = $request->get('order');
        $orderString = strtolower($orderString);
        $order = ($orderString === 'asc' || $orderString === 'desc') ? $orderString : 'ASC';

        $smtpArray = $this->get('smtp_service')->getAttributesResponse($order);
        return $this->json($smtpArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *          "data":
     *          {
     *             "id": 1,
     *             "host": "Host",
     *             "port": 3306,
     *             "email": "mb@web-solutions.sk",
     *             "name": "test",
     *             "password": "test",
     *             "ssl": true,
     *             "tls": false
     *          },
     *          "_links":
     *          {
     *             "put": "/api/v1/task-bundle/smtp/1",
     *             "patch": "/api/v1/task-bundle/smtp/1",
     *             "delete": "/api/v1/task-bundle/smtp/1"
     *          }
     *      }
     *
     * @ApiDoc(
     *  description="Returns a SMTP Entity",
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
     *  output="API\TaskBundle\Entity\Smtp",
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
     * @throws \InvalidArgumentException
     */
    public function getAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $smtpEntity = $this->getDoctrine()->getRepository('APITaskBundle:Smtp')->find($id);

        if (!$smtpEntity instanceof Smtp) {
            return $this->notFoundResponse();
        }

        $smtpArray = $this->get('smtp_service')->getAttributeResponse($id);
        return $this->json($smtpArray, StatusCodesHelper::SUCCESSFUL_CODE);
    }

    /**
     *  ### Response ###
     *      {
     *          "data":
     *          {
     *             "id": 1,
     *             "host": "Host",
     *             "port": 3306,
     *             "email": "mb@web-solutions.sk",
     *             "name": "test",
     *             "password": "test",
     *             "ssl": true,
     *             "tls": false
     *          },
     *          "_links":
     *          {
     *             "put": "/api/v1/task-bundle/smtp/1",
     *             "patch": "/api/v1/task-bundle/smtp/1",
     *             "delete": "/api/v1/task-bundle/smtp/1"
     *          }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new SMTP Entity",
     *  input={"class"="API\TaskBundle\Entity\Smtp"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Smtp"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters",
     *  }
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $smtp = new Smtp();
        $requestData = $request->request->all();

        return $this->updateSmtpEntity($smtp, $requestData, true);
    }

    /**
     * ### Response ###
     *      {
     *          "data":
     *          {### Response ###
     *      {
     *        "data":
     *        {
     *           "id": "2",
     *        },
     *        "_links":
     *        {
     *           "put": "/api/v1/entityName/2",
     *           "patch": "/api/v1/entityName/2",
     *           "delete": "/api/v1/entityName/2"
     *         }
     *      }
     *             "id": 1,
     *             "host": "Host",
     *             "port": 3306,
     *             "email": "mb@web-solutions.sk",
     *             "name": "test",
     *             "password": "test",
     *             "ssl": true,
     *             "tls": false
     *          },
     *          "_links":
     *          {
     *             "put": "/api/v1/task-bundle/smtp/1",
     *             "patch": "/api/v1/task-bundle/smtp/1",
     *             "delete": "/api/v1/task-bundle/smtp/1"
     *          }
     *      }
     *
     * @ApiDoc(
     *  description="Update the SMTP Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Smtp"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Smtp"},
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
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $smtp = $this->getDoctrine()->getRepository('APITaskBundle:Smtp')->find($id);
        if (!$smtp instanceof Smtp) {
            return $this->notFoundResponse();
        }

        $requestData = $request->request->all();

        return $this->updateSmtpEntity($smtp, $requestData, false);
    }

    /**
     *  ### Response ###
     *      {
     *          "data":
     *          {
     *             "id": 1,
     *             "host": "Host",
     *             "port": 3306,
     *             "email": "mb@web-solutions.sk",
     *             "name": "test",
     *             "password": "test",
     *             "ssl": true,
     *             "tls": false
     *          },
     *          "_links":
     *          {
     *             "put": "/api/v1/task-bundle/smtp/1",
     *             "patch": "/api/v1/task-bundle/smtp/1",
     *             "delete": "/api/v1/task-bundle/smtp/1"
     *          }
     *      }
     *
     * @ApiDoc(
     *  description="Partially update the SMTP Entity",
     *  requirements={
     *     {
     *       "name"="id",
     *       "dataType"="integer",
     *       "requirement"="\d+",
     *       "description"="The id of processed object"
     *     }
     *  },
     *  input={"class"="API\TaskBundle\Entity\Smtp"},
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\TaskBundle\Entity\Smtp"},
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function updatePartialAction(int $id, Request $request)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $smtp = $this->getDoctrine()->getRepository('APITaskBundle:Smtp')->find($id);
        if (!$smtp instanceof Smtp) {
            return $this->notFoundResponse();
        }

        $requestData = json_decode($request->getContent(), true);
        return $this->updateSmtpEntity($smtp, $requestData, false);
    }

    /**
     * @ApiDoc(
     *  description="Delete SMTP Entity",
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
     */
    public function deleteAction(int $id)
    {
        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            return $this->accessDeniedResponse();
        }

        $smtp = $this->getDoctrine()->getRepository('APITaskBundle:Smtp')->find($id);

        if (!$smtp instanceof Smtp) {
            return $this->notFoundResponse();
        }

        $this->getDoctrine()->getManager()->remove($smtp);
        $this->getDoctrine()->getManager()->flush();

        return $this->createApiResponse([
            'message' => StatusCodesHelper::DELETED_MESSAGE,
        ], StatusCodesHelper::DELETED_CODE);
    }


    public function testSMTPConnectionAction()
    {

    }

    /**
     * @param Smtp $smtp
     * @param array $requestData
     * @param bool $create
     * @return Response|JsonResponse
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function updateSmtpEntity(Smtp $smtp, array $requestData, $create = false)
    {
        $allowedEntityParams = [
            'host',
            'port',
            'email',
            'name',
            'password',
            'ssl',
            'tls'
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

        // Check if values for ssl and tls are sent. If not, default value = false
        if ($create) {
            if (!array_key_exists('ssl', $requestData)) {
                $smtp->setSsl(false);
            }
            if (!array_key_exists('tls', $requestData)) {
                $smtp->setTls(false);
            }
        }

        $statusCode = $this->getCreateUpdateStatusCode($create);

        $errors = $this->get('entity_processor')->processEntity($smtp, $requestData);

        if (false === $errors) {
            $this->getDoctrine()->getManager()->persist($smtp);
            $this->getDoctrine()->getManager()->flush();

            $tagArray = $this->get('smtp_service')->getAttributeResponse($smtp->getId());
            return $this->json($tagArray, $statusCode);
        }

        $data = [
            'errors' => $errors,
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE
        ];
        return $this->createApiResponse($data, StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }
}
