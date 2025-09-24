# Borsch - PSR-18 HTTP Client

A minimalist PSR-18 implementation for making HTTP requests in PHP.

## Installation

The package can be installed via [Composer](https://getcomposer.org/).  
Run the following command:

```bash
composer require borschphp/http-client
```

## Usage

Here's a simple example of how to use the Borsch HTTP Client:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Borsch\Http\Client;
use Borsch\Http\Adapter\Curl;
use Laminas\Diactoros\{RequestFactory, ResponseFactory, StreamFactory};

$adapter = new Curl(new ResponseFactory(), new StreamFactory());
$client = new Client($adapter);

$request = (new RequestFactory())->createRequest(
    'GET',
    'https://jsonplaceholder.typicode.com/posts/1'
);

$response = $client->sendRequest($request);

echo $response->getBody();
```
