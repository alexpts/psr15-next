<?php

use Blackfire\Client;
use Blackfire\Profile\Configuration;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Next;

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

$app->getStoreLayers()
    ->use(function (ServerRequestInterface $request, $next) {
        /** @var ResponseInterface $response */
        $response = $next->handle($request);
        return $response->withHeader('x-header', 12);
    })
    ->get('/users', function (ServerRequestInterface $request, $next) {
        return new Response(200, ['content-type' => 'application/json'], json_encode(['message' => 'hello']));
    })
    ->use(function (ServerRequestInterface $request, $next) {
        return new Response(200, ['content-type' => 'application/json'], json_encode(['message' => 'otherwise']));
    });

$psr17Factory = new Psr17Factory;
$creator = new ServerRequestCreator($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

while ($iterations--) {
    $request = $creator->fromGlobals();
    $response = $app->handle($request);
}

$diff = (microtime(true) - $startTime) * 1000;
echo sprintf('%2.3f ms', $diff);
echo "\n" . memory_get_peak_usage()/1024;

if ($blackfire) {
    $client->endProbe($probe);
}
