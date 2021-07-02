<?php

namespace Core\Adaptors\Vendor\OAuth;

use Core\Adaptors\Adaptor;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Throwable;

/**
 * Class Provider
 *
 * @package Core\Adaptors\Vendor\OAuth
 *
 * @property GenericProvider|null $classInstance
 * @property string $className = 'GenericProvider'
 */
class Provider extends Adaptor
{
    public array $options = [];
    public array $collaborators = [];

    /**
     * Call the providers getAccessToken method.
     *
     * @param mixed $grant
     * @param array $options
     *
     * @return AccessToken|AccessTokenInterface
     *
     * @throws Throwable
     */
    public function getAccessToken(mixed $grant, array $options = []): AccessToken|AccessTokenInterface
    {
        return $this->useThrowable(fn() => $this->classInstance->getAccessToken($grant, $options));
    }
}