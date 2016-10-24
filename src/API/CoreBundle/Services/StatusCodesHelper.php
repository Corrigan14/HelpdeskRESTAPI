<?php

namespace API\CoreBundle\Services;

/**
 * Class CodeStatusHelper
 * Should be used for generating standardized responses
 *
 * @package API\CoreBundle\Services
 */
class StatusCodesHelper
{
    const SUCCESSFUL_CODE = 200;
    const SUCCESSFUL_MESSAGE = 'The request has succeeded';
    const CREATED_CODE = 201;
    const CREATED_MESSAGE = 'The entity was successfully created';
    const DELETED_CODE = 204;
    const DELETED_MESSAGE = 'The entity was successfully deleted';

    const BAD_REQUEST_MESSAGE = 'Bad Request';
    const BAD_REQUEST_CODE = 400;
    const INVALID_TOKEN_MESSAGE = 'Token not valid';
    const INVALID_TOKEN_CODE = 400;
    const UNAUTHORIZED_MESSAGE = 'You are not authorized';
    const UNAUTHORIZED_CODE = 401;
    const INCORRECT_CREDENTIALS_MESSAGE = 'Incorrect credentials';
    const INCORRECT_CREDENTIALS_CODE = 403;
    const RESOURCE_NOT_FOUND_MESSAGE = 'Resource not found';
    const RESOURCE_NOT_FOUND_CODE = 404;
    const USER_NOT_FOUND_MESSAGE = 'User not found';
    const USER_NOT_FOUND_CODE = 404;
    const INVALID_PARAMETERS_MESSAGE = 'Invalid parameters';
    const INVALID_PARAMETERS_CODE = 409;


}