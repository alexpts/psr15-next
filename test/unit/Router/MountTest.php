<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class MountTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Next(new LayerResolver);
    }

    public function testMount(): void
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $router2 = clone $this->app;

        $router2->getRouterStore()
            ->use(fn($request, $next) => new JsonResponse(['status' => 401]), ['path' => '/admin/.*'])
            ->use(fn($request, $next) => new JsonResponse(['status' => 200]), ['path' => '/']);

        /** @var JsonResponse $response */
        $response = $this->app
            ->mount($router2, '/api')
            ->handle($request);

        static::assertSame(['status' => 200], $response->getData());
    }

    public function testMountWithoutPath(): void
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $router2 = clone $this->app;

        $router2->getRouterStore()
            ->use(fn($request, $next) => new JsonResponse(['status' => 401]), ['path' => '/admin/.*'])
            ->use(fn ($request, $next) => new JsonResponse(['status' => 200]), ['path' => '/']);

        /** @var JsonResponse $response */
        $response = $this->app
            ->mount($router2)
            ->handle($request);

        static::assertSame(['status' => 200], $response->getData());
    }
}
