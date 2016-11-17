<?php

namespace API\CoreBundle\Controller;

use Igsem\APIBundle\Controller\ApiBaseController;
use API\CoreBundle\Services\CDN\UploadedFile;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use API\CoreBundle\Entity\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class CdnController
 *
 * @package API\CoreBundle\Controller
 */
class CdnController extends ApiBaseController
{
    /**
     * @Route("/upload")
     * @param Request $request
     *
     * @return Response
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \InvalidArgumentException
     * @ApiDoc(
     *  resource = true,
     *  description="Create a new Entity (POST)",
     *  input={"class"="API\CoreBundle\Entity\...entityName"},
     *     parameters={
     *     {
     *       "name"="file",
     *       "dataType"="file",
     *       "required"="true",
     *       "description"="File to upload"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\...entityName"},
     *  statusCodes={
     *      201 ="The entity was successfully created",
     *      401 ="Unauthorized request",
     *      409 ="Invalid parameters",
     *  }
     * )
     */
    public function uploadAction(Request $request)
    {

        $file = $this->getUploadedFile('file');

        if (false === $file) {
            return $this->createApiResponse(['message' => StatusCodesHelper::INVALID_PARAMETERS_MESSAGE ,] , StatusCodesHelper::INVALID_PARAMETERS_CODE);
        }

        $target = $this->getFolderDir();
        $file->setUploadDir($target['dir']);
        $fileEntity = new File();
        $file->save($fileEntity);
        $file->move($target['path']);

        if ($request->get('public')) {
            $fileEntity->setPublic(true);
        }

        $this->getDoctrine()->getManager()->persist($fileEntity);
        $this->getDoctrine()->getManager()->flush();
        $slug = $fileEntity->getSlug();


        return $this->createApiResponse(['slug' => $slug , 'message' => StatusCodesHelper::CREATED_MESSAGE ,] , StatusCodesHelper::CREATED_CODE);
    }


    /**
     * @Route("/load/{slug}", name="cdn_load_file")
     *
     * @param string $slug
     *
     * @return Response
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     *
     * @ApiDoc(
     *  description="Returns File",
     *  requirements={
     *     {
     *       "name"="slug",
     *       "dataType"="string",
     *       "description"="Slug of file"
     *     }
     *  },
     *  headers={
     *     {
     *       "name"="Authorization",
     *       "required"=true,
     *       "description"="Bearer {JWT Token}"
     *     }
     *  },
     *  output={"class"="API\CoreBundle\Entity\File"},
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      404 ="Not found file"
     *  },
     *  )
     *
     */
    public function loadAction($slug)
    {
        $fileEntity = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug ,
        ]);

        if (null === $fileEntity) {
            return $this->createApiResponse(['message' => StatusCodesHelper::RESOURCE_NOT_FOUND_MESSAGE ,] , StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
        }
        $uploadDir = $this->container->getParameter('upload_dir');

        $file = $uploadDir . DIRECTORY_SEPARATOR . $fileEntity->getUploadDir() . DIRECTORY_SEPARATOR . $fileEntity->getTempName();

        if (!file_exists($file)) {
            return $this->createApiResponse(['message' => StatusCodesHelper::RESOURCE_NOT_FOUND_MESSAGE ,] , StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
        }

        //TODO check user privileges - cache
        // Generate response
        $response = new Response();

        if ($fileEntity->isPublic()) {
            $response->setPublic();
            $response->setSharedMaxAge(3600);
        } else {
            $response->setPrivate();
        }


//        $etag = md5(rand(0, 500));
//        $response->setEtag($etag);
//        $expires=time()+(60*60*24*365);
//        $response->headers->set("Expires",gmdate("D, d M Y H:i:s", $expires)." GMT");

        // Set headers
        $response->headers->set('Content-type' , mime_content_type($file));//$fileEntity->getType());
        $response->headers->set('Content-length' , filesize($file));//$fileEntity->getSize());
        $response->headers->set('Access-Control-Allow-Origin' , '*');
        $response->headers->set('Last-Modified' , gmdate('D, d M Y H:i:s' , filemtime($file)) . ' GMT');

//        $response->setTtl(25);
//        $response->setClientTtl(25);
//        $response->setMaxAge(15);
//        $response->setSharedMaxAge(15);

        // Send headers before outputting anything
        $response->sendHeaders();

//        $response->setContent('test '.time());
        $response->setContent(file_get_contents($file));

        return $response;
    }


    /**
     * @param string $postName
     *
     * @return UploadedFile|bool
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    private function getUploadedFile($postName = 'upload')
    {
        if (!empty($_FILES[$postName])) {
            $postFile = $_FILES[$postName];

            return new UploadedFile($postFile['tmp_name'] , $postFile['name'] , $postFile['type'] , $postFile['size'] , $postFile['error']);
        }

        return false;
    }

    /**
     * Get a relatively unique folder name
     *
     * @param bool $onlyName
     *
     * @return string|array folder name or path and directory name ('path'=> '', 'dir'=>'')
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    private function getFolderDir($onlyName = false)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' . time();

        $unique = md5(str_shuffle($characters));
        if ($onlyName) {
            return $unique;
        }
        $uploadDir = $this->container->getParameter('upload_dir');
        $uploadDir = $uploadDir . DIRECTORY_SEPARATOR . $unique;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir , 0777 , true);
        }

        return [
            'path' => $uploadDir ,
            'dir'  => $unique ,
        ];
    }
}
