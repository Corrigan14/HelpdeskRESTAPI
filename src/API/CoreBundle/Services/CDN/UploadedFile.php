<?php
namespace API\CoreBundle\Services\CDN;


use \Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;


/**
 * Class UploadedFile
 *
 * @package API\CoreBundle\Services\CDN;
 */
class UploadedFile extends SymfonyUploadedFile
{
    /**
     * @var string relative path to uploaded direcotry
     */
    private $uploadDir;


    /**
     * Fill an entity with file data
     *
     * @param FileEntityInterface $file
     */
    public function save(FileEntityInterface $file)
    {
        $file->setUploadDir($this->getUploadDir());
        $file->setName($this->getClientOriginalName());
        $file->setSize($this->getClientSize());
        $file->setTempName($this->getFilename());
        try {
            $file->setType($this->getMimeType());
        } catch (\Exception $e) {
            $file->setType('');
        }
    }


    /**
     * @return string
     */
    public function getUploadDir()
    {
        return $this->uploadDir;
    }


    /**
     * @param string $uploadDir
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }


}