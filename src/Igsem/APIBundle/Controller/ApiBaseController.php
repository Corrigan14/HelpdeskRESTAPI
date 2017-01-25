<?php
/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 11/1/16
 * Time: 10:22 AM
 */

namespace Igsem\APIBundle\Controller;


use Igsem\APIBundle\Services\StatusCodesHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiBaseController
 *
 * @package API\CoreBundle\Controller
 */
abstract class ApiBaseController extends Controller
{
    /**
     * @return Response 401
     * @throws \InvalidArgumentException
     */
    protected function unauthorizedResponse()
    {
        return $this->createApiResponse([
            'message' => StatusCodesHelper::UNAUTHORIZED_MESSAGE ,
        ] , StatusCodesHelper::UNAUTHORIZED_CODE);
    }

    /**
     * @return Response 404
     * @throws \InvalidArgumentException
     */
    protected function notFoundResponse()
    {
        return $this->createApiResponse([
            'message' => StatusCodesHelper::NOT_FOUND_MESSAGE ,
        ] , StatusCodesHelper::NOT_FOUND_CODE);
    }

    /**
     * @return Response 409
     * @throws \InvalidArgumentException
     */
    protected function invalidParametersResponse()
    {
        return $this->createApiResponse([
            'message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE ,
        ] , StatusCodesHelper::INVALID_PARAMETERS_CODE);
    }

    /**
     * @return Response 403
     * @throws \InvalidArgumentException
     */
    protected function accessDeniedResponse()
    {
        return $this->createApiResponse([
            'message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE ,
        ] , StatusCodesHelper::ACCESS_DENIED_CODE);
    }

    /**
     * @param     $data
     * @param int $statusCode
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    protected function createApiResponse($data , $statusCode = 200)
    {
        $json = $this->serialize($data);

        return new Response($json , $statusCode , [
            'Content-Type' => 'application/json' ,
        ]);
    }

    /**
     * @param        $data
     * @param string $format
     *
     * @return mixed
     */
    protected function serialize($data , $format = 'json')
    {
        return $this->get('jms_serializer')
                    ->serialize($data , $format);
    }

    /**
     * Return's 201 code if create = true, else 200
     *
     * @param bool $create
     *
     * @return int
     */
    protected function getCreateUpdateStatusCode($create)
    {
        if ($create) {
            return StatusCodesHelper::CREATED_CODE;
        } else {
            return StatusCodesHelper::SUCCESSFUL_CODE;
        }
    }
}