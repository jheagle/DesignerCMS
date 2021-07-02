<?php

namespace Core\Adaptors\Vendor\Curl;

use Core\Adaptors\Adaptor;
use Throwable;

/**
 * Class Client
 *
 * @package Core\Adaptors\Vendor\Curl
 *
 * @property \GuzzleHttp\Client $classInstance
 */
class Client extends Adaptor
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
    public function request(string $method, string $url, array $options): Response
    {
        return $this->useThrowable(
            fn() => Response::wrapCast($this->classInstance->request($method, $url, $options))
        );
    }
}