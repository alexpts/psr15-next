<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class MiddlewareTest extends TestCase
{

    /** @var Next */
    protected $app;

    public function setUp()
    {
        parent::setUp();

        $this->app = new Next;
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/');

        $next = function (ServerRequestInterface $request, RequestHandlerInterface $next) {
            return $next->handle($request);
        };
        $middleware = new CallableToMiddleware($next);

        $this->app->getStoreLayers()
            ->middleware($middleware)
            ->use($next)
            ->getLayerFactory()->callable($next);

        $this->app->getStoreLayers()->get('/', function ($request, $next) {
            return new JsonResponse(['status' => 200]);
        });
        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }

    public function testSimple2(): void
    {
        $request = new ServerRequest([], [], '/main');

        $this->app->getStoreLayers()
            ->get('/', function ($request, $next) {
                throw new \Exception('must skip');
            })
            ->get('/main', function ($request, $next) {
                return new JsonResponse(['status' => 'main']);
            });

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['status' => 'main'], $response->getPayload());
    }

    public function testFallback(): void
    {
        $request = new ServerRequest([], [], '/otherwise');

        $this->app->getStoreLayers()
            ->get('/', function ($request, $next) {
                throw new \Exception('must skip');
            })
            ->get('/main', function ($request, $next) {
                return new JsonResponse(['status' => 'main']);
            })
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 'otherwise']);
            });

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['status' => 'otherwise'], $response->getPayload());
    }

    public function testWithPrefix(): void
    {
        $request = new ServerRequest([], [], '/admins/dashboard');
        $router = new Next;

        $router->getStoreLayers()
            ->setPrefix('/admins')
            ->get('/admins/dashboard', function ($request, $next) { // /admins/admins/dashboard
                throw new \Exception('must skip');
            })
            ->get('/dashboard', function ($request, $next) {
                return new JsonResponse(['status' => 'dashboard']);
            });

        /** @var JsonResponse $response */
        $response = $router->handle($request);

        $this->assertSame(['status' => 'dashboard'], $response->getPayload());
    }
}
