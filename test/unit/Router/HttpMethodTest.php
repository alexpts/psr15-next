<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;

class HttpMethodTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Next(new LayerResolver);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/');

        $this->app->getStoreLayers()
            // expected skip by http method
            ->post('/', function ($request, $next) {
                return new JsonResponse(['method' => 'post']);
            })
            ->patch('/', function ($request, $next) {
                return new JsonResponse(['method' => 'patch']);
            })
            ->get('/', function ($request, $next) {
                return new JsonResponse(['method' => 'get']);
            });

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['method' => 'get'], $response->getPayload());
    }
}
