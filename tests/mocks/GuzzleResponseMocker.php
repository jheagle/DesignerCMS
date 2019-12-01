<?php

namespace Core\Tests\Mocks;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Trait GuzzleResponseMocker
 *
 * Provide helper methods for generating responses which can return a guzzle client with mocked handler.
 * The client produced will use all of the responses provided instead of actual connections.
 *
 * @package Core\Tests\Mocks
 */
class GuzzleResponseMocker
{
    /**
     * Get mocked Guzzle client. Define the expected response or responses if there should be multiple
     * check points.
     *
     * @param Response[] $responses Provide an array of all expected responses in the order they will be
     *     encountered
     *
     * @return Client
     */
    static public function createMockClient(array $responses = []): Client
    {
        return new Client([
            'handler' => HandlerStack::create(
            // Provide the desired responses as a stack (array) to be processed
                new MockHandler($responses)
            ),
        ]);
    }

    /**
     * Return a Guzzle Response from an associative array of parameters
     *
     * @param array $config A keyed array corresponding to the Guzzle Response parameters (status, headers, body)
     *
     * @return Response
     */
    static public function createResponse(array $config = [])
    {
        return new Response(
            $config['status'] ?? 200,
            $config['headers'] ?? [],
            $config['body'] ?? 'OK'
        );
    }
}
