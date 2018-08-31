<?php
declare(strict_types=1);

use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\PSR15\Middlewares\ResponseEmit;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;

require_once '../vendor/autoload.php';

$app = new Router(new LayerResolver);
$responseEmitter = require 'include/ResponseEmitter.php';

$app->getStore()
    ->middleware(new ResponseEmit($responseEmitter))
    ->use(function () {
        return new JsonResponse(['message' => 'otherwise']);
    }, ['priority' => 100]) // will run after route
    ->get('/', function (){
        return new JsonResponse(['message' => 'handler']);
    }); // default priority = 50

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);

