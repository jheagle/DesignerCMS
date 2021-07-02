<?php

namespace Core\Adaptors\Vendor\OAuth\Exceptions;

use Core\Adaptors\ExceptionAdaptor;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Class IdentityException
 *
 * @package Core\Adaptors\Vendor\OAuth\Exceptions
 *
 * @property IdentityProviderException $classInstance
 */
class IdentityException extends ExceptionAdaptor
{
}