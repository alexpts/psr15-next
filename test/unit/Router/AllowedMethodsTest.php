<?php

use PHPUnit\Framework\TestCase;
use PTS\Events\Events;
use PTS\NextRouter\Extra\OptionsMiddleware;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class AllowedMethodsTest extends TestCase
{

    /** @var Router */
    protected $router;
    /** @var LayerResolver */
    protected $resolver;

    public function setUp()
    {
        parent::setUp();

        $this->resolver = new LayerResolver;
        $this->router = new Router($this->resolver, new Events);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/user', 'OPTIONS');

        $response = $this->router
            ->get('/user', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->delete('/user', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->middleware(new OptionsMiddleware($this->router, new LayerResolver))
            ->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Methods'));
        $this->assertSame('OPTIONS, GET, DELETE', $response->getHeaderLine('Access-Control-Allow-Methods'));
    }

    public function testUnknownPath(): void
    {
        $request = new ServerRequest([], [], '/user', 'OPTIONS');

        /** @var JsonResponse $response */
        $response = $this->router
            ->get('/not-user', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->put('/not-user/{id}/', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->middleware(new OptionsMiddleware($this->router, $this->resolver))
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 404]);
            })
            ->handle($request);

        $this->assertSame(404, $response->getPayload()['status']);
    }
}