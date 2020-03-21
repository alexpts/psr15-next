<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use PTS\Events\Events;
use PTS\NextRouter\Extra\OptionsMiddleware;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;

class AllowedMethodsTest extends TestCase
{

    protected Next $app;
    protected LayerResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Next;
        $this->app->setEvents(new Events);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/user', 'OPTIONS');

        $this->app->getRouterStore()
            ->get('/user', fn($request, $next) => new JsonResponse(['status' => 200]) )
            ->delete('/user', fn($request, $next) => new JsonResponse(['status' => 200]) )
            ->middleware(new OptionsMiddleware($this->app));

        $response = $this->app->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Methods'));
        $this->assertSame('GET, DELETE, OPTIONS', $response->getHeaderLine('Access-Control-Allow-Methods'));
    }

    public function testUnknownPath(): void
    {
        $request = new ServerRequest([], [], '/user', 'OPTIONS');

        $this->app->getRouterStore()
            ->get('/not-user', fn($request, $next) => new JsonResponse(['status' => 200]) )
            ->put('/not-user/{id}/', fn($request, $next) => new JsonResponse(['status' => 200]) )
            ->middleware(new OptionsMiddleware($this->app))
            ->use(fn($request, $next) => new JsonResponse(['status' => 404]) );

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(404, $response->getPayload()['status']);
    }
}
