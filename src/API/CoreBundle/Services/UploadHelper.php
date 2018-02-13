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
     * @var String
     */

    private $uploadDir;


    /**
     * @var EntityManager
     */
    private $em;


    /**
     * @param String $uploadDir
     * @param EntityManager $em
     */
    public function __construct(String $uploadDir, EntityManager $em)
    {
        $this->uploadDir = $uploadDir;
        $this->em = $em;
    }


    /**
     * @param array $files
     *
     *
     * @return array
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function uploadFiles($files): array
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
     * @param bool $public
     * @return bool|File
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function uploadFile(UploadedFile $file, bool $public = false)
    {
        $fileEntity = new File();
        $uploadDir = $this->getUploadDir();

        $fileEntity->setUploadDir($uploadDir['dir']);
        $fileEntity->setName($file->getClientOriginalName());
        $fileEntity->setSize($file->getClientSize());
        try {
            $fileEntity->setType($file->getMimeType());
        } catch (\Exception $e) {
            return false;
        }
        $guessExtension = $file->guessExtension();
        $fileName = $file->getFilename() . '.' . $guessExtension;

        $fileEntity->setTempName($fileName);
        $fileEntity->setPublic($public);
        $this->em->persist($fileEntity);
        $this->em->flush();

        $file->move($uploadDir['path'], $fileName);



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
        $characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789" . time();

        $unique = md5(str_shuffle($characters));
        if ($onlyName) {
            return $unique;
        }

        $uploadDir = $this->uploadDir;
        $uploadDir = $uploadDir . DIRECTORY_SEPARATOR . $unique;


        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }


        return [
            'path' => $uploadDir,
            'dir' => $unique,
        ];
    }
}