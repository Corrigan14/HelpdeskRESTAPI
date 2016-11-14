<?php
/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 11/1/16
 * Time: 10:22 AM
 */

namespace API\CoreBundle\Controller;


use API\CoreBundle\Services\StatusCodesHelper;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiBaseController
 *
 * @package API\CoreBundle\Controller
 */
abstract class ApiBaseController extends Controller
{
    /**
     * @return JsonResponse
     */
    protected function unauthorizedResponse()
    {
        return $this->createApiResponse([
            'message' => StatusCodesHelper::UNAUTHORIZED_MESSAGE ,
        ] , StatusCodesHelper::UNAUTHORIZED_CODE);
    }

    /**
     * @return JsonResponse
     */
    protected function accessDeniedResponse()
    {
        return $this->createApiResponse([
            'message' => StatusCodesHelper::ACCESS_DENIED_MESSAGE,
        ] , StatusCodesHelper::ACCESS_DENIED_CODE);
    }

    /**
     * @param $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function createApiResponse($data , $statusCode = 200)
    {
        $json = $this->serialize($data);

        return new Response($json , $statusCode , [
            'Content-Type' => 'application/json' ,
        ]);
    }

    /**
     * @param $data
     * @param string $format
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
     * @return int
     */
    protected function getCreateUpdateStatusCode($create)
    {
        if($create){
            return StatusCodesHelper::CREATED_CODE;
        }else{
            return StatusCodesHelper::SUCCESSFUL_CODE;
        }
    }
}