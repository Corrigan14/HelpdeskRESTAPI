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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SmtpController
 *
 * @package API\TaskBundle\Controller
 */
class SmtpController extends ApiBaseController
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
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function listAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('smtp_list');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
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

            $smtpArray = $this->get('smtp_service')->getAttributesResponse($order);
            $response = $response->setContent(json_encode($smtpArray));
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
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getAction(int $id): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('smtp', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $smtpEntity = $this->getDoctrine()->getRepository('APITaskBundle:Smtp')->find($id);

        if (!$smtpEntity instanceof Smtp) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::NOT_FOUND_MESSAGE]));
            return $response;
        }

        $smtpArray = $this->get('smtp_service')->getAttributeResponse($id);
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($smtpArray));
        return $response;
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
     * @throws \UnexpectedValueException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('smtp_create');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);

        // Check if there exist Entity in DB. If no, create the new one. If yes, just edit it.
        $existedSmtp = $this->getDoctrine()->getRepository('APITaskBundle:Smtp')->findAll();
        if (\count($existedSmtp) > 0) {
            $iteration = 0;
            foreach ($existedSmtp as $es) {
                if ($iteration === 0) {
                    $smtp = $es;
                    $iteration++;
                } else {
                    $this->getDoctrine()->getManager()->remove($es);
                    $this->getDoctrine()->getManager()->flush();
                }
            }
        } else {
            $smtp = new Smtp();
        }

        return $this->updateSmtpEntity($smtp, $requestBody, true, $locationURL);
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
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \LogicException
     */
    public function updateAction(int $id, Request $request): Response
    {
        // JSON API Response - Content type and Location settings
        $locationURL = $this->generateUrl('smtp_update', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $smtp = $this->getDoctrine()->getRepository('APITaskBundle:Smtp')->find($id);
        if (!$smtp instanceof Smtp) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'SMTP with requested Id does not exist!']));
            return $response;
        }

        $requestBody = $this->get('api_base.service')->encodeRequest($request);
        return $this->updateSmtpEntity($smtp, $requestBody, false, $locationURL);
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
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function deleteAction(int $id): Response
    {
        $locationURL = $this->generateUrl('smtp_delete', ['id' => $id]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        $smtp = $this->getDoctrine()->getRepository('APITaskBundle:Smtp')->find($id);

        if (!$smtp instanceof Smtp) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'SMTP Entity with requested Id does not exist!']));
            return $response;
        }

        $this->getDoctrine()->getManager()->remove($smtp);
        $this->getDoctrine()->getManager()->flush();

        $response = $response->setStatusCode(StatusCodesHelper::DELETED_CODE);
        $response = $response->setContent(json_encode(['message' => StatusCodesHelper::DELETED_MESSAGE]));
        return $response;
    }

    /**
     * @ApiDoc(
     *  description="SMTP Connection Test",
     *  parameters={
     *      {"name"="emails", "dataType"="string", "required"=true, "description"="Coma separated Email addresses - on these Emails will be delivered testing message!"}
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  statusCodes={
     *      200 ="Email was successfully sent",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      409 ="Invalid parameters - email/s",
     *  })
     *
     *
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\Validator\Exception\MissingOptionsException
     * @throws \Symfony\Component\Validator\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function testSMTPConnectionAction(Request $request): Response
    {
        $locationURL = $this->generateUrl('smtp_test_connection');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $aclOptions = [
            'acl' => UserRoleAclOptions::SMTP_SETTINGS,
            'user' => $this->getUser()
        ];

        if (!$this->get('acl_helper')->roleHasACL($aclOptions)) {
            $response = $response->setStatusCode(StatusCodesHelper::ACCESS_DENIED_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE]));
            return $response;
        }

        // Load SMTP settings - there should be only one in the DB
        $smtpSettings = $this->getDoctrine()->getRepository('APITaskBundle:Smtp')->findOneBy([]);

        if ($smtpSettings instanceof Smtp) {
            $requestBody = $this->get('api_base.service')->encodeRequest($request);

            if (false !== $requestBody) {
                if (isset($requestBody['emails'])) {
                    $testEmails = $requestBody['emails'];

                    // Validate requested Email addresses
                    $notValidEmailAddresses = $this->validateEmailAddresses($testEmails);
                    if (\count($notValidEmailAddresses) > 0) {
                        $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                        $response = $response->setContent(json_encode(['message' => 'Not valid email address: ' . implode(";", $notValidEmailAddresses)]));
                        return $response;
                    }

                    // Prepare params
                    $templateParams = $this->getTemplateParams($smtpSettings->getEmail(), $smtpSettings->getHost(), $testEmails);

                    // Send emails
                    $sendingError = $this->get('email_service')->sendEmail($templateParams);
                    if (true !== $sendingError) {
                        $data = [
                            'errors' => $sendingError,
                            'message' => 'Error with sending email!'
                        ];
                        $response = $response->setStatusCode(StatusCodesHelper::PROBLEM_WITH_EMAIL_SENDING);
                        $response = $response->setContent(json_encode($data));
                        return $response;
                    }
                    $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Email/s was/were successfully sent!']));
                    return $response;
                } else {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'At least one Email address is required!']));
                }
            } else {
                $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
                $response = $response->setContent(json_encode(['message' => 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded']));
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'SMTP Settings are not correctly set in Database!']));
        }

        return $response;
    }

    /**
     * @param Smtp $smtp
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
    private function updateSmtpEntity(Smtp $smtp, array $requestData, $create = false, $locationUrl): Response
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

                $smtpArray = $this->get('smtp_service')->getAttributeResponse($smtp->getId());

                $response = $response->setStatusCode($statusCode);
                $response = $response->setContent(json_encode($smtpArray));
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

    /**
     * @param string $smtpEmail
     * @param string $smtpHost
     * @param array $emailAddresses
     * @return array
     */
    private function getTemplateParams(string $smtpEmail, string $smtpHost, array $emailAddresses): array
    {
        $todayDate = new \DateTime();
        $email = $smtpEmail;
        $host = $smtpHost;

        $templateParams = [
            'date' => $todayDate,
            'email' => $email,
            'host' => $host
        ];
        $params = [
            'subject' => 'LanHelpdesk - SMTP Test',
            'from' => $smtpEmail,
            'to' => $emailAddresses,
            'body' => $this->renderView('@APITask/Emails/testSMTP.html.twig', $templateParams)
        ];

        return $params;
    }

    /**
     * @param $testEmails
     * @return array
     * @throws \Symfony\Component\Validator\Exception\MissingOptionsException
     * @throws \Symfony\Component\Validator\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    private function validateEmailAddresses(&$testEmails): array
    {
        $notValidEmailAddresses = [];

        // Validate Requested Email/s
        if (!\is_array($testEmails)) {
            $testEmails = explode(',', $testEmails);
        }

        if (\count($testEmails) > 0) {
            $validator = $this->get('validator');
            $constraints = [
                new Email(),
                new NotBlank()
            ];

            foreach ($testEmails as $email) {
                $emailError = $validator->validate($email, $constraints);
                if (\count($emailError)) {
                    $notValidEmailAddresses[] = $email;
                }
            }
        }

        return $notValidEmailAddresses;
    }
}
