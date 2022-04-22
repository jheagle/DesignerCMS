<?php

namespace Core\Controllers\Api;

use Core\Adaptors\Vendor\Curl\Client;
use Core\Objects\DataTransferObject;

/**
 * Class RequestHandlerOptions
 *
 * @package Core\Controllers\Api
 */
class RequestDetails extends DataTransferObject
{
    public const METHOD_CONNECT = 'CONNECT';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_GET = 'GET';
    public const METHOD_HEAD = 'HEAD';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_TRACE = 'TRACE';

    public ?Client $curlClient = null;
    public ?string $endpointUrl = null;
    public array $headers = ['Accept' => 'application/json'];
    public string $method = self::METHOD_GET;
}