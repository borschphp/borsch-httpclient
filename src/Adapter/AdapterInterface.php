<?php

namespace Borsch\Http\Adapter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface AdapterInterface
{

    /**
     * Set the configuration array for the adapter
     *
     * @param array<int, mixed> $options
     */
    public function setOptions(array $options = []): void;

    /**
     * Establish a connection using the adapter
     */
    public function connect(RequestInterface $request): void;

    /**
     * Send a request using the adapter
     */
    public function send(): void;

    /**
     * Get the response from the adapter
     */
    public function getResponse(): ResponseInterface;

    /**
     * Close the connection using the adapter
     */
    public function close(): void;
}
