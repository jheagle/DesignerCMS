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
use Core\Contracts\LazyAssignable;
use Core\Traits\LazyAssignment;
use Core\Utilities\Functional\Pure;
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
class RequestHandler implements LazyAssignable
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
    private string $baseUrl = '';
    private string $endpointUrl = '';
    private string $method = 'GET';
    private array $headers = [];
    private string $tokenCache = 'endpoint-token';
    private string $tokenGrantType = '';
    private array $credentials = [
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
    private ?Client $curlClient = null;
    private ?Response $curlResponse = null;
    private ?Provider $authProvider = null;
    private ?CacheRegistry $cacheProvider = null;

    /**
     * RequestHandler constructor.
     *
     * @param array $config The config data for making requests to an endpoint
     */
    private function __construct(array $config = [])
    {
        $this->applyMemberSettings($config);
        if (is_null($this->cacheProvider)) {
            $this->cacheProvider = CacheRegistry::singleton()->build();
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
                    $credentials[$name] = Pure::envGet(
                        preg_replace("/^ENV:{(.*)}$/", "$1", $credentials[$name])
                    );
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
     * @param RequestHandlerOptions $config Provide the $config data from the Form Project
     * @param RequestDetails|null $requestDetails An array of settings to be used in the request
     *
     * @return callable|RequestHandler
     */
    public static function prepareApiHandler(
        RequestHandlerOptions $config,
        RequestDetails $requestDetails = null
    ): callable|RequestHandler {
        /**
         * Pre-loaded with config and base URL, this function will take the array of request details having endpointUrl
         * defined as one of the elements and return an API Handler.
         *
         * @param RequestHandlerOptions $requestDetails
         *
         * @return RequestHandler
         */
        $preparedHandler = function (
            RequestDetails $requestDetails
        ) use (
            $config,
        ): self {
            if (!is_string($requestDetails->endpointUrl)) {
                throw new InvalidArgumentException(
                    "'requestDetails' must provide an 'endpointUrl' property with a string value"
                );
            }
            return new self(array_replace_recursive($config->toArray(), $requestDetails->toArray()));
        };
        return (!is_string($config->endpointUrl) && (is_null($requestDetails) || !is_string(
                    $requestDetails->endpointUrl
                )))
            ? $preparedHandler
            : $preparedHandler($requestDetails ?? $config);
    }

    /**
     * Submit the submission data to the correct API url to be stored, return any responses from the API server.
     *
     * @param RequestDataSettings|null $requestSettings Includes settings such as request payload, withAuthorization,
     *     and renewToken
     *
     * @return Response
     */
    public function completeRequest(RequestDataSettings $requestSettings = null): Response
    {
        if (is_null($requestSettings)) {
            $requestSettings = RequestDataSettings::fromArray();
        }
        $requestOptions = RequestOptions::initialize();
        $options = [$requestOptions->HEADERS => $this->headers];
        if ($requestSettings->submitData) {
            // When there is submit data, add the json content type and the submission data
            $options[$requestOptions->HEADERS]['Content-Type'] = 'application/json';
            $options[$requestOptions->JSON] = $requestSettings->submitData;
        }
        // Attempt to retrieve an access token, add to headers on success
        if ($accessToken = ($requestSettings->authorize ? $this->authorizeConnection(
            $requestSettings->renewToken
        ) : false)) {
            $options[$requestOptions->HEADERS]['Authorization'] = $accessToken;
        }
        $response = $this->sendRequest($this->endpointUrl, $this->method, $options);
        $status = $response->getStatusCode();
        // If this request should have an authorization token and failed, attempt to renew the token and retry.
        if (($status < 200 || $status >= 300) && !$requestSettings->renewToken) {
            $requestSettings->renewToken = true;
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
     */
    private function authorizeConnection(bool $renew = false): string
    {
        if (empty($this->tokenGrantType)) {
            return '';
        }
        try {
            if ($renew) {
                $this->cacheProvider->delete($this->tokenCache);
            }
            return $this->cacheProvider->get(
                $this->tokenCache,
                fn(CacheItem $item) => $this->fetchAuthenticationToken($item)
            );
        } catch (\Core\Adaptors\Vendor\CacheRegistry\Exceptions\InvalidArgumentException|Throwable) {
        }
        return '';
    }

    /**
     * Retrieve an authentication token.
     *
     * @param CacheItem $item
     *
     * @return string
     *
     * @throws Throwable
     */
    private function fetchAuthenticationToken(CacheItem $item): string
    {
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
            $tokenResult = $provider->getAccessToken($this->tokenGrantType);
            $item->expiresAfter($tokenResult->getExpires());
            return $tokenResult->getToken();
        } catch (RequestException|IdentityException $e) {
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

    /**
     * Using Curl Adaptor, build and send a request to an external resource.
     *
     * @param string $url The url of the remote resource.
     * @param string $method The request method to be applied ['GET', 'POST', 'OPTIONS', 'CREATE', etc.]
     * @param array $options Additional details to be applied as the final parameters in the HttpClient::request()
     *
     * @return Response
     */
    private function sendRequest(string $url, string $method = 'GET', array $options = []): Response
    {
        $options['exceptions'] = $options['exceptions'] ?? false;
        $client = $this->curlClient ?? Client::instantiate()->with(['verify' => false])->build();
        try {
            return $client->request($method, $url, $options);
        } catch (CurlException|RequestException|Throwable $e) {
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
