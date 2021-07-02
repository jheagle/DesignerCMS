<?php

namespace Core\Adaptors\Vendor\Curl\Exceptions;

use Core\Adaptors\ExceptionAdaptor;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class CurlException
 *
 * @package Core\Adaptors\Vendor\Curl\Exceptions
 *
 * @property GuzzleException $classInstance
 */
class CurlException extends ExceptionAdaptor
{
}