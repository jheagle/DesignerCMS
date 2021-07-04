<?php

namespace Core\Controllers\Api;

/**
 * Class RequestHandlerOptions
 *
 * @package Core\Controllers\Api
 */
class RequestHandlerOptions extends RequestDetails
{
    public const CREDENTIAL_AUTHORIZE = 'urlAuthorize';
    public const CREDENTIAL_TOKEN = 'urlAccessToken';
    public const CREDENTIAL_RESOURCE = 'urlResourceOwnerDetails';

    public const GRANT_AUTHORIZATION_CODE = 'authorization-code';
    public const GRANT_CLIENT_CREDENTIALS = 'client-credentials';
    public const GRANT_PASSWORD = 'password';
    public const GRANT_REFRESH_TOKEN = 'refresh-token';

    public const GRANT_TYPE = [
        self::GRANT_AUTHORIZATION_CODE,
        self::GRANT_CLIENT_CREDENTIALS,
        self::GRANT_PASSWORD,
        self::GRANT_REFRESH_TOKEN,
    ];

    public string $baseUrl = '';
    public array $credentials = [
        self::CREDENTIAL_TOKEN => '',
        self::CREDENTIAL_AUTHORIZE => '',
        self::CREDENTIAL_RESOURCE => '',
    ];
    public string $tokenCache = '';
    public string $tokenGrantType = '';
}