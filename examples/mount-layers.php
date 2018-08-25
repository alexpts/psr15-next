<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\Events\Events;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\PSR15\Middlewares\ResponseEmit;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;

require_once '../vendor/autoload.php';

$responseEmitter = require 'include/ResponseEmitter.php';
$layerResolver = new LayerResolver;
$events = new Events;

$apiV1 = new Router($layerResolver);
$apiV2 = new Router($layerResolver);

$apiV2->getStore()->get('/users/', function (ServerRequestInterface $request, $next) {
    return new JsonResponse(['message' => 'api users']);
});

$apiV1->getStore()
    ->middleware(new ResponseEmit($responseEmitter))
    ->get('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'app']);
    });

$apiV1->mount($apiV2, '/api');

$apiV1->getStore()->use(function (ServerRequestInterface $request, $next) {
    return new JsonResponse(['message' => 'otherwise']);
});


$request = ServerRequestFactory::fromGlobals();
//$request = new ServerRequest([], [], '/api/users/');
$response = $apiV1->handle($request);

