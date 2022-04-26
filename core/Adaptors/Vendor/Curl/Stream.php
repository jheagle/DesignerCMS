<?php

namespace Core\Adaptors\Vendor\Curl;

use Core\Adaptors\Adaptor;

/**
 * Class Stream
 *
 * @package Core\Adaptors\Vendor\Curl
 *
 * @property \GuzzleHttp\Psr7\Stream $classInstance
 */
class Stream extends Adaptor implements StreamInterface
{
    /**
     * Retrieve all of the contents of this data stream at once.
     *
     * @return bool|string
     */
    final public function getContents(): bool|string
    {
        return $this->classInstance->getContents();
    }
}