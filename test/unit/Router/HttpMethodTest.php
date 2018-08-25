<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class HttpMethodTest extends TestCase
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

        $this->router->getStore()
            // expected skip by http method
            ->post('/', function ($request, $next) {
                return new JsonResponse(['method' => 'post']);
            })
            ->patch('/', function ($request, $next) {
                return new JsonResponse(['method' => 'patch']);
            })
            ->get('/', function ($request, $next) {
                return new JsonResponse(['method' => 'get']);
            });

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);

        $this->assertSame(['method' => 'get'], $response->getPayload());
    }
}
