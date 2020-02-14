<?php
declare(strict_types=1);

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;

require_once '../vendor/autoload.php';

$app = new Next(new LayerResolver);

$app->getStoreLayers()
    ->use(function () {
        return new JsonResponse(['message' => 'otherwise']);
    }, ['priority' => 100]) // will run after route
    ->get('/', function (){
        return new JsonResponse(['message' => 'handler']);
    }); // default priority = 50

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

