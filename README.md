# Next

[![phpunit](https://github.com/alexpts/psr15-next/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/alexpts/psr15-next/actions/workflows/phpunit.yml)
[![codecov](https://codecov.io/gh/alexpts/psr15-next/branch/master/graph/badge.svg?token=14L6IJA5UE)](https://codecov.io/gh/alexpts/psr15-next)


Runner for PSR-15 middlewares.


[See examples](https://github.com/alexpts/psr15-next/tree/master/examples)


* Named routes with URL generation
* Responds to `OPTIONS` requests with allowed methods
* Multiple route middleware
* Multiple routers
* Nestable routers
* PSR-15 middlewares
* PSR-7 request/response
* Flexible priority (low level)
* Dynamic endpoint (low level)


```php

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\Next;
use PTS\PSR15\Middlewares\ErrorToJsonResponse;
use PTS\ParserPsr7\SapiEmitter;
use PTS\Psr7\Factory\Psr17Factory;
use PTS\Psr7\Response\JsonResponse;

require_once '../vendor/autoload.php';

$psr17Factory = new Psr17Factory;
$app = new Next;

$app->getRouterStore()
    ->get('/hello', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'Hello world'], 200);
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = $psr17Factory->fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

```


### Install

`composer require alexpts/psr15-next`


### Todo: add more examples
