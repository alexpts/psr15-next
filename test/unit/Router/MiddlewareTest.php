<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Next;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class MiddlewareTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Next;
    }

    public function testSimple(): void
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $next = function (ServerRequestInterface $request, RequestHandlerInterface $next) {
            return $next->handle($request);
        };
        $middleware = new CallableToMiddleware($next);

        $this->app->getRouterStore()
            ->middleware($middleware)
            ->use($next)
            ->getLayerFactory()->callable($next);

        $this->app->getRouterStore()->get('/', fn($request, $next) => new JsonResponse(['status' => 200]) );
        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        static::assertSame(['status' => 200], $response->getData());
    }

    public function testSimple2(): void
    {
        $request = new ServerRequest('GET', new Uri('/main'));

        $this->app->getRouterStore()
            ->get('/', fn($request, $next) => throw new Exception('must skip'))
            ->get('/main', fn($request, $next) => new JsonResponse(['status' => 'main']));

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        static::assertSame(['status' => 'main'], $response->getData());
    }

    public function testFallback(): void
    {
        $request = new ServerRequest('GET', new Uri('/otherwise'));

        $this->app->getRouterStore()
            ->get('/', fn($request, $next) => throw new Exception('must skip'))
            ->get('/main', fn($request, $next) => new JsonResponse(['status' => 'main']) )
            ->use(fn($request, $next) => new JsonResponse(['status' => 'otherwise']) );

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        static::assertSame(['status' => 'otherwise'], $response->getData());
    }

    public function testWithPrefix(): void
    {
        $request = new ServerRequest('GET', new Uri('/admins/dashboard'));
        $router = new Next;

        $router->getRouterStore()
            ->setPrefix('/admins')
            ->get('/admins/dashboard', function ($request, $next) { // /admins/admins/dashboard
                throw new Exception('must skip');
            })
            ->get('/dashboard', fn($request, $next) => new JsonResponse(['status' => 'dashboard']) );

        /** @var JsonResponse $response */
        $response = $router->handle($request);

        static::assertSame(['status' => 'dashboard'], $response->getData());
    }
}
