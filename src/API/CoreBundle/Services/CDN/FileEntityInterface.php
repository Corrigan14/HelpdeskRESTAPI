<?php


namespace API\CoreBundle\Services\CDN;


/**
 * Interface FileEntityInterface
 */

interface FileEntityInterface
{

    /**
     * Set the file name
     *
     * @param string $name
     *
     * @return mixed
     */

    public function setName($name);


    /**
     * Set the tmp_name of uploaded file
     *
     * @param string $tempName
     *
     * @return mixed
     */

    public function setTempName($tempName);


    /**
     * Set the mime type of the file
     *
     * @param string $type
     *
     * @return mixed
     */

    public function setType($type);


    /**
     * Set the sime in mb
     *
     * @param integer $size
     *
     * @return mixed
     */

    public function setSize($size);


    /**
     * Relative path to uploaded file
     *
     * @param string $uploadDir
     *
     * @return mixed
     */

    public function setUploadDir($uploadDir);


    /**
     * Get the url name for the file
     *
     * @return string
     */

    public function getSlug();

}