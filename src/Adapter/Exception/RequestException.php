<?php

namespace Borsch\Http\Adapter\Exception;

use Exception;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class RequestException extends Exception implements RequestExceptionInterface
{

    public function __construct(
        protected RequestInterface $request,
        string $message,
        int $code = 0,
        Throwable|null $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public static function fromCurlError(string $message, int $code, RequestInterface $request): self
    {
        return new self($request, $message, $code);
    }
}
