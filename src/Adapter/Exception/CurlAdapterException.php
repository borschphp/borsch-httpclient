<?php

namespace Borsch\Http\Adapter\Exception;

class CurlAdapterException extends AdapterException
{

    public static function initializeFailed(): self
    {
        return new self('Failed to initialize cURL session');
    }

    public static function invalidOption(int $key): self
    {
        return new self("Invalid cURL option: $key");
    }

    public static function sessionNotInitialized(): self
    {
        return new self('cURL session is not initialized. Call connect() first.');
    }
}
