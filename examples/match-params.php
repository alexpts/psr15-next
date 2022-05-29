<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;

use PTS\Events\EventEmitter;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\Runner;
use PTS\ParserPsr7\SapiEmitter;
use PTS\Psr7\Factory\Psr17Factory;
use PTS\Psr7\Response\JsonResponse;

require_once '../vendor/autoload.php';

$psr17Factory = new Psr17Factory;

$layerResolver = new LayerResolver;
$events = new EventEmitter;

$events->on(Runner::EVENT_BEFORE_NEXT, function (ServerRequestInterface $request, Runner $runner){
    $request->withAttribute('my-params', $runner->getCurrentLayer()->matches);
});

$app = new Next($layerResolver);
$app->setEvents($events);

$app->getRouterStore()
    ->get('/', fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'app']))
    ->get('/users/{id}/', function (ServerRequestInterface $request, $next) {
        $params = $request->getAttribute('my-params');
        return new JsonResponse(['message' => 'user: ' . $params['id'] ?? null]);
    })
    ->use(fn(ServerRequestInterface $request, $next) => new JsonResponse(['message' => 'otherwise']));

$request = $psr17Factory->fromGlobals(); // /api/users/34/
$response = $app->handle($request);
(new SapiEmitter)->emit($response);