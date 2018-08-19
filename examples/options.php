<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\Events\Events;
use PTS\NextRouter\Extra\OptionsMiddleware;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\PSR15\Middlewares\ResponseEmit;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

require_once '../vendor/autoload.php';
$responseEmitter = require 'include/ResponseEmitter.php';
$resolver = new LayerResolver;
$app = new Router($resolver, new Events);

$app
    ->middleware(new ResponseEmit($responseEmitter))
    ->get('/api/users/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => "fetch('/api/users/', {method: 'OPTIONS'}).then(response => console.log(response.headers.get(\"Access-Control-Allow-Methods\")))"]);
    })
    ->post('/api/users/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'post /api/users/']);
    })
    ->middleware(new OptionsMiddleware($app, $resolver))
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
//$request = new ServerRequest([], [], '/api/users/', 'OPTIONS');
$response = $app->handle($request);

