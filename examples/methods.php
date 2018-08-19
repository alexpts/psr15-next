<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\Events\Events;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\PSR15\Middlewares\ResponseEmit;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

require_once '../vendor/autoload.php';

$app = new Router(new LayerResolver, new Events);
$responseEmitter = require 'include/ResponseEmitter.php';

$app
    ->middleware(new ResponseEmit($responseEmitter))
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
//$request = new ServerRequest([], [], '/');
$response = $app->handle($request);

