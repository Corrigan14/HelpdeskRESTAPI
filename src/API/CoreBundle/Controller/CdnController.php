<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Security\UploadingFilesOptions;
use API\TaskBundle\Security\VoteOptions;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAttachment;
use Igsem\APIBundle\Controller\ApiBaseController;
use API\CoreBundle\Services\CDN\UploadedFile;
use Igsem\APIBundle\Services\StatusCodesHelper;
use API\CoreBundle\Entity\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class CdnController - File upload control
 *
 * @package API\CoreBundle\Controller
 */
class CdnController extends ApiBaseController
{
    /**
     *    ### Response ###
     *      {
     *        "data":
     *        {
     *           "slug": "slug-of-uploaded-entity",
     *           "name": "Uploaded file name"
     *        }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Upload a file. For data uploading multipart/form-data data format is required! ",
     *  parameters={
     *     {"name"="file", "dataType"="file", "required"=true, "format"="POST", "description"="File to upload"},
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
     *      201 ="The entity was successfully uploaded",
     *      400 = "Bad Request",
     *      401 ="Unauthorized request",
     *      409 ="Invalid parameters"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \InvalidArgumentException
     */
    public function uploadAction(Request $request): Response
    {
        $locationURL = $this->generateUrl('file_upload');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $contentType = $request->headers->get('Content-Type');
        $contentType = substr($contentType, 0, 19);

        if ('multipart/form-data' !== $contentType) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_MULTIPART_FORM_SUPPORT]));
            return $response;
        }

        $uploadingFile = $request->files->get('file');

        if (null === $uploadingFile) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'No file is chosen!']));
            return $response;
        }

        // Check, if the file was uploaded via HTTP POST
        if (!is_uploaded_file($uploadingFile)) {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Uploading Error! File was not uploaded via HTTP POST. File needs to have a filename.']));
            return $response;
        }

        // Check if the uploading file is in an allowed FORMAT
        $fileType = $uploadingFile->getMimeType();
        $supportedFileTypes = UploadingFilesOptions::$supportedFileTypes;

        if (\in_array($fileType, $supportedFileTypes, true)) {

            if (\in_array($fileType, UploadingFilesOptions::$supportedImageTypes, true)) {
                //Check the allowed image size
                $imageSize = getimagesize($uploadingFile);
                if ($imageSize && ($imageSize[0] > 10000 || $imageSize[1] > 10000)) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Uploading image is too large!']));
                    return $response;
                }

            } else {
                $fileSize = filesize($uploadingFile);
                if ($fileSize > 10000000) {
                    $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                    $response = $response->setContent(json_encode(['message' => 'Uploading file is too large! Max Allowed: 10MB']));
                    return $response;
                }
            }

            $file = $this->get('upload_helper')->uploadFile($uploadingFile);
            if ($file) {
                $responseArray['data'] = [
                    'slug' => $file->getSlug(),
                    'name' => $file->getName()
                ];
                $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
                $response = $response->setContent(json_encode($responseArray));
                return $response;
            } else {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'Problem with uploaded file type!']));
                return $response;
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Not supported file type! Supported are: ' . implode(', ', UploadingFilesOptions::$supportedFileTypesArray)]));
            return $response;
        }
    }

    /**
     *    ### Response ###
     *      {
     *        "data":
     *        {
     *           "slug": "slug-of-uploaded-entity",
     *           "name": "Uploaded file name"
     *        }
     *      }
     *
     * @ApiDoc(
     *  resource = true,
     *  description="Upload an IMAGE file. For data uploading multipart/form-data data format is required! ",
     *  parameters={
     *     {"name"="file", "dataType"="file", "required"=true, "format"="POST", "description"="File to upload"},
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
     *      201 ="The entity was successfully uploaded",
     *      401 ="Unauthorized request",
     *      409 ="Invalid parameters"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \InvalidArgumentException
     */
    public function uploadImageAction(Request $request): Response
    {
        $locationURL = $this->generateUrl('file_upload_image');
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $contentType = $request->headers->get('Content-Type');
        $contentType = substr($contentType, 0, 19);

        if ('multipart/form-data' !== $contentType) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => StatusCodesHelper::INVALID_DATA_FORMAT_MESSAGE_MULTIPART_FORM_SUPPORT]));
            return $response;
        }

        $uploadingFile = $request->files->get('file');

        if (null === $uploadingFile) {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'No file is chosen!']));
            return $response;
        }

        // Check, if the file was uploaded via HTTP POST
        if (!is_uploaded_file($uploadingFile)) {
            $response = $response->setStatusCode(StatusCodesHelper::BAD_REQUEST_CODE);
            $response = $response->setContent(json_encode(['message' => 'Uploading Error! File was not uploaded via HTTP POST. Image needs to have a filename.']));
            return $response;
        }

        // Check if the uploading file is an IMAGE
        $fileType = $uploadingFile->getMimeType();
        $supportedImageTypes = UploadingFilesOptions::$supportedImageTypes;

        if (\in_array($fileType, $supportedImageTypes, true)) {
            //Check the allowed image size
            $imageSize = getimagesize($uploadingFile);
            if ($imageSize && ($imageSize[0] > 10000 || $imageSize[1] > 10000)) {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'Uploading image is too large!']));
                return $response;
            }

            $file = $this->get('upload_helper')->uploadFile($uploadingFile);
            if ($file) {
                $responseArray['data'] = [
                    'slug' => $file->getSlug(),
                    'name' => $file->getName()
                ];
                $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
                $response = $response->setContent(json_encode($responseArray));
                return $response;
            }

            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with uploaded file type!']));
            return $response;
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Not supported image type!']));
            return $response;
        }
    }

    /**
     *  ### Response ###
     *      {
     *        "data":
     *        {
     *           "fileDir": "d3bf991fae9a36a9c2f58c5a780d4d15",
     *           "fileName": "phphHwC70.png",
     *           "prefix": "uploads"
     *        }
     *      }
     *
     * @ApiDoc(
     *  description="Load an Image - returns image location data.",
     *  requirements={
     *     {
     *       "name"="slug",
     *       "dataType"="string",
     *       "description"="Slug of a image"
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
     *      404 ="Not found file"
     *  }
     *  )
     *
     * @param string $slug
     *
     * @return Response
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     *
     */
    public function loadFileLocationDataAction($slug): Response
    {
        $locationURL = $this->generateUrl('file_load_image', ['slug' => $slug]);
        $response = $this->get('api_base.service')->createResponseEntityWithSettings($locationURL);

        $fileEntity = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'slug' => $slug,
        ]);

        if (!$fileEntity instanceof File) {
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'File with requested Slug does not exist in DB!']));
            return $response;
        }

        // Check if the File exists in a web-page file system
        $uploadDir = $this->getParameter('upload_dir');
        $file = $uploadDir . DIRECTORY_SEPARATOR . $fileEntity->getUploadDir() . DIRECTORY_SEPARATOR . $fileEntity->getTempName();

        if (!file_exists($file)) {
            $response = $response->setStatusCode(StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'File with requested Slug does not exist in a web-page File System!']));
            return $response;
        }

        $responseArray ['data'] = [
            'fileDir' => $fileEntity->getUploadDir(),
            'fileName' => $fileEntity->getTempName(),
            'prefix' => 'uploads'
        ];
        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        $response = $response->setContent(json_encode($responseArray));
        return $response;
    }

    /**
     *
     * @ApiDoc(
     *  description="Download file",
     *  requirements={
     *     {
     *       "name"="fileDir",
     *       "dataType"="string",
     *       "description"="File DIR"
     *     },
     *     {
     *       "name"="fileName",
     *       "dataType"="string",
     *       "description"="File DIR"
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
     *  )
     *
     * @param string $fileDir
     * @param string $fileName
     *
     * @return Response|BinaryFileResponse
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     *
     */
    public function downloadFileAction(string $fileDir, string $fileName)
    {
        $uploadDir = $this->getParameter('upload_dir');
        $file = $uploadDir . DIRECTORY_SEPARATOR . $fileDir . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($file)) {
            $response = new Response();
            $response->headers->set('Location', $this->generateUrl('file_download', ['fileName' => $fileName, 'fileDir' => $fileDir]));
            $response = $response->setStatusCode(StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'File with requested Data does not exist in a web-page File System!']));
            return $response;
        }

        $fileEntity = $this->getDoctrine()->getRepository('APICoreBundle:File')->findOneBy([
            'uploadDir' => $fileDir,
            'tempName' => $fileName
        ]);

        if (!$fileEntity instanceof File) {
            $response = new Response();
            $response->headers->set('Location', $this->generateUrl('file_download', ['fileName' => $fileName, 'fileDir' => $fileDir]));
            $response = $response->setStatusCode(StatusCodesHelper::NOT_FOUND_CODE);
            $response = $response->setContent(json_encode(['message' => 'File with requested Data does not exist in DB!']));
            return $response;
        }

        $response = new BinaryFileResponse($file);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $fileName,
            iconv('UTF-8', 'ASCII//TRANSLIT', $fileName)
        );

        $response->headers->set('Location', $this->generateUrl('file_download', ['fileName' => $fileName, 'fileDir' => $fileDir]));
        $response->headers->set('Content-type', $fileEntity->getType());
        $response->headers->set('Content-length', $fileEntity->getSize());
        $response->headers->set('Last-Modified', $fileEntity->getUpdatedAt());
        if ($fileEntity->isPublic()) {
            $response->setPublic();
        } else {
            $response->setPrivate();
        }

        $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
        return $response;
    }
}
