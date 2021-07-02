<?php

namespace Core\Controllers\Api;

use Core\Adaptors\Vendor\CacheRegistry\CacheItem;
use Core\Adaptors\Vendor\CacheRegistry\CacheRegistry;
use Core\Adaptors\Vendor\Curl\Client;
use Core\Adaptors\Vendor\Curl\Exceptions\CurlException;
use Core\Adaptors\Vendor\Curl\Exceptions\RequestException;
use Core\Adaptors\Vendor\Curl\RequestOptions;
use Core\Adaptors\Vendor\Curl\Response;
use Core\Adaptors\Vendor\Logger\Logger;
use Core\Adaptors\Vendor\OAuth\Exceptions\IdentityException;
use Core\Adaptors\Vendor\OAuth\Provider;
use Core\Traits\LazyAssignment;
use InvalidArgumentException;
use Throwable;

/**
 * Class RequestHandler
 *
 * Provide a means to connect with external APIs. Enables authentication via OAuth, requires an array of config data
 * to connect.
 *
 * @package Core\Controllers\API
 */
class RequestHandler
{
    use LazyAssignment;

    /**
     * Class Members
     *
     * @var string $baseUrl The root URL to connect with
     * @var string $endpointUrl The submission path
     * @var string $method The request method to be used
     * @var string $headers Additional headers to be sent
     * @var string $tokenCache A name for storing and retrieving cached access tokens
     * @var string $tokenGrantType The type of oauth authentication
     * @var array $credentials An name keyed array of the required access credentials
     */
    protected string $baseUrl = '';
    protected string $endpointUrl = '';
    protected string $method = 'GET';
    protected array $headers = [];
    protected string $tokenCache = 'endpoint-token';
    protected string $tokenGrantType = '';
    protected array $credentials = [
        'urlAuthorize' => '',
        'urlResourceOwnerDetails' => '',
    ];

    /**
     * Instance variables
     *
     * @var Client|null $curlClient Provide a custom client
     * @var Response|null $curlResponse Provide a custom response
     * @var Provider|null $authProvider Provide a custom Oauth2 Provider
     */
    protected ?Client $curlClient = null;
    protected ?Response $curlResponse = null;
    protected ?Provider $authProvider = null;
    protected ?CacheRegistry $cacheProvider = null;

    /**
     * RequestHandler constructor.
     *
     * @param array $config The config data for making requests to an endpoint
     */
    public function __construct(array $config = [])
    {
        $this->applyMemberSettings($config);
        if (is_null($this->cacheProvider)) {
            $this->cacheProvider = CacheRegistry::instantiate()->build();
        }
        if (!empty($this->baseUrl) && !str_starts_with($this->endpointUrl, $this->baseUrl)) {
            // Prepend the base URL to the endpoint URL
            $this->endpointUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($this->endpointUrl, '/');
        }
        // Format the stored credentials and apply to this
        $this->credentials = array_reduce(
            array_keys($this->credentials),
            function (array $credentials, string $name): array {
                if (str_starts_with($name, 'url')) {
                    // All credentials with keys preceded with 'url' must have the baseUrl prepended
                    $credentials[$name] = rtrim($this->baseUrl, '/')
                        . '/'
                        . ltrim($credentials[$name], '/');
                }
                if (preg_match('/^ENV:{.*}$/', $credentials[$name])) {
                    // TODO: use some mechanism to store credentials
                    $configKey = preg_replace("/^ENV:{(.*)}$/", "$1", $credentials[$name]);
                    // TODO: Fetch the credentials and set them on $credentials[$name]
                    $credentials[$name] = $configKey;
                }
                return $credentials;
            },
            $this->credentials
        );
    }

    /**
     * Prepare an instance of the API Handler in order to subsequently complete a submission, will return a function
     * which expects the array of request details having endpointUrl defined as one of the elements. If the array is
     * provided and it has 'endpointUrl', return the API Handler.
     *
     * @param array $config Provide the $config data from the Form Project
     * @param string $baseUrl Provide the base URL for where the form will be submitted
     * @param null|array $requestDetails An array of settings to be used in the request
     * [
     * 'method' => '(null|string) Optionally, provide the type of request (ex: POST or GET)',
     * 'endpointUrl' => '(null|string) Optionally, provide the specific path to submit to',
     * 'headers' => '(null|string[]) Optionally, provide the headers for the request',
     * 'curlClient' => '(null|\GuzzleHttp\Client) Optionally, provide a custom client'
     * ]
     *
     * @return callable|RequestHandler
     */
    public static function prepareApiHandler(
        array $config = [],
        string $baseUrl = '',
        ?array $requestDetails = null
    ): callable|RequestHandler {
        /**
         * Pre-loaded with config and base URL, this function will take the array of request details having endpointUrl
         * defined as one of the elements and return an API Handler.
         *
         * @param array $requestDetails An array of settings to be used in the request
         * [
         * 'method' => '(null|string) Optionally, provide the type of request (ex: POST or GET)',
         * 'endpointUrl' => '(string) Provide the specific path to submit to',
         * 'headers' => '(null|string[]) Optionally, provide the headers for the request',
         * 'curlClient' => '(null|\GuzzleHttp\Client) Optionally, provide a custom client'
         * ]
         *
         * @return RequestHandler
         */
        $preparedHandler = function (
            array $requestDetails
        ) use (
            $config,
            $baseUrl
        ): RequestHandler {
            $endpointUrl = $requestDetails['endpointUrl'] ?? null;
            if (!is_string($endpointUrl)) {
                throw new InvalidArgumentException(
                    "'requestDetails' must provide an 'endpointUrl' property with a string value"
                );
            }
            return new RequestHandler(
                [
                    'tokenCache' => $config['tokenCache'] ?? 'cache-endpoint-token',
                    'tokenGrantType' => $config['grantType'] ?? '',
                    'credentials' => $config['credentials'] ?? [],
                    'baseUrl' => $baseUrl,
                    'method' => $requestDetails['method'] ?? 'GET',
                    'endpointUrl' => $endpointUrl,
                    'headers' => $requestDetails['headers']
                        ?? ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    'curlClient' => $requestDetails['curlClient'] ?? null,
                    'cacheProvider' => $requestDetails['cacheProvider'] ?? null,
                ]
            );
        };
        return (!is_array($requestDetails) || !is_string($requestDetails['endpointUrl'] ?? null))
            ? $preparedHandler
            : $preparedHandler($requestDetails);
    }

    /**
     * Submit the submission data to the correct API url to be stored, return any responses from the API server.
     *
     * @param array $requestSettings Includes settings such as request payload, withAuthorization, and renewToken
     *
     * @return Response
     *
     * @throws Throwable
     */
    public function completeRequest(array $requestSettings = []): Response
    {
        $submitData = $requestSettings['submitData'] ?? false;
        $renewToken = $requestSettings['renewToken'] ?? false;
        $authorize = $requestSettings['authorize'] ?? true;
        $requestOptions = RequestOptions::initialize();
        $options = [$requestOptions->HEADERS => $this->headers];
        if ($submitData) {
            // When there is submit data, add the json content type and the submission data
            $options[$requestOptions->HEADERS]['Content-Type'] = 'application/json';
            $options[$requestOptions->JSON] = $submitData;
        }
        // Attempt to retrieve an access token, add to headers on success
        if ($accessToken = ($authorize ? $this->authorizeConnection($renewToken) : false)) {
            $options[$requestOptions->HEADERS]['Authorization'] = $accessToken;
        }
        $response = $this->sendRequest($this->endpointUrl, $this->method, $options);
        $status = $response->getStatusCode();
        // If this request should have an authorization token and failed, attempt to renew the token and retry.
        if (($status < 200 || $status >= 300) && !$renewToken) {
            $requestSettings['renewToken'] = true;
            return $this->completeRequest($requestSettings);
        }
        // Return a new response with the recent response headers appended
        return $response;
    }

    /**
     * This method is responsible for retrieving and providing a JWT access token for connecting with the API.
     *
     * @param bool $renew Choose whether to use the cached token or to request a new one.
     *
     * @return string
     *
     * @throws Throwable
     */
    protected function authorizeConnection(bool $renew = false): string
    {
        if (!array_key_exists('urlAccessToken', $this->credentials)) {
            return '';
        }
        if ($renew) {
            $this->cacheProvider->delete($this->tokenCache);
        }
        return $this->cacheProvider->get(
            $this->tokenCache,
            function (CacheItem $item): string {
                $clientClass = $this->curlClient ? get_class($this->curlClient) : Client::class;
                $provider = $this->authProvider ?? Provider::instantiate()
                        ->with(
                            [
                                'options' => $this->credentials,
                                'collaborators' => [
                                    'httpClient' => $this->curlClient ?? new $clientClass(['verify' => false]),
                                ],
                            ]
                        )
                        ->build();
                try {
                    // Try to get an access token using the client credentials grant.
                    $tokenResult = $provider->getAccessToken($this->tokenGrantType);
                    $item->expiresAfter($tokenResult->getExpires());
                    return $tokenResult->getToken();
                } catch (RequestException | IdentityException $e) {
                    Logger::error(
                        'Unable to reach the specified endpoint',
                        [
                            'Endpoint URL' => $this->baseUrl,
                            'RequestException' => $e,
                        ]
                    );
                }
                return '';
            }
        );
    }

    /**
     * Using Guzzle, build and send a request to an external resource.
     *
     * @param string $url The url of the remote resource.
     * @param string $method The request method to be applied ['GET', 'POST', 'OPTIONS', 'CREATE', etc.]
     * @param array $options Additional details to be applied as the final parameters in the HttpClient::request()
     *
     * @return Response
     */
    private function sendRequest(string $url, string $method = 'GET', array $options = []): Response
    {
        // Default to not throwing exceptions for non-success state
        $options['exceptions'] = $options['exceptions'] ?? false;
        $client = $this->curlClient ?? Client::instantiate()->with(['verify' => false])->build();
        // Attempt to connect to remote resource, log in case of failure
        try {
            return $client->request($method, $url, $options);
        } catch (CurlException | RequestException | Throwable $e) {
            Logger::error(
                'Unable to reach the specified endpoint',
                [
                    'Endpoint URL' => $url,
                    'Request Method' => $method,
                    'Options' => $options,
                    'RequestException' => $e,
                ]
            );
        }
        return $this->curlResponse ?? Response::reset()::instantiate()
                ->with(
                    [
                        'status' => 503,
                        'headers' => [],
                        'body' => 'Invalid submission destination.',
                    ]
                )
                ->build();
    }
}
