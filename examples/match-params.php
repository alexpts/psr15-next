<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\Events\Events;
use PTS\NextRouter\Extra\HttpContext;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\NextRouter\Runner;
use PTS\PSR15\Middlewares\ResponseEmit;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;

require_once '../vendor/autoload.php';

$responseEmitter = require 'include/ResponseEmitter.php';
$layerResolver = new LayerResolver;
$events = new Events;

$events->on(Runner::EVENT_BEFORE_NEXT, function (ServerRequestInterface $request, Runner $runner){
    /** @var HttpContext $context */
    $context = $request->getAttribute('context');
    $context->replaceState('params', $runner->getCurrentLayer()->matches);
});

$app = new Router($layerResolver);

$app->getStore()
    ->middleware(new ResponseEmit($responseEmitter))
    ->get('/', function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'app']);
    })
    ->get('/users/{id}/', function (ServerRequestInterface $request, $next) {
        /** @var HttpContext $context */
        $context = $request->getAttribute('context');
        $params = $context->getState('params');
        return new JsonResponse(['message' => 'user: ' . $params['id'] ?? null]);
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
//$request = new ServerRequest([], [], '/api/users/34/');
$response = $app->handle($request);

