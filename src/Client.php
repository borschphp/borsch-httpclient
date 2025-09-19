<?php

namespace Borsch\Http;

use Borsch\Http\Adapter\AdapterInterface;
use Borsch\Http\Exception\ClientException;
use Psr\Http\Client\{ClientInterface, NetworkExceptionInterface, RequestExceptionInterface};
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Throwable;

readonly class Client implements ClientInterface
{

    public function __construct(
        private AdapterInterface $adapter,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $this->adapter->connect($request);
            $this->adapter->send();

            return $this->adapter->getResponse();
        } catch (NetworkExceptionInterface|RequestExceptionInterface $e) {
            throw $e;
        } catch (Throwable $throwable) {
            throw ClientException::unableToSendRequest($request, $throwable);
        }
    }
}
