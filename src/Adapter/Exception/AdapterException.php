<?php

namespace Borsch\Http\Adapter\Exception;

use Exception;

class AdapterException extends Exception
{

    public static function requestNotAvailable(): self
    {
        return new self('Request is not available. Did you forget to call send() ?');
    }

    public static function responseNotAvailable(): self
    {
        return new self('Response is not available. Did you forget to call send() ?');
    }

    public static function unknownError(string $error = '', int $code = 0): self
    {
        return new self("An unknown error occurred: $error", $code);
    }
}
