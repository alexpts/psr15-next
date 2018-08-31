<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Router;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class GetTest extends TestCase
{

    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Router;
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/');

        $this->router->getStore()
            ->get('/', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            });
        /** @var JsonResponse $response */
        $response = $this->router->handle($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }

    public function testInvoke(): void
    {
        $request = new ServerRequest([], [], '/');
        $router = $this->router;

        $router->getStore()
            ->get('/', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            });
        /** @var JsonResponse $response */
        $response = $router($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }

    public function testSimple2(): void
    {
        $request = new ServerRequest([], [], '/main');

        $this->router->getStore()
            ->get('/', function ($request, $next) {
                throw new \Exception('must skip');
            })
            ->get('/main', function ($request, $next) {
                return new JsonResponse(['status' => 'main']);
            });

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);

        $this->assertSame(['status' => 'main'], $response->getPayload());
    }

    public function testFallback(): void
    {
        $request = new ServerRequest([], [], '/otherwise');

        $this->router->getStore()
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
        $response = $this->router->handle($request);

        $this->assertSame(['status' => 'otherwise'], $response->getPayload());
    }

    public function testWithPrefix(): void
    {
        $request = new ServerRequest([], [], '/admins/dashboard');
        $router = new Router;

        $router->getStore()
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
