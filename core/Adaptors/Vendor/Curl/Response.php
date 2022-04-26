<?php

namespace Core\Adaptors\Vendor\Curl;

use Core\Adaptors\Adaptor;

/**
 * Class Response
 *
 * @package Core\Adaptors\Vendor\Curl
 *
 * @property \GuzzleHttp\Psr7\Response $classInstance
 */
class Response extends Adaptor implements ResponseInterface
{
    /**
     * Retrieve the HTTP status code returned from the request.
     *
     * @return int
     */
    final public function getStatusCode(): int
    {
        return $this->classInstance->getStatusCode();
    }

    /**
     * Retrieve the body returned from the request, returns a data stream.
     *
     * @return Stream
     */
    final public function getBody(): Stream
    {
        return Stream::wrapCast($this->classInstance->getBody());
    }
}