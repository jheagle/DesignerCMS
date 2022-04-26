<?php

namespace Core\Adaptors\Vendor\Curl;

use Throwable;

interface ClientInterface
{
    /**
     * Access the request method of this client and provide some exception handling.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     *
     * @return Response
     *
     * @throws Throwable
     */
    public function request(string $method, string $url, array $options): Response;
}