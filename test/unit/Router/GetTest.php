<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\LayerResolver;
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

        $this->router = new Router(new LayerResolver);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/');

        /** @var JsonResponse $response */
        $response = $this->router
            ->get('/', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->handle($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }

    public function testSimple2(): void
    {
        $request = new ServerRequest([], [], '/main');

        /** @var JsonResponse $response */
        $response = $this->router
            ->get('/', function ($request, $next) {
                throw new \Exception('must skip');
            })
            ->get('/main', function ($request, $next) {
                return new JsonResponse(['status' => 'main']);
            })
            ->handle($request);

        $this->assertSame(['status' => 'main'], $response->getPayload());
    }

    public function testFallback(): void
    {
        $request = new ServerRequest([], [], '/otherwise');

        /** @var JsonResponse $response */
        $response = $this->router
            ->get('/', function ($request, $next) {
                throw new \Exception('must skip');
            })
            ->get('/main', function ($request, $next) {
                return new JsonResponse(['status' => 'main']);
            })
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 'otherwise']);
            })
            ->handle($request);

        $this->assertSame(['status' => 'otherwise'], $response->getPayload());
    }

    public function testWithPrefix(): void
    {
        $request = new ServerRequest([], [], '/admins/dashboard');

        $this->router = new Router;
        $this->router->getStore()->setPrefix('/admins');

        /** @var JsonResponse $response */
        $response = $this->router
            ->get('/admins/dashboard', function ($request, $next) { // /admins/admins/dashboard
                throw new \Exception('must skip');
            })
            ->get('/dashboard', function ($request, $next) {
                return new JsonResponse(['status' => 'dashboard']);
            })
            ->handle($request);

        $this->assertSame(['status' => 'dashboard'], $response->getPayload());
    }
}
