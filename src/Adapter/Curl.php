<?php

namespace Borsch\Http\Adapter;

use Borsch\Http\Adapter\Exception\CurlAdapterException;
use Borsch\Http\Adapter\Exception\NetworkException;
use Borsch\Http\Adapter\Exception\RequestException;
use CurlHandle;
use Psr\Http\Message\{RequestInterface, ResponseFactoryInterface, ResponseInterface, StreamFactoryInterface};

class Curl implements AdapterInterface
{

    protected ?CurlHandle $curl = null;

    protected array $curl_constants = [];
    protected array $curl_constants_to_ignore = [
        CURLOPT_URL,
        CURLOPT_CUSTOMREQUEST,
        CURLOPT_HTTP_VERSION,
        CURLOPT_POSTFIELDS,
        CURLOPT_NOBODY,
        CURLOPT_HTTPAUTH,
        CURLOPT_USERPWD
    ];
    protected array $curl_network_error_codes = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_RESOLVE_PROXY,
        CURLE_COULDNT_CONNECT,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_SEND_ERROR,
        CURLE_RECV_ERROR,
        CURLE_GOT_NOTHING,
        CURLE_SSL_CONNECT_ERROR,
    ];
    protected array $curl_request_error_codes = [
        CURLE_TOO_MANY_REDIRECTS,
        CURLE_UNSUPPORTED_PROTOCOL,
        CURLE_URL_MALFORMAT,
        CURLE_SSL_CERTPROBLEM,
        CURLE_SSL_CACERT
    ];

    /** @var array<int, mixed> $options */
    protected array $options = [];

    protected ?RequestInterface $request = null;
    protected ?ResponseInterface $response = null;

    public function __construct(
        private ResponseFactoryInterface $response_factory,
        private StreamFactoryInterface $stream_factory,
    ) {
        $this->curl_constants = array_filter(
            get_defined_constants(true)['curl'] ?? [],
            fn($key) => str_starts_with($key, 'CURLOPT_'),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param array<int, mixed> $options
     * @throws CurlAdapterException
     */
    public function setOptions(array $options = []): void
    {
        foreach (array_keys($options) as $key) {
            if (!in_array($key, $this->curl_constants, true)) {
                throw CurlAdapterException::invalidOption($key);
            }
        }

        $this->options = array_filter(
            $options,
            fn(int $key) => !in_array($key, $this->curl_constants_to_ignore, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @throws CurlAdapterException
     */
    public function connect(RequestInterface $request): void
    {
        if ($this->curl instanceof CurlHandle) {
            $this->close();
        }

        $this->curl = curl_init();
        if ($this->curl === false) {
            throw CurlAdapterException::initializeFailed();
        }

        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_URL => (string)$request->getUri(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => match ($request->getProtocolVersion()) {
                    '1.0', '1' => CURL_HTTP_VERSION_1_0,
                    '1.1' => CURL_HTTP_VERSION_1_1,
                    '2.0', '2' => CURL_HTTP_VERSION_2_0,
                    default => CURL_HTTP_VERSION_NONE,
                },
                CURLOPT_CUSTOMREQUEST => $request->getMethod(),
                CURLOPT_HTTPHEADER => array_map(
                    fn($name, $value) => "$name: $value",
                    array_keys($request->getHeaders()),
                    array_map(fn($values) => implode(', ', $values), $request->getHeaders())
                ),
                CURLOPT_NOBODY => in_array($request->getMethod(), ['HEAD', 'OPTIONS'], true),
            ] + $this->options
        );

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            $body = (string)$request->getBody();
            if ($body !== '') {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
            }
        }

        if ($request->hasHeader('Authorization') &&
            str_starts_with($request->getHeaderLine('Authorization'), 'Basic ')) {
            curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->curl, CURLOPT_USERPWD, substr($request   ->getHeaderLine('Authorization'), 6));
        }

        // Save the request for later use
        $this->request = $request;
    }

    /**
     * @throws CurlAdapterException
     * @throws NetworkException
     * @throws RequestException
     */
    public function send(): void
    {
        if (!$this->curl instanceof CurlHandle) {
            throw CurlAdapterException::sessionNotInitialized();
        }

        $response = curl_exec($this->curl);
        if ($response === false) {
            $this->throwException();
            throw new CurlAdapterException(curl_error($this->curl), curl_errno($this->curl));
        }

        $status_code = curl_getinfo($this->curl, CURLINFO_RESPONSE_CODE);
        $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $this->response = $this->response_factory->createResponse($status_code);
        $this->response = $this->response->withBody($this->stream_factory->createStream($body));

        foreach (explode("\r\n", $headers) as $header_line) {
            if (str_contains($header_line, ':')) {
                [$name, $value] = explode(':', $header_line, 2);
                $this->response = $this->response->withAddedHeader(trim($name), trim($value));
            }
        }
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function close(): void
    {
        if ($this->curl instanceof CurlHandle) {
            curl_close($this->curl);
        }

        $this->curl = null;
        $this->request = null;
        $this->response = null;
    }

    protected function throwException(): void
    {
        $errno = curl_errno($this->curl);
        $error = curl_error($this->curl);

        if (in_array($errno, $this->curl_network_error_codes, true)) {
            throw NetworkException::fromCurlError($error, $errno, $this->request);
        }

        if (in_array($errno, $this->curl_request_error_codes, true)) {
            throw RequestException::fromCurlError($error, $errno, $this->request);
        }
    }
}
