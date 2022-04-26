<?php

namespace Core\Adaptors\Vendor\Curl;

interface StreamInterface
{
    /**
     * Retrieve all of the contents of this data stream at once.
     *
     * @return bool|string
     */
    public function getContents(): bool|string;
}