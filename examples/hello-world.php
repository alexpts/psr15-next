<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\ParserPsr7\SapiEmitter;
use PTS\Psr7\Factory\Psr17Factory;
use PTS\Psr7\Response\JsonResponse;

require_once '../vendor/autoload.php';

$psr17Factory = new Psr17Factory;

$app = new Next(new LayerResolver);

$app->getRouterStore()
    ->get('/users', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'Hello world'], 200);
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = $psr17Factory->fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

