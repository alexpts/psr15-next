<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class HttpMethodTest extends TestCase
{

    /** @var Next */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Next(new LayerResolver);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/');

        $this->router->getStoreLayers()
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
