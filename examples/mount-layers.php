<?php
declare(strict_types=1);

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Next;

require_once '../vendor/autoload.php';

$apiV1 = new Next;
$apiV2 = new Next;

$apiV2->getStoreLayers()
    ->get('/users/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'api users']);
    });

$apiV1->getStoreLayers()
    ->get('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'app']);
    });

$apiV1->mount($apiV2, '/api');

$apiV1->getStoreLayers()
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
$response = $apiV1->handle($request);
(new SapiEmitter)->emit($response);

