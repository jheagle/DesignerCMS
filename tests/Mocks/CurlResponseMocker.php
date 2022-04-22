<?php

namespace Tests\Mocks;

use Core\Adaptors\Vendor\Curl\Client;
use Core\Adaptors\Vendor\Curl\Exceptions\RequestException;
use Core\Adaptors\Vendor\Curl\HandlerStack;
use Core\Adaptors\Vendor\Curl\Mocks\Handler;
use Core\Adaptors\Vendor\Curl\Response;

/**
 * Trait GuzzleResponseMocker
 *
 * Provide helper methods for generating responses which can return a guzzle client with mocked handler.
 * The client produced will use all of the responses provided instead of actual connections.
 *
 * @package Tests\Mocks
 */
class CurlResponseMocker
{
    /**
     * Get mocked Guzzle client. Define the expected response or responses if there should be multiple
     * check points.
     *
     * @param Response[]|RequestException[] $responses Provide an array of all expected responses in the order they
     *     will be encountered
     *
     * @return Client
     */
    public static function createMockClient(array $responses = []): Client
    {
        Client::reset();
        Handler::reset();
        HandlerStack::reset();
        return Client::instantiate()
            ->with(
                [
                    'handler' => HandlerStack::create(
                    // Provide the desired responses as a stack (array) to be processed
                        Handler::instantiate()->with($responses)->build()
                    ),
                ]
            )
            ->build();
    }

    /**
     * Return a Guzzle Response from an associative array of parameters
     *
     * @param array $config A keyed array corresponding to the Guzzle Response parameters (status, headers, body)
     *
     * @return Response
     */
    public static function createResponse(array $config = []): Response
    {
        Response::reset();
        return Response::instantiate()
            ->with(
                [
                    'status' => $config['status'] ?? 200,
                    'headers' => $config['headers'] ?? [],
                    'body' => $config['body'] ?? 'OK',
                ]
            )
            ->build();
    }
}
