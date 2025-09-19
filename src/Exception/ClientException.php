<?php

namespace Borsch\Http\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class ClientException extends Exception implements ClientExceptionInterface
{

    public static function unableToSendRequest(RequestInterface $request, Throwable $throwable): self
    {
        return new self(
            sprintf('Unable to send request %s %s, got: %s',
                $request->getMethod(),
                (string)$request->getUri(),
                $throwable->getMessage()
            ),
            previous: $throwable
        );
    }
}
