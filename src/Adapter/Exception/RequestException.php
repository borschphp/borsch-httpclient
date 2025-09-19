<?php

namespace Borsch\Http\Adapter\Exception;

use Exception;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class RequestException extends Exception implements RequestExceptionInterface
{

    public function __construct(
        string $message,
        int $code = 0,
        Throwable $previous = null,
        protected RequestInterface $request
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public static function fromCurlError($message, $code, RequestInterface $request): self
    {
        return new self($message, $code, request: $request);
    }
}
