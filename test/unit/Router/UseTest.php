<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;

class UseTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Next(new LayerResolver);
    }

    public function testMethod(): void
    {
        $request = new ServerRequest;

        $this->app->getRouterStore()
            ->use(fn($request, $next) => new JsonResponse(['status' => 200]) );

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }

    public function testChainMiddlewares(): void
    {
        $request = new ServerRequest();

        $this->app->getRouterStore()
            ->use(fn($request, RequestHandlerInterface $next) => $next->handle($request) )
            ->use(fn ($request, $next) => new JsonResponse(['status' => 202]) );

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['status' => 202], $response->getPayload());
    }

    public function testPathMiddlewares(): void
    {
        $request = new ServerRequest();

        $this->app->getRouterStore()
            ->use(fn ($request, $next) => new JsonResponse(['name' => 'A']), ['path' => '/blog'])
            ->use(fn ($request, $next) => new JsonResponse(['name' => 'B']));

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['name' => 'B'], $response->getPayload());
    }
}
