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

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\Next;
use PTS\PSR15\Middlewares\ErrorToJsonResponse;

require_once '../vendor/autoload.php';

$app = new Next;

$app->getRouterStore()
    ->middleware(new ErrorToJsonResponse(true))
    ->get('/hello', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'Hello world'], 200);
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

```


### Install

`composer require alexpts/psr15-next`
