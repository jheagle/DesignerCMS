<?php

namespace Core\Controllers\Api;

use ArgumentCountError;
use Core\Adaptors\Config;
use Core\Adaptors\Vendor\CacheRegistry\CacheRegistry;
use Core\Adaptors\Vendor\Curl\Exceptions\RequestException;
use Core\Adaptors\Vendor\Curl\Request;
use Core\Adaptors\Vendor\Logger\Logger;
use Psr\Cache\InvalidArgumentException;
use Tests\Mocks\CurlResponseMocker;
use Tests\mocks\GenericClass;
use Tests\TestCase;
use Throwable;
use TypeError;

/**
 * Class RequestHandlerTest
 *
 * @package Core\Controllers\Api
 *
 * @small
 *
 * @group Unit
 * @group Controllers
 * @group Api
 * @group RequestHandler
 */
class RequestHandlerTest extends TestCase
{
    /**
     * Create a simple request
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testCreateAndCompleteRequest()
    {
        $client = CurlResponseMocker::createMockClient(
            [
                CurlResponseMocker::createResponse(['body' => 'Created', 'status' => 201]),
            ]
        );
        $response = (new RequestHandler(
            [
                'baseUrl' => '',
                'tokenCache' => 'cache-endpoint-token',
                'headers' => ['Accept' => 'application/json'],
                'endpointUrl' => '',
                'tokenGrantType' => '',
                'credentials' => [],
                'curlClient' => $client,
            ]
        ))
            ->completeRequest(
                [
                    'submitData' => (object)['someProperty' => 'someValue'],
                    'authorize' => false,
                ]
            );
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Created', $response->getBody()->getContents());
    }

    public function testCreateFetchToken()
    {
        // Ensure the token to be used is cleared
        // TODO: Forget any stored tokens
        $client = CurlResponseMocker::createMockClient(
            [
                CurlResponseMocker::createResponse(
                    [
                        'status' => 200,
                        'body' => '{"token_type":"Bearer","expires_in":10,"access_token":"token.place.holder"}',
                    ]
                ),
            ]
        );
        $response = (new RequestHandler(
            [
                'baseUrl' => '',
                'tokenCache' => 'cache-endpoint-token',
                'headers' => ['Accept' => 'application/json'],
                'endpointUrl' => '',
                'tokenGrantType' => '',
                'credentials' => [],
                'curlClient' => $client,
            ]
        ))
            ->completeRequest();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            '{"token_type":"Bearer","expires_in":10,"access_token":"token.place.holder"}',
            $response->getBody()->getContents()
        );
    }

    /**
     */
    public function testCreateFetchTokenThrowException()
    {
        $client = CurlResponseMocker::createMockClient(
            [
                new RequestException(
                    'Error Communicating with Server',
                    Request::instantiate()->with('GET', 'test')->build()
                ),
                new RequestException(
                    'Error Communicating with Server',
                    Request::instantiate()->with('GET', 'test')->build()
                ),
                new RequestException(
                    'Error Communicating with Server',
                    Request::instantiate()->with('GET', 'test')->build()
                ),
                new RequestException(
                    'Error Communicating with Server',
                    Request::instantiate()->with('GET', 'test')->build()
                ),
            ]
        );
        Config::set('logger.handlers', []);
        Logger::setResource(
            new GenericClass(
                [
                    'error' => function ($message) {
                        $this->assertEquals('Unable to reach the specified endpoint', $message);
                    },
                ]
            )
        );
        $response = (new RequestHandler(
            [
                'baseUrl' => '',
                'tokenCache' => 'cache-endpoint-token',
                'headers' => ['Accept' => 'application/json'],
                'endpointUrl' => '',
                'tokenGrantType' => '',
                'credentials' => [],
                'curlClient' => $client,
            ]
        ))
            ->completeRequest();
        $this->assertEquals(503, $response->getStatusCode());
        $this->assertEquals('Invalid submission destination.', $response->getBody()->getContents());
    }

    /**
     * Use the prepareApiHandler method to create a callable function.
     *
     * @return void
     */
    public function testPrepareApiHandlerReturnsCallable()
    {
        $createApiHandler = RequestHandler::prepareApiHandler();
        $createApiHandler2 = RequestHandler::prepareApiHandler(['someConfig' => 'The configuration'], 'someUrl');
        $this->assertIsCallable($createApiHandler);
        $this->assertIsCallable($createApiHandler2);
    }

    /**
     * Use the prepareApiHandler method to create a callable function and then try excluding or sending bad arguments.
     *
     * @return void
     */
    public function testPrepareApiHandlerReturnedFunctionWithBadArguments()
    {
        /**
         * Type def
         *
         * @var callable $createApiHandler The method returns a function with injected default configuration data
         */
        $createApiHandler = RequestHandler::prepareApiHandler();

        // Calling the returned function without the required array argument results in an exception
        $this->expectException(ArgumentCountError::class);
        $createApiHandler();

        // Calling the returned function with the wrong argument type (null) will throw an error
        $this->expectException(TypeError::class);
        $createApiHandler(null);

        // Not providing the 'endpointUrl' in the array or nor having 'endpointUrl' equal to a string will through an
        // InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $createApiHandler([]);
        $this->expectException(InvalidArgumentException::class);
        $createApiHandler(['endpointUrl' => 0]);
    }

    /**
     * Use the prepareApiHandler method to create a callable function and then send a 'good' argument which means that
     * there is an array and it contains a key of 'endpointUrl' which corresponds to a string value
     *
     * @return void
     */
    public function testPrepareApiHandlerReturnedFunctionWithGoodArguments()
    {
        /**
         * Type def
         *
         * @var callable $createApiHandler The method returns a function with injected default configuration data
         * @var RequestHandler $apiHandler The returned RequestHandler instance
         */
        $createApiHandler = RequestHandler::prepareApiHandler();
        $apiHandler = $createApiHandler(['endpointUrl' => 'someUrl']);
        $this->assertInstanceOf(RequestHandler::class, $apiHandler);
    }

    /**
     * Use the prepareApiHandler method and provide all the arguments, including the array of request details so that
     * it will immediately return an instance of RequestHandler.
     *
     * @return void
     */
    public function testPrepareApiHandlerWithAllParametersProvidedInitially()
    {
        $apiHandler = RequestHandler::prepareApiHandler([], 'someBaseUrl', ['endpointUrl' => 'someUrl']);
        $this->assertInstanceOf(RequestHandler::class, $apiHandler);
    }

    /**
     * Use the prepareApiHandler method to create a callable function and then try excluding or sending bad arguments.
     *
     * @return void
     */
    public function testPrepareApiHandlerWithBadParametersProvidedInitially()
    {
        $createApiHandler = RequestHandler::prepareApiHandler([], 'someBaseUrl', null);
        $createApiHandler2 = RequestHandler::prepareApiHandler([], 'someBaseUrl', []);
        $createApiHandler3 = RequestHandler::prepareApiHandler([], 'someBaseUrl', ['endpointUrl' => 0]);
        $this->assertIsCallable($createApiHandler);
        $this->assertIsCallable($createApiHandler2);
        $this->assertIsCallable($createApiHandler3);
    }

    /**
     * Create a client credentials OAuth request which will be validated.
     * This also confirms the implementation of the cache.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testCreatClientCredentialsAuthorizedRequest(): void
    {
        $tokenPrefix = 'cache-endpoint-token';
        CacheRegistry::reset($tokenPrefix);
        $client = CurlResponseMocker::createMockClient(
            [
                CurlResponseMocker::createResponse(
                    ['body' => '{"access_token":"some-token","expires":7258122000}', 'status' => 200]
                ),
                CurlResponseMocker::createResponse(['body' => 'Created', 'status' => 201]),
            ]
        );
        $response = (new RequestHandler(
            [
                'baseUrl' => '',
                'tokenCache' => $tokenPrefix,
                'headers' => ['Accept' => 'application/json'],
                'endpointUrl' => '',
                'tokenGrantType' => 'client-credentials',
                'credentials' => [
                    'urlAccessToken' => 'something',
                    'urlAuthorize' => 'another thing',
                    'urlResourceOwnerDetails' => 'fake',
                ],
                'curlClient' => $client,
            ]
        ))
            ->completeRequest(
                [
                    'submitData' => (object)['someProperty' => 'someValue'],
                ]
            );
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Created', $response->getBody()->getContents());
    }
}
