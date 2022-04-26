<?php

namespace Core\Adaptors\Vendor\OAuth;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Throwable;

interface ProviderInterface
{
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
    public function getAccessToken(mixed $grant, array $options = []): AccessToken|AccessTokenInterface;
}