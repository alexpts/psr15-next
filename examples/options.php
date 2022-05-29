<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Extra\OptionsMiddleware;
use PTS\NextRouter\Next;
use PTS\ParserPsr7\SapiEmitter;
use PTS\Psr7\Factory\Psr17Factory;
use PTS\Psr7\Response\JsonResponse;

require_once '../vendor/autoload.php';

$psr17Factory = new Psr17Factory;
$app = new Next;

$app->getRouterStore()
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

$request = $psr17Factory->fromGlobals(); // '/api/users/', 'OPTIONS'
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

