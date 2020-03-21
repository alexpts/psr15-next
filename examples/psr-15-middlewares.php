<?php
declare(strict_types=1);

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\PSR15\Middlewares\ErrorToJsonResponse;

require_once '../vendor/autoload.php';

$app = new Next(new LayerResolver);

$app->getRouterStore()
    ->middleware(new ErrorToJsonResponse(true), '/api/.*') // middelware active only /api/* path
    ->get('/api/users/', function (ServerRequestInterface $request, $next) {
        throw new Exception('Exception text - will convert to json response');
    })
    ->get('/users/', function (ServerRequestInterface $request, $next) {
        throw new Exception('Exception text - raw');
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

