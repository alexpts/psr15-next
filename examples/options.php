<?php
declare(strict_types=1);

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Extra\OptionsMiddleware;
use PTS\NextRouter\Next;

require_once '../vendor/autoload.php';
$app = new Next;

$app->getStoreLayers()
    ->get('/api/users/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => "fetch('/api/users/', {method: 'OPTIONS'}).then(response => console.log(response.headers.get(\"Access-Control-Allow-Methods\")))"]);
    })
    ->post('/api/users/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'post /api/users/']);
    })
    ->middleware(new OptionsMiddleware($app))
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals(); // '/api/users/', 'OPTIONS'
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

