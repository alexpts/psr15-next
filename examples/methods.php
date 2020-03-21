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
    ->get('/', fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'GET /']))
    ->post('/', fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'POST /']))
    ->delete('/', fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'DELETE /']))
    ->put('/', fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'PUT /']))
    ->patch('/', fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'PATCH /']))
    ->use(fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'otherwise']));

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);
