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

$app = new Router($layerResolver, $events);
$api = new Router($layerResolver, $events);

$api->get('/users/', function (ServerRequestInterface $request, $next) {
    return new JsonResponse(['message' => 'api users']);
});

$app
    ->middleware(new ResponseEmit($responseEmitter))
    ->get('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'app']);
    })
    ->mount($api, '/api')
    ->use($api, '/api')
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });


$request = ServerRequestFactory::fromGlobals();
//$request = new ServerRequest([], [], '/api/users/');
$response = $app->handle($request);

