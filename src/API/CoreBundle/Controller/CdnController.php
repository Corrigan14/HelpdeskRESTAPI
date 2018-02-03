<?php

namespace API\CoreBundle\Controller;

use API\TaskBundle\Security\VoteOptions;
use API\TaskBundle\Entity\Task;
use API\TaskBundle\Entity\TaskHasAttachment;
use Igsem\APIBundle\Controller\ApiBaseController;
use API\CoreBundle\Services\CDN\UploadedFile;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use API\CoreBundle\Entity\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
     *           "slug": "slug-of-uploaded-entity"
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
     *      401 ="Unauthorized request",
     *      409 ="Invalid parameters"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Doctrine\ORM\OptimisticLockException
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

        $file = $this->get('upload_helper')->uploadFile($uploadingFile);
        if ($file) {
            $responseArray['data'] = ['slug' => $file->getSlug()];
            $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
            $response = $response->setContent(json_encode($responseArray));
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Problem with uploaded file type extension!']));
        }
        return $response;
    }

    /**
     *    ### Response ###
     *      {
     *        "data":
     *        {
     *           "slug": "slug-of-uploaded-entity"
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
     * @throws \Doctrine\ORM\OptimisticLockException
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

        // Check if the uploading file is an IMAGE
        $fileType = $uploadingFile->getMimeType();
        if ('image/png' === $fileType || 'image/gif' === $fileType || 'image/jpg' === $fileType || 'image/jpeg' === $fileType) {
            $file = $this->get('upload_helper')->uploadFile($uploadingFile);
            if ($file) {
                $responseArray['data'] = ['slug' => $file->getSlug()];
                $response = $response->setStatusCode(StatusCodesHelper::SUCCESSFUL_CODE);
                $response = $response->setContent(json_encode($responseArray));
                return $response;
            } else {
                $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
                $response = $response->setContent(json_encode(['message' => 'Problem with uploaded file type extension!']));
                return $response;
            }
        } else {
            $response = $response->setStatusCode(StatusCodesHelper::INVALID_PARAMETERS_CODE);
            $response = $response->setContent(json_encode(['message' => 'Not supported image type!']));
            return $response;
        }
    }


    /**
     * @ApiDoc(
     *  description="Upload attachments to TASK - PREROBIT, DAT URL",
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
     *  output="API\TaskBundle\Entity\Task",
     *  statusCodes={
     *      200 ="The request has succeeded",
     *      401 ="Unauthorized request",
     *      403 ="Access denied",
     *      404 ="Not found Entity",
     *  }
     * )
     * @param Request $request
     * @param Task $task
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function uploadFilesAction(Request $request, Task $task)
    {
        if (!$this->get('task_voter')->isGranted(VoteOptions::ADD_ATTACHMENT_TO_TASK, $task)) {
            return $this->accessDeniedResponse();
        }

        $files = $_FILES;
        $slugs = [];
        try {
            foreach ($files as $file) {
                $slugs[] = $this->processFile($this->createUploadedFile($file));
            }
            foreach ($slugs as $slug) {
                if ($this->canAddAttachmentToTask($task, $slug)) {
                    $taskHasAttachment = new TaskHasAttachment();
                    $taskHasAttachment->setTask($task);
                    $taskHasAttachment->setSlug($slug);
                    $task->addTaskHasAttachment($taskHasAttachment);
                    $this->getDoctrine()->getManager()->persist($taskHasAttachment);
                }
            }

            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();
        } catch (\Exception $e) {
            return $this->createApiResponse([
                'message' => $e->getMessage(),
            ], StatusCodesHelper::INVALID_PARAMETERS_CODE);
        }

        $ids = [
            'id' => $task->getId(),
            'projectId' => false,
            'requesterId' => false,
        ];
        $response = $this->get('task_service')->getTaskResponse($ids);
        $responseData['data'] = $response['data'];//[0];
        $responseLinks['_links'] = $response['_links'];

        return $this->json(array_merge($responseData, $responseLinks), StatusCodesHelper::CREATED_CODE);
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
            'slug' => $slug,
        ]);

        if (null === $fileEntity) {
            return $this->createApiResponse(['message' => StatusCodesHelper::RESOURCE_NOT_FOUND_MESSAGE,], StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
        }
        $uploadDir = $this->getParameter('upload_dir');

        $file = $uploadDir . DIRECTORY_SEPARATOR . $fileEntity->getUploadDir() . DIRECTORY_SEPARATOR . $fileEntity->getTempName();

        if (!file_exists($file)) {
            return $this->createApiResponse(['message' => StatusCodesHelper::RESOURCE_NOT_FOUND_MESSAGE,], StatusCodesHelper::RESOURCE_NOT_FOUND_CODE);
        }


        // Generate response
        $response = new Response();

        if ($fileEntity->isPublic()) {
            $response->setPublic();
            $response->setSharedMaxAge(3600);
        } else {
            $response->setPrivate();
        }

        // Set headers
        $response->headers->set('Content-type', mime_content_type($file));//$fileEntity->getType());
        $response->headers->set('Content-length', filesize($file));//$fileEntity->getSize());
//        $response->headers->set('Access-Control-Allow-Origin' , '*');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(file_get_contents($file));

        return $response;
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
        $uploadDir = $this->getParameter('upload_dir');
        $uploadDir = $uploadDir . DIRECTORY_SEPARATOR . $unique;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        return [
            'path' => $uploadDir,
            'dir' => $unique,
        ];
    }

    /**
     * @param UploadedFile $file
     * @param bool $public
     *
     * @return mixed
     * @throws \LogicException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    private function processFile(UploadedFile $file, $public = false)
    {
        $target = $this->getFolderDir();
        $file->setUploadDir($target['dir']);
        $fileEntity = new File();
        $file->save($fileEntity);
        $file->move($target['path']);

        if ($public) {
            $fileEntity->setPublic(true);
        }

        $this->getDoctrine()->getManager()->persist($fileEntity);
        $this->getDoctrine()->getManager()->flush();

        return $fileEntity->getSlug();
    }

    /**
     * @param Task $task
     * @param string $slug
     *
     * @return bool
     * @throws \LogicException
     */
    private function canAddAttachmentToTask(Task $task, string $slug): bool
    {
        $taskHasAttachment = $this->getDoctrine()->getRepository('APITaskBundle:TaskHasAttachment')->findOneBy([
            'task' => $task,
            'slug' => $slug,
        ]);

        return (!$taskHasAttachment instanceof TaskHasAttachment);
    }
}
