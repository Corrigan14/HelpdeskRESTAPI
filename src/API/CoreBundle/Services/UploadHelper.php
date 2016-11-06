<?php


namespace API\CoreBundle\Services;


use API\CoreBundle\Entity\File;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Class UploadHelper
 *
 * @package API\CoreBundle\Services
 */
class UploadHelper
{
    /**
     * @var Container
     */
    private $container;

    private $uploadDir;


    /**
     * @var EntityManager
     */
    private $em;


    /**
     * @param Container $container
     */
    public function __construct($uploadDir, EntityManager $em)
    {
        //$this->container = $container;
        $this->uploadDir = $uploadDir;
        $this->em = $em;
    }


    /**
     * @param array $files
     *
     *
     * @return array
     */
    public function uploadFiles($files)
    {
        $slugs = [];
        foreach ($files as $file) {
            if (!empty($file)) {
                $slugs[] = $this->uploadFile($file);
            }
        }


        return $slugs;
    }


    /**
     * @param UploadedFile $file
     *
     * @return string
     */
    public function uploadFile(UploadedFile $file)
    {
        $fileEntity = new File();
        $uploadDir = $this->getUploadDir();
        $fileEntity->setUploadDir($uploadDir['dir']);
        $fileEntity->setName($file->getClientOriginalName());
        $fileEntity->setSize($file->getClientSize());
        $fileEntity->setTempName($file->getFilename());
        $fileEntity->setType($file->getMimeType());
        $fileEntity->setSlug(rand(0,123456));
        $this->em->persist($fileEntity);
        $this->em->flush();
        $file->move($uploadDir['path']);


        //return $fileEntity->getSlug();
        return $fileEntity;
    }
    /**
     * Get a relatively unique folder name
     *
     * @param bool $onlyName
     *
     * @return string|array folder name or path and directory name ('path'=> '', 'dir'=>'')
     */
    public function getUploadDir($onlyName = false)
    {
        $characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789".time();


        $unique = md5(str_shuffle($characters));
        if ($onlyName) {
            return $unique;
        }
        //$uploadDir = $this->container->getParameter('upload_dir');
        $uploadDir = $this->uploadDir;
        $uploadDir = $uploadDir.DIRECTORY_SEPARATOR.$unique;


        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }


        return [
            'path' => $uploadDir,
            'dir'  => $unique,
        ];
    }
}