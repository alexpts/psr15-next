<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;

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

        $this->app->getRouterStore()
            // expected skip by http method
            ->post('/', fn($request, $next) => new JsonResponse(['method' => 'post']) )
            ->patch('/', fn($request, $next) => new JsonResponse(['method' => 'patch']) )
            ->get('/', fn($request, $next) => new JsonResponse(['method' => 'get']) );

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['method' => 'get'], $response->getPayload());
    }
}
