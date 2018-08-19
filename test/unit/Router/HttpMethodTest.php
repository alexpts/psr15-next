<?php

use PHPUnit\Framework\TestCase;
use PTS\Events\Events;
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

        $this->router = new Router(new LayerResolver, new Events);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/');

        /** @var JsonResponse $response */
        $response = $this->router
            // expected skip by http method
            ->post('/', function ($request, $next) {
                return new JsonResponse(['method' => 'post']);
            })
            ->patch('/', function ($request, $next) {
                return new JsonResponse(['method' => 'patch']);
            })
            ->get('/', function ($request, $next) {
                return new JsonResponse(['method' => 'get']);
            })
            ->handle($request);

        $this->assertSame(['method' => 'get'], $response->getPayload());
    }
}
