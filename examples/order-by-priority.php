<?php
declare(strict_types=1);

use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\ParserPsr7\SapiEmitter;
use PTS\Psr7\Factory\Psr17Factory;
use PTS\Psr7\Response\JsonResponse;

require_once '../vendor/autoload.php';

$psr17Factory = new Psr17Factory;
$app = new Next(new LayerResolver);

$app->getRouterStore()
    ->use(function () {
        return new JsonResponse(['message' => 'otherwise']);
    }, ['priority' => 100]) // will run after route
    ->get('/', function (){
        return new JsonResponse(['message' => 'handler']);
    }); // default priority = 50

$request = $psr17Factory->fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

