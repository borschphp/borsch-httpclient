<?php

use Borsch\Http\Adapter\Exception\{NetworkException, RequestException};
use Borsch\Http\Client;
use Borsch\Http\Adapter\AdapterInterface;
use Borsch\Http\Exception\ClientException;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Client\{NetworkExceptionInterface, RequestExceptionInterface};

covers(Client::class);

it('sends request and returns response', function () {
    $request = mock(RequestInterface::class);
    $response = mock(ResponseInterface::class);

    $adapter = mock(AdapterInterface::class);
    $adapter->shouldReceive('connect')->once()->with($request);
    $adapter->shouldReceive('send')->once();
    $adapter->shouldReceive('getResponse')->once()->andReturn($response);

    $client = new Client($adapter);
    expect($client->sendRequest($request))->toBe($response);
});

it('throws network exception if adapter throws NetworkExceptionInterface', function () {
    $request = mock(RequestInterface::class);

    $adapter = mock(AdapterInterface::class);
    $adapter->shouldReceive('connect')->andThrow(NetworkException::fromCurlError('error', 28, $request));

    $client = new Client($adapter);

    $this->expectException(NetworkExceptionInterface::class);
    $this->expectExceptionCode(28);
    $this->expectExceptionMessage('error');

    $client->sendRequest($request);
});

it('throws request exception if adapter throws RequestExceptionInterface', function () {
    $request = mock(RequestInterface::class);

    $adapter = mock(AdapterInterface::class);
    $adapter->shouldReceive('connect')->andThrow(RequestException::fromCurlError('error', 28, $request));

    $client = new Client($adapter);

    $this->expectException(RequestExceptionInterface::class);
    $this->expectExceptionCode(28);
    $this->expectExceptionMessage('error');

    $client->sendRequest($request);
});

it('throws ClientException for other throwables', function () {
    $request = mock(RequestInterface::class);
    $request->shouldReceive('getMethod')->twice()->andReturn('GET');
    $request->shouldReceive('getUri')->twice()->andReturn(new Uri('https://example.com')) ;

    $adapter = mock(AdapterInterface::class);
    $adapter->shouldReceive('connect')->andThrow(
        ClientException::unableToSendRequest($request, new Exception('exception', 42))
    );

    $client = new Client($adapter);

    $this->expectException(ClientException::class);
    $this->expectExceptionCode(0);
    $this->expectExceptionMessage(sprintf(
        'Unable to send request %s %s, got: %s',
        'GET',
        'https://example.com',
        'exception'
    ));

    $client->sendRequest($request);
});
