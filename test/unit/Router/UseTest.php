<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

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
        $request = new ServerRequest('GET', new Uri('/'));

        $this->app->getRouterStore()->use(fn($request, $next) => new JsonResponse(['status' => 200]) );

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        static::assertSame(['status' => 200], $response->getData());
    }

    public function testChainMiddlewares(): void
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $this->app->getRouterStore()
            ->use(fn($request, RequestHandlerInterface $next) => $next->handle($request) )
            ->use(fn ($request, $next) => new JsonResponse(['status' => 202]) );

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        static::assertSame(['status' => 202], $response->getData());
    }

    public function testPathMiddlewares(): void
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $this->app->getRouterStore()
            ->use(fn ($request, $next) => new JsonResponse(['name' => 'A']), ['path' => '/blog'])
            ->use(fn ($request, $next) => new JsonResponse(['name' => 'B']));

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        static::assertSame(['name' => 'B'], $response->getData());
    }
}
