<?php

use Blackfire\Client;
use Blackfire\Profile\Configuration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Next;
use PTS\Psr7\Factory\Psr17Factory;
use PTS\Psr7\Response;

require_once __DIR__  .'/../vendor/autoload.php';

$iterations = $argv[1] ?? 1000;
$blackfire = $argv[2] ?? false;
$iterations++;

if ($blackfire) {
    $client = new Client;
    $probe = $client->createProbe(new Configuration);
}

$startTime = microtime(true);

$app = new Next;
$response404 = new Response(200, ['content-type' => 'application/json'], json_encode(['message' => 'otherwise']));

$app->getRouterStore()
    ->use(function (ServerRequestInterface $request, $next) {
        /** @var ResponseInterface $response */
        $response = $next->handle($request);
        return $response->withHeader('x-header', 1);
    })
    ->get('/users', function (ServerRequestInterface $request, $next) {
        return new Response(200, ['content-type' => 'application/json'], json_encode(['message' => 'hello']));
    })
    ->use(function (ServerRequestInterface $request, $next) use ($response404) {
        return clone $response404;
    });

$psr17Factory = new Psr17Factory;
while ($iterations--) {
	$request = $psr17Factory->createServerRequest('GET', '/user');
    $response = $app->handle($request);
}

$diff = (microtime(true) - $startTime) * 1000;
echo sprintf('%2.3f ms', $diff);
echo "\n" . memory_get_peak_usage()/1024;

if ($blackfire) {
    $client->endProbe($probe);
}
