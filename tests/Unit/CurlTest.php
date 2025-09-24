<?php

use Borsch\Http\Adapter\Curl;
use Borsch\Http\Adapter\Exception\{AdapterException, CurlAdapterException};
use Laminas\Diactoros\Uri;
use Psr\Http\Message\{RequestInterface, ResponseFactoryInterface, ResponseInterface, StreamFactoryInterface};

covers(Curl::class);

beforeEach(function () {
    $this->responseFactory = mock(ResponseFactoryInterface::class);
    $this->streamFactory = mock(StreamFactoryInterface::class);
    $this->curl = new Curl($this->responseFactory, $this->streamFactory);
});

it('can be constructed', function () {
    expect($this->curl)->toBeInstanceOf(Curl::class);
});

it('throws on invalid option', function () {
    $this->curl->setOptions([999999 => 'foo']);
})->throws(CurlAdapterException::class);

it('closes without error if not initialized', function () {
    $this->curl->close();
    expect(true)->toBeTrue();
});

test('connect() sets up curl with request', function () {
    $request = mock(RequestInterface::class);
    $request->shouldReceive('getUri')->andReturn(new Uri('http://example.com'));
    $request->shouldReceive('getProtocolVersion')->andReturn('1.1');
    $request->shouldReceive('getMethod')->andReturn('GET');
    $request->shouldReceive('getHeaders')->andReturn([]);
    $request->shouldReceive('hasHeader')->andReturn(false);

    $this->curl->connect($request);
    expect($this->curl)->not->toBeNull();
});

test('send() throws if curl not initialized', function () {
    $this->curl->close();
    $this->curl->send();
})->throws(CurlAdapterException::class);

test('getResponse() throws if no response available', function () {
    $this->curl->getResponse();
})->throws(AdapterException::class);
