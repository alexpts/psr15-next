# Next

[![Build Status](https://travis-ci.org/alexpts/psr15-next.svg?branch=master)](https://travis-ci.org/alexpts/psr15-next)
[![Code Coverage](https://scrutinizer-ci.com/g/alexpts/psr15-next/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/alexpts/psr15-next/?branch=master)
[![Code Climate](https://codeclimate.com/github/alexpts/psr15-next/badges/gpa.svg)](https://codeclimate.com/github/alexpts/psr15-next)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alexpts/psr15-next/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alexpts/psr15-next/?branch=master)


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
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;
use PTS\PSR15\Middlewares\ErrorToJsonResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

require_once '../vendor/autoload.php';

$app = new Next;

$app->getStoreLayers()
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
