<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\PSR15\Middlewares\ErrorToJsonResponse;
use PTS\PSR15\Middlewares\ResponseEmit;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;

require_once '../vendor/autoload.php';

$app = new Router(new LayerResolver);
$responseEmitter = require 'include/ResponseEmitter.php';

$app->getStore()
    ->middleware(new ResponseEmit($responseEmitter))
    ->middleware(new ErrorToJsonResponse(true), '/api/.*') // middelware active only /api/* path
    ->get('/api/users/', function (ServerRequestInterface $request, $next) {
        throw new \Exception('Exception text - will convert to json response');
    })
    ->get('/users/', function (ServerRequestInterface $request, $next) {
        throw new \Exception('Exception text - raw');
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
//$request = new ServerRequest([], [], '/api/users/');
$response = $app->handle($request);

