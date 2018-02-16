<?php

namespace API\CoreBundle\Security;

/**
 * Class UploadingFilesOptions
 *
 * @package API\CoreBundle\Security
 */
class UploadingFilesOptions
{
    // Allowed keys in filter array
    public static $supportedImageTypes = [
        'image/png',
        'image/jpg',
        'image/jpeg',
        'image/gif'

    ];

    public static $supportedFileTypes = [
        'image/png',
        'image/jpg',
        'image/jpeg',
        'image/gif',
        'application/zip',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.ms-office',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/pdf',
        'text/plain',
        'application/xml',
        'text/xml'

    ];

    public static $supportedFileTypesArray = [
        'png',
        'jpg',
        'jpeg',
        'gif',
        'zip',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'pdf',
        'txt',
        'xml'
    ];
}