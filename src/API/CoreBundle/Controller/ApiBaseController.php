<?php
/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 11/1/16
 * Time: 10:22 AM
 */

namespace API\CoreBundle\Controller;


use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiBaseController
 *
 * @package API\CoreBundle\Controller
 */
abstract class ApiBaseController extends Controller
{
    protected function serialize($data , $format = 'json')
    {
        return $this->get('jms_serializer')
                    ->serialize($data , $format);
    }

    protected function createApiResponse($data , $statusCode = 200)
    {
        $json = $this->serialize($data);

        return new Response($json , $statusCode , [
            'Content-Type' => 'application/json' ,
        ]);
    }
}