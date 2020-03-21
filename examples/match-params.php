<?php
declare(strict_types=1);

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use PTS\Events\Events;
use PTS\NextRouter\Extra\HttpContext;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\Runner;

require_once '../vendor/autoload.php';

$layerResolver = new LayerResolver;
$events = new Events;

$events->on(Runner::EVENT_BEFORE_NEXT, function (ServerRequestInterface $request, Runner $runner){
    /** @var HttpContext $context */
    $context = $request->getAttribute('context');
    $context->replaceState('params', $runner->getCurrentLayer()->matches);
});

$app = new Next($layerResolver);

$app->getRouterStore()
    ->get('/', fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'app']))
    ->get('/users/{id}/', function (ServerRequestInterface $request, $next) {
        /** @var HttpContext $context */
        $context = $request->getAttribute('context');
        $params = $context->getState('params');
        return new JsonResponse(['message' => 'user: ' . $params['id'] ?? null]);
    })
    ->use(fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'otherwise']));

$request = ServerRequestFactory::fromGlobals(); // /api/users/34/
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

