<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Next;

class DynamicEndpointTest extends TestCase
{
    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Next;
    }

    public function testEndPoint(): void
    {
        $request = new ServerRequest([], [], '/user-controller/');
        $store = $this->app->getRouterStore();

        $endPoint = ['prefix' => 'PTS\\NextRouter\\Controller\\'];
        $layer = $store->getLayerFactory()->dynamicEndPoint($endPoint, ['path' => '/{_controller}/']);
        $store->addLayer($layer);

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        $this->assertSame(['action' => 'get'], $response->getPayload());
    }

    public function testBadEndpointError(): void
    {
        $request = new ServerRequest([], [], '/user-controller/');
        $store = $this->app->getRouterStore();

        $endPoint = ['prefix' => 'PTS\\NextRouter\\Controller\\', 'nextOnError' => false];
        $layer = $store->getLayerFactory()->dynamicEndPoint($endPoint, ['path' => '/user-controller/']);
        $store->addLayer($layer);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Not found controller name for dynamic controller point');
        $this->app->handle($request);
    }
}
