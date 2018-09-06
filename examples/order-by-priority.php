<?php
declare(strict_types=1);

use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

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

