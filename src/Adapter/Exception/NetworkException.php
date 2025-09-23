<?php

namespace Borsch\Http\Adapter\Exception;

use Exception;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class NetworkException extends Exception implements NetworkExceptionInterface
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
