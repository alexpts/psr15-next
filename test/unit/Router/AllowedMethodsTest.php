<?php

use PHPUnit\Framework\TestCase;
use PTS\Events\Events;
use PTS\NextRouter\Extra\OptionsMiddleware;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class AllowedMethodsTest extends TestCase
{

    /** @var Next */
    protected $app;
    /** @var LayerResolver */
    protected $resolver;

    public function setUp()
    {
        parent::setUp();

        $this->app = new Next;
        $this->app->setEvents(new Events);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/user', 'OPTIONS');

        $this->app->getStoreLayers()
            ->get('/user', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->delete('/user', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->middleware(new OptionsMiddleware($this->app));

        $response = $this->app->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Methods'));
        $this->assertSame('GET, DELETE, OPTIONS', $response->getHeaderLine('Access-Control-Allow-Methods'));
    }

    public function testUnknownPath(): void
    {
        $request = new ServerRequest([], [], '/user', 'OPTIONS');

        $this->app->getStoreLayers()
            ->get('/not-user', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->put('/not-user/{id}/', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->middleware(new OptionsMiddleware($this->app))
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 404]);
            });
        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(404, $response->getPayload()['status']);
    }
}
