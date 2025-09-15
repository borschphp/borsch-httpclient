<?php

namespace Borsch\Http;

use Borsch\Http\Adapter\AdapterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

readonly class Client implements ClientInterface
{

    public function __construct(
        private AdapterInterface $adapter,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->adapter->connect($request);
        $this->adapter->send();

        return $this->adapter->getResponse();
    }
}
