<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

require_once '../vendor/autoload.php';

$app = new Next(new LayerResolver);

$app->getStoreLayers()
    ->get('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'GET /']);
    })
    ->post('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'POST /']);
    })
    ->delete('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'DELETE /']);
    })
    ->put('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'PUT /']);
    })
    ->patch('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'PATCH /']);
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);
