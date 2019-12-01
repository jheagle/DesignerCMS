<?php

namespace Core\Controllers\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

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
    protected $baseUrl = '';
    protected $endpointUrl = '';
    protected $method = 'GET';
    protected $headers = [];
    protected $tokenCache = 'endpoint-token';
    protected $tokenGrantType = '';
    protected $credentials = [
        'urlAuthorize' => '',
        'urlResourceOwnerDetails' => '',
    ];

    /**
     * Instance variables
     *
     * @var \GuzzleHttp\Client|null $curlClient Provide a custom client
     * @var \GuzzleHttp\Psr7\Response|null $curlResponse Provide a custom response
     * @var \League\OAuth2\Client\Provider\GenericProvider|null $authProvider Provide a custom Oauth2 Provider
     */
    protected $curlClient = null;
    protected $curlResponse = null;
    protected $authProvider = null;

    /**
     * RequestHandler constructor.
     *
     * @param mixed[] $config The config data for making requests to an endpoint
     */
    public function __construct(array $config = [])
    {
        // Retrieve all the properties of this class so they can be populated lazily
        foreach (get_class_vars(__CLASS__) as $classMemberName => $default) {
            // Set this property to the incoming form data otherwise, use the default value
            $this->{$classMemberName} = is_array($this->{$classMemberName})
                ? $default + $config[$classMemberName] ?? []
                : $config[$classMemberName] ?? $default;
        }
        if (!is_null($this->curlClient)) {
            $config = $this->curlClient->getConfig();
            $config['verify'] = false;
            $this->curlClient = new Client($config);
        }
        if (!empty($this->baseUrl) && strpos($this->endpointUrl, $this->baseUrl) !== 0) {
            // Prepend the base URL to the endpoint URL
            $this->endpointUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($this->endpointUrl, '/');
        }
        // Format the stored credentials and apply to this
        $this->credentials = array_reduce(
            array_keys($this->credentials),
            function (array $credentials, string $name): array {
                if (strpos($name, 'url') === 0) {
                    // All credentials with keys preceded with 'url' must have the baseUrl prepended
                    $credentials[$name] = rtrim($this->baseUrl, '/')
                        . '/'
                        . ltrim($credentials[$name], '/');
                }
                if (preg_match('/^ENV:\{.*\}$/', $credentials[$name])) {
                    // TODO: use some mechanism to store credentials
                    $configKey = preg_replace("/^ENV:\{(.*)\}$/", "$1", $credentials[$name]);
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
     * @param null|mixed[] $requestDetails An array of settings to be used in the request
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
    ) {
        /**
         * Pre-loaded with config and base URL, this function will take the array of request details having endpointUrl
         * defined as one of the elements and return an API Handler.
         *
         * @param mixed[] $requestDetails An array of settings to be used in the request
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
            return new RequestHandler([
                'tokenCache' => $config['tokenCache'] ?? 'cache-endpoint-token',
                'tokenGrantType' => $config['grantType'] ?? '',
                'credentials' => $config['credentials'] ?? [],
                'baseUrl' => $baseUrl,
                'method' => $requestDetails['method'] ?? 'GET',
                'endpointUrl' => $endpointUrl,
                'headers' => $requestDetails['headers']
                    ?? ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'curlClient' => $requestDetails['curlClient'] ?? null,
            ]);
        };
        return (!is_array($requestDetails) || !is_string($requestDetails['endpointUrl'] ?? null))
            ? $preparedHandler
            : $preparedHandler($requestDetails);
    }

    /**
     * Submit the submission data to the correct API url to be stored, return any responses from the API server.
     *
     * @param mixed[] $requestSettings Includes settings such as request payload, withAuthorization, and renewToken
     *
     * @return Response
     */
    public function completeRequest(array $requestSettings = []): Response
    {
        $submitData = $requestSettings['submitData'] ?? false;
        $renewToken = $requestSettings['renewToken'] ?? false;
        $authorize = $requestSettings['authorize'] ?? true;
        $options = [RequestOptions::HEADERS => $this->headers];
        if ($submitData) {
            // When there is submit data, add the json content type and the submission data
            $options[RequestOptions::HEADERS]['Content-Type'] = 'application/json';
            $options[RequestOptions::JSON] = $submitData;
        }
        // Attempt to retrieve an access token, add to headers on success
        if ($accessToken = ($authorize ? $this->authorizeConnection($renewToken) : false)) {
            $options[RequestOptions::HEADERS]['Authorization'] = $accessToken;
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
     */
    protected function authorizeConnection(bool $renew = false): string
    {
        $token = '';
        // TODO: Get token from cache using $this->tokenCache key

        // If the token is empty or renew flag is set then generate a new token
        if ((!$token || $renew) && ($this->credentials['urlAccessToken'] ?? false)) {
            $provider = $this->authProvider ?? new \League\OAuth2\Client\Provider\GenericProvider([
                    'options' => $this->credentials,
                    'collaborators' => [
                        'httpClient' => $this->curlClient ?? App::make('\GuzzleHttp\Client', [
                                'config' => ['verify' => false],
                            ]),
                    ],
                ]);
            try {
                // Try to get an access token using the client credentials grant.
                $tokenResult = $provider->getAccessToken($this->tokenGrantType);
                $token = $tokenResult->getToken();
                // TODO: Set token into the cache using $this->tokenCache key, $token, and $tokenResult->getExpires()
            } catch (RequestException | IdentityProviderException $e) {
                // Log Message: 'Unable to reach the specified endpoint'
                // Log Details: [
                //                    'Endpoint URL' => $this->baseUrl,
                //                    'RequestException' => $e,
                //                ]
            }
        }

        return $token;
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
        $client = $this->curlClient ?? new \GuzzleHttp\Client(['verify' => false]);
        $response = $this->curlResponse ?? new \GuzzleHttp\Psr7\Response(
                503,
                [],
                'Invalid submission destination.'
            );
        // Attempt to connect to remote resource, log in case of failure
        try {
            $response = $client->request($method, $url, $options);
        } catch (RequestException | GuzzleException $e) {
            // Log Message: 'Unable to reach the specified endpoint'
            // Log Details: [
            //                'Endpoint URL' => $url,
            //                'Request Method' => $method,
            //                'Options' => $options,
            //                'RequestException' => $e
            //            ]
        }
        return $response;
    }
}
