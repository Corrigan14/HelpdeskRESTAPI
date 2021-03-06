<?php

namespace Igsem\APIBundle\Services;

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
    const UNACITVATE_MESSAGE = 'The entity was successfully inactivated!';
    const ACITVATE_MESSAGE = 'The entity was successfully activated!';

    const INVALID_DATA_FORMAT_MESSAGE_JSON_FORM_SUPPORT = 'Problem with data coding. Supported Content Types: application/json, application/x-www-form-urlencoded';
    const INVALID_DATA_FORMAT_MESSAGE_JSON_SUPPORT = 'Problem with data coding. Supported Content Type: application/json';
    const INVALID_DATA_FORMAT_MESSAGE_X_CODE_SUPPORT = 'Problem with data coding. Supported Content Type: application/x-www-form-urlencoded';
    const INVALID_DATA_FORMAT_MESSAGE_MULTIPART_FORM_SUPPORT = 'Problem with data coding. Supported Content Type: multipart/form-data';
    const INVALID_DATA_FORMAT_MESSAGE_IMAGE_SUPPORT = 'Problem with data coding. Supported Content Type: image/gif';
    const PROBLEM_WITH_FILTER_DATA_CODING = 'Problem with filter data coding!';
    const BAD_REQUEST_MESSAGE = 'Bad Request';
    const BAD_REQUEST_CODE = 400;
    const INVALID_TOKEN_MESSAGE = 'Token not valid';
    const INVALID_TOKEN_CODE = 400;
    const UNAUTHORIZED_MESSAGE = 'You are not authorized';
    const ACCOUNT_DISABLED_MESSAGE = 'User account is disabled';
    const UNAUTHORIZED_CODE = 401;
    const INCORRECT_CREDENTIALS_MESSAGE = 'Incorrect credentials';
    const INCORRECT_CREDENTIALS_CODE = 403;
    const ACCESS_DENIED_MESSAGE = 'Access denied';
    const ACCESS_DENIED_CODE = 403;
    const RESOURCE_NOT_FOUND_MESSAGE = 'Resource not found';
    const RESOURCE_NOT_FOUND_CODE = 404;
    const USER_NOT_FOUND_MESSAGE = 'User not found';
    const USER_NOT_FOUND_CODE = 404;
    const NOT_FOUND_MESSAGE = 'Not found';
    const NOT_FOUND_CODE = 404;
    const ROUTE_REQUIREMENT_NOTMET_MESSAGE = 'No route found';
    const ROUTE_REQUIREMENT_NOTMET_CODE = 405;
    const INVALID_PARAMETERS_MESSAGE = 'Invalid parameters';
    const INVALID_PARAMETERS_CODE = 409;
    const PROBLEM_WITH_EMAIL_SENDING = 422;

    const INVALID_JWT_TOKEN_MESSAGE = 'Invalid JWT Token';
    const INVALID_JWT_TOKEN_CODE = 401;

    const NO_CRON_TASKS_CREATED = 'No tasks created!';
}