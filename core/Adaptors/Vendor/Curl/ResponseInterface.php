<?php

namespace Core\Adaptors\Vendor\Curl;

interface ResponseInterface
{
    /**
     * Retrieve the HTTP status code returned from the request.
     *
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * Retrieve the body returned from the request, returns a data stream.
     *
     * @return Stream
     */
    public function getBody(): Stream;
}