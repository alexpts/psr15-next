<?php
declare(strict_types=1);

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;

require_once '../vendor/autoload.php';

$app = new Next(new LayerResolver);

$app->getRouterStore()
    ->get('/users', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'Hello world'], 200);
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

