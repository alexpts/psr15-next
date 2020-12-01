<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PTS\Events\EventEmitter;
use PTS\NextRouter\Extra\OptionsMiddleware;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class AllowedMethodsTest extends TestCase
{

    protected Next $app;
    protected LayerResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Next;
        $this->app->setEvents(new EventEmitter);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest('OPTIONS', new Uri('/user'));

        $this->app->getRouterStore()
            ->get('/user', fn($request, $next) => new JsonResponse(['status' => 200]) )
            ->delete('/user', fn($request, $next) => new JsonResponse(['status' => 200]) )
            ->middleware(new OptionsMiddleware($this->app));

        $response = $this->app->handle($request);

        static::assertTrue($response->hasHeader('Access-Control-Allow-Methods'));
        static::assertSame('GET, DELETE, OPTIONS', $response->getHeaderLine('Access-Control-Allow-Methods'));
    }

    public function testUnknownPath(): void
    {
        $request = new ServerRequest('OPTIONS', new Uri('/user'));

        $this->app->getRouterStore()
            ->get('/not-user', fn($request, $next) => new JsonResponse(['status' => 200]) )
            ->put('/not-user/{id}/', fn($request, $next) => new JsonResponse(['status' => 200]) )
            ->middleware(new OptionsMiddleware($this->app))
            ->use(fn($request, $next) => new JsonResponse(['status' => 404]) );

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        static::assertSame(404, $response->getData()['status']);
    }
}
