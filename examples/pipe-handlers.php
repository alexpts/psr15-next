<?php
declare(strict_types=1);

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Next;

require_once '../vendor/autoload.php';

$app = new Next;

$handler = [
    'fetch user' => function (ServerRequestInterface $request, $next) {
        $user = 'some user object';
        // ...  find $user by id
        if (null === $user) {
            throw new \Exception('User not found', 404);
        }

        $request = $request->withAttribute('user', $user);
    },
    'controller' => function (ServerRequestInterface $request, $next) {
        $user = $request->getAttribute('user');
        return new JsonResponse(['user' => $user], 200);
    },
];

$app->getStoreLayers()
    ->pipe($handler, ['path' => '/users', 'method' => ['GET']])
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);
(new SapiEmitter)->emit($response);

