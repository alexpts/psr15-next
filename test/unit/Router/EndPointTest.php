<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Controller\UserController;
use PTS\NextRouter\Next;

class EndPointTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Next;
    }

    public function testEndPoint(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->app->getStoreLayers();

        $endPoint = ['controller' => UserController::class];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer);

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        $this->assertSame(['action' => 'main'], $response->getPayload());
    }

    public function testEndPointReuse(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->app->getStoreLayers();

        $endPoint = ['controller' => UserController::class, 'reuse' => true];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer);

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        /** @var JsonResponse $response2 */
        $response2 = $this->app->handle($request);
        $this->assertSame(['action' => 'main'], $response->getPayload());
        $this->assertSame(['action' => 'main'], $response2->getPayload());
    }

    public function testBacController(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->app->getStoreLayers();

        $endPoint = ['controller' => 'UnknownClass', 'nextOnError' => false];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Controller not found');
        $this->app->handle($request);
    }

    public function testBacAction(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->app->getStoreLayers();

        $endPoint = ['controller' => UserController::class, 'action' => 'unknown', 'nextOnError' => false];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Action not found');
        $this->app->handle($request);
    }

    public function testBacControllerNext(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->app->getStoreLayers();

        $endPoint = ['controller' => 'UnknownClass', 'nextOnError' => true];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer)->use(function (){
            return new JsonResponse(['action' => 'otherwise']);
        });

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        $this->assertSame(['action' => 'otherwise'], $response->getPayload());
    }

    public function testFilterMatches(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->app->getStoreLayers();

        $endPoint = ['controller' => UserController::class];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer);

        $request = $request->withAttribute('params', [
            '_action' => 'get',
            'id' => 4
        ]);

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        $this->assertSame(['action' => 'get'], $response->getPayload());
    }
}
