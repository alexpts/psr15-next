<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\ParserPsr7\SapiEmitter;
use PTS\PSR15\Middlewares\ErrorToJsonResponse;
use PTS\Psr7\Factory\Psr17Factory;
use PTS\Psr7\Response\JsonResponse;

require_once '../vendor/autoload.php';

$psr17Factory = new Psr17Factory;
$app = new Next(new LayerResolver);

$app->getRouterStore()
    ->middleware(new ErrorToJsonResponse(500,true), ['path' => '/api/.*']) // middleware active only /api/* path
    ->get('/api/users/', function (ServerRequestInterface $request, $next) {
        throw new Exception('Exception text - will convert to json response');
    })
    ->get('/users/', function (ServerRequestInterface $request, $next) {
        throw new Exception('Exception text - raw');
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = $psr17Factory->fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

