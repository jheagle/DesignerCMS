<?php

namespace Core\Controllers\Api;

use Core\Objects\DataTransferObject;

/**
 * Class RequestDataSettings
 *
 * @package Core\Controllers\Api
 */
class RequestDataSettings extends DataTransferObject
{
    public bool $authorize = true;
    public bool $renewToken = false;
    public mixed $submitData = null;
}