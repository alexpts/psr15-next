<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Next;
use PTS\ParserPsr7\SapiEmitter;
use PTS\Psr7\Factory\Psr17Factory;
use PTS\Psr7\Response\JsonResponse;

require_once '../vendor/autoload.php';

$psr17Factory = new Psr17Factory;

$apiV1 = new Next;
$apiV2 = new Next;

$apiV2->getRouterStore()
    ->get('/users/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'api users']);
    });

$apiV1->getRouterStore()
    ->get('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'app']);
    });

$apiV1->mount($apiV2, '/api');

$apiV1->getRouterStore()
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = $psr17Factory->fromGlobals();
$response = $apiV1->handle($request);
(new SapiEmitter)->emit($response);

